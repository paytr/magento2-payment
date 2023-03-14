<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 namespace Paytr\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;


class SendMailOnOrderSuccess implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @param LoggerInterface $logger
     * @param OrderSender $orderSender
     */
    public function __construct(
        LoggerInterface $logger,
        OrderSender $orderSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
    }

    /**
     * Send order email.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();


        if (
          ($order->getPayment()->getMethodInstance()->getCode()== "paytr" ||
          $order->getPayment()->getMethodInstance()->getCode()== "paytr_iframe")
        and $order->getState()== Order::STATE_PENDING_PAYMENT) {
          try {
              $this->orderSender->send($order);
          } catch (\Throwable $e) {
              $this->logger->critical($e);
          }
        }
    }
}
