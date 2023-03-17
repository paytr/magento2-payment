<?php

namespace Paytr\Payment\Model;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class PaytrPaymentMethodIframe
 *
 * @package Paytr\Payment\Model
 */
class PaytrPaymentMethodIframe extends AbstractMethod
{
    protected $_code = 'paytr_iframe';
    protected $_isInitializeNeeded = false;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    /**
     * @return array[]
     */
    public function getConfig()
    {
        $objectManager   = ObjectManager::getInstance();
        $logo            = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe/paytr_logo');
        return [
            'payment' => [
                'paytr' => [
                    'logo_url' => 'https://www.paytr.com/img/general/PayTR-Odeme-Kurulusu.svg?v01',
                    'logo_visible' => $logo ? 'display: inline' : 'display: none'
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getOrderPlaceRedirectUrl()
    {
        return ObjectManager::getInstance()->get('Magento\Framework\UrlInterface')->getUrl("paytr/redirect");
    }

    /**
     * @param InfoInterface $payment
     * @param $amount
     * @return $this
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $transactionId = $payment->getLastTransId();
        $objectManager   = ObjectManager::getInstance();
        $merchant_id = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe/merchant_id');
        $merchant_key = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe/merchant_key');
        $merchant_salt = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe/merchant_salt');
        $paytr_token = base64_encode(hash_hmac('sha256', $merchant_id . $transactionId . $amount . $merchant_salt, $merchant_key, true));
        try {
            $post_vals = ['merchant_id'   => $merchant_id,
                'merchant_oid'  => $transactionId,
                'return_amount' => $amount,
                'paytr_token'   => $paytr_token];
            if($this->callRefundCurl($post_vals) == true) {
                $refundTransaction = $objectManager->get('Magento\Sales\Model\Order\Payment\Transaction\Builder')
                    ->setPayment($payment)
                    ->setOrder($payment->getOrder())
                    ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
                    ->build(Transaction::TYPE_REFUND);
                $payment->addTransactionCommentsToOrder(
                    $refundTransaction,
                    "<b>PAYTR NOTICE - Refund Complete</b><br/>".$refundTransaction->getId()
                );
                return $this;
            }
        } catch (Exception $e) {
            throw new Exception(__('Payment refunding error.'));
        }
        return $this;
    }

    public function callRefundCurl($variables)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/iade");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $variables);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, 1);
            if ($result['status'] !== 'success') {
                throw new Exception($result['err_no'] . " - " . $result['err_msg']);
            } else {
                return true;
            }
        } catch (Exception $e) {
            throw new Exception(__('Payment refunding error.'));
        }
        return false;
    }
}
