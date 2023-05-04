<?php

namespace Paytr\Payment\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

/**
 * Class ControllerActionPredispatch
 *
 * @package Paytr\Payment\Observer
 */
class ControllerActionPredispatch implements ObserverInterface
{

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Http
     */
    protected $_redirect;

    /**
     * @var mixed
     */
    protected $urlBuilder;

    /**
     * ControllerActionPredispatch constructor.
     *
     * @param Session      $checkoutSession
     * @param OrderFactory $orderFactory
     * @param Http         $redirect
     */
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Http $redirect
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_redirect = $redirect;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');
        if ($request->getModuleName() == "checkout" and $request->getActionName()== "success") {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if (($order->getPayment()->getMethodInstance()->getCode()== "paytr" ||
                        $order->getPayment()->getMethodInstance()->getCode()== "paytr_iframe"
                    ) and $order->getState()== Order::STATE_NEW) {
                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                    $url = $this->urlBuilder->getUrl("paytr/redirect");
                    $this->_redirect->setRedirect($url);
                }
            }
        }
    }
}