<?php

namespace Paytr\Payment\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\View\LayoutInterface;

class ViewPlugin
{
    public function beforeSetLayout(View $view, LayoutInterface $layout)
    {
        $orderId = $view->getRequest()->getParam('order_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        if($order->getStatus() !== 'pending') {
            $view->removeButton('order_cancel');
        }
    }
}