<?php

namespace Paytr\Payment\Controller\Success;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 *
 * @package Paytr\Payment\Controller\Success
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SuccessValidator
     */
    private $successValidator;
    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param Context                  $context
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession          $checkoutSession
     * @param SuccessValidator         $successValidator
     * @param OrderSender $orderSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        SuccessValidator $successValidator,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->successValidator = $successValidator;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if($order->getState() == Order::STATE_PENDING_PAYMENT ||
            $order->getState() == Order::STATE_NEW ||
            $order->getState() == null) {
                $order->setState(Order::STATE_PENDING_PAYMENT);
                try {
                    $this->orderSender->send($order);
                } catch (\Throwable $e) {
                    $this->logger->critical($e);
                }
                $order->save();
        }
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
        if (!$this->successValidator->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
