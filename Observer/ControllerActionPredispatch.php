<?php
namespace Paytr\Payment\Observer;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

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
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var LoggerInterface
     */
    private $logger;

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
        Http $redirect,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_redirect = $redirect;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
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
                        $order->getPayment()->getMethodInstance()->getCode() == "paytr_iframe" ||
                        $order->getPayment()->getMethodInstance()->getCode() == "paytr_iframe_transfer"
                    ) and $order->getState()== Order::STATE_NEW) {
                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                    $url = $this->urlBuilder->getUrl("paytr/redirect");
                    try {
                        $this->orderSender->send($order);
                    } catch (\Throwable $e) {
                        $this->logger->critical($e);
                    }

                    // refill cart
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $logger = $objectManager->get('Psr\Log\LoggerInterface');
                    $cart = $objectManager->get('Magento\Checkout\Model\Cart');
                    $items = $order->getItemsCollection();
                    foreach ($items as $item) {
                        try {
                            $cart->addOrderItem($item);
                        } catch (\Exception $e) {
                            $logger->critical($e);
                        }
                    }
                    $cart->save();

                    $this->_redirect->setRedirect($url);
                }
            }
        }
    }
}
