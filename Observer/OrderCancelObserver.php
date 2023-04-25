<?php
namespace Paytr\Payment\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Paytr\Payment\Model\PaytrPaymentMethodIframe;
use Psr\Log\LoggerInterface;

class OrderCancelObserver implements ObserverInterface
{
    protected LoggerInterface $logLoggerInterface;
    protected TransactionSearchResultInterfaceFactory $transactions;
    protected PaytrPaymentMethodIframe $paytrPaymentMethodIframe;

    public function __construct(
        LoggerInterface $logLoggerInterface,
        TransactionSearchResultInterfaceFactory $transactions,
        PaytrPaymentMethodIframe $paytrPaymentMethodIframe) {
        $this->logLoggerInterface = $logLoggerInterface;
        $this->transactions = $transactions;
        $this->paytrPaymentMethodIframe = $paytrPaymentMethodIframe;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $transactions = $this->transactions->create()->addOrderIdFilter($order->getId());
        if($transactions->getTotalCount() > 0 and
            ($order->getPayment()->getMethodInstance()->getCode()== "paytr" ||
                $order->getPayment()->getMethodInstance()->getCode()== "paytr_iframe")) {
            // ödeme iade edilecek
            return $this->paytrPaymentMethodIframe->refund($order->getPayment(), $order->getGrandTotal());
        }
        // ödeme yok, sipariş kapatılacak
        return true;
    }
}