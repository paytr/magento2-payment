<?php

namespace Paytr\Payment\Model\Api;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\OrderFactory;
use Paytr\Payment\Helper\PaytrHelper;
use Psr\Log\LoggerInterface;

/**
 * Class Webhook
 *
 * @package Paytr\Payment\Model\Api
 */
class Webhook
{
    protected $orderFactory;
    protected $config;
    protected $transactionBuilder;
    protected $transactionRepository;
    protected $request;
    protected $paytrHelper;
    private $orderSender;
    private $logger;

    /**
     * Webhook constructor.
     *
     * @param OrderFactory                   $orderFactory
     * @param Context                        $context
     * @param TransactionBuilder             $tb
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Request                        $request
     * @param PaytrHelper                    $paytrHelper
     */
    public function __construct(
        OrderFactory $orderFactory,
        Context $context,
        TransactionBuilder $tb,
        TransactionRepositoryInterface $transactionRepository,
        Request $request,
        PaytrHelper $paytrHelper,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->orderFactory             = $orderFactory;
        $this->config                   = $context->getScopeConfig();
        $this->transactionBuilder       = $tb;
        $this->transactionRepository    = $transactionRepository;
        $this->request                  = $request;
        $this->paytrHelper              = $paytrHelper;
        $this->orderSender              = $orderSender;
        $this->logger                   = $logger;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        $response = $this->responseNormalize($this->request->getBodyParams());
        return array_key_exists('status', $response) && $response['status'] === 'success'
            ? $this->getSuccessResponse($response)
            : $this->getFailedResponse($response);
    }

    /**
     * @param $response
     * @return string
     */
    public function getSuccessResponse($response)
    {
        if ($this->validateHash($response, $response['hash'])) {
            $order_id   = $this->normalizeMerchantOid($response['merchant_oid']);
            $order      = $this->orderFactory->create()->load($order_id);
            return $this->addTransactionToOrder($order, $response);
        } else {
            return 'PAYTR notification failed: bad hash';
        }
    }

    /**
     * @param $response
     * @return string
     */
    public function getFailedResponse($response)
    {
        if ($this->validateHash($response, $response['hash'])) {
            $order_id   = $this->normalizeMerchantOid($response['merchant_oid']);
            $order      = $this->orderFactory->create()->load($order_id);
            if($order->getState() == Order::STATE_PENDING_PAYMENT ||
                $order->getState() == Order::STATE_NEW) {
              $order->addStatusHistoryComment($response['failed_reason_msg']);
              $order->cancel();
              $order->setState(Order::STATE_CANCELED);
              $order->setStatus("canceled");
              $order->save();
              return 'OK';
            }
            return 'OK';
        } else {
            return 'PAYTR notification failed: bad hash';
        }
    }

    /**
     * @param $response
     * @param $hash
     * @return bool
     */
    public function validateHash($response, $hash)
    {
        return base64_encode(hash_hmac('sha256', $response['merchant_oid'] . $this->paytrHelper->getMerchantSalt() . $response['status'] . $response['total_amount'], $this->paytrHelper->getMerchantKey(), true)) === $hash;
    }

    /**
     * @param $params
     * @return array
     */
    public function responseNormalize($params)
    {
        $items = [];
        foreach ($params as $key => $param) {
            $items[$key] = $param;
        }
        return $items;
    }

    /**
     * @param $merchant_oid
     * @return mixed|string
     */
    public function normalizeMerchantOid($merchant_oid)
    {
        $merchant_oid = explode('SP', $merchant_oid);
        $merchant_oid = explode('MG', $merchant_oid[1]);
        return $merchant_oid[0];
    }

    /**
     * @param $order
     * @param $response
     * @return string
     */
    public function addTransactionToOrder($order, $response)
    {
        if ($order->getState()) {
            if($order->getState() == Order::STATE_PENDING_PAYMENT ||
            $order->getState() == Order::STATE_NEW ||
            $order->getState() == Order::STATE_CANCELED) {
                $payment = $order->getPayment();
                $payment->setLastTransId($response['merchant_oid']);
                $payment->setTransactionId($response['merchant_oid']);
                $transaction = $this->transactionBuilder->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($response['merchant_oid'])
                    ->setAdditionalInformation(
                        [Transaction::RAW_DETAILS => (array) $response]
                    )
                    ->setFailSafe(true)
                    ->build(Transaction::TYPE_ORDER);
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    $this->customNote($response, $order)
                );
                $payment->setParentTransactionId(null);
                $payment->save();
                $order->setState(Order::STATE_PROCESSING, true);
                $order->setStatus(Order::STATE_PROCESSING);
                $order->save();
                try {
                    $this->orderSender->send($order);
                } catch (\Throwable $e) {
                    $this->logger->critical($e);
                }
                return 'OK';
            }
            return 'OK';
        }
        return 'HATA: Sipariş durumu tamamlanmadı. Tekrar deneniyor.';
    }

    /**
     * @param $response
     * @param $order
     * @return string
     */
    public function customNote($response, $order)
    {
        $currency               = $this->orderFactory->create()->load($order->getRealOrderId());
        $currency               = $currency->getOrderCurrency()->getId();
        $maturity_difference    = 'Vade Farkı: ' . (round(($response['total_amount'] - $response['payment_amount']) / 100)) . ' ' . $currency . '<br>';
        $total_amount           = number_format(($response['total_amount'] / 100), 2, '.', '.');
        $amount                 = number_format(($response['payment_amount'] / 100), 2, '.', '.');
        $note = '<b>' . __('PAYTR NOTICE - Payment Accepted') . '</b><br>';
        $note .= __('Total Paid') . ': ' . $total_amount . ' ' . $currency . '<br>';
        $note .= __('Paid') . ': ' . $amount . ' ' . $currency . '<br>';
        $note .= ($response['installment_count'] === '1' ? '' : $maturity_difference);
        $note .= __('Installment Count') . ': ' . ($response['installment_count'] === '1' ? 'Tek Çekim' : $response['installment_count']) . '<br>';
        $note .= __('PayTR Order Number') . ': <a href="https://www.paytr.com/magaza/islemler?merchant_oid=' . $response['merchant_oid'] . '" target="_blank">' . $response['merchant_oid'] . '</a><br>';
        return $note;
    }
}
