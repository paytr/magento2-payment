<?php

namespace Paytr\Payment\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Paytr\Payment\Helper\PaytrHelper;
use Paytr\Payment\Helper\PaytrRequestHelper;
use Magento\Framework\App\ObjectManager;

/**
 * Class Redirect
 *
 * @package Paytr\Payment\Block
 */
class Redirect extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var PaytrHelper
     */
    protected $paytrHelper;

    /**
     * @var PaytrRequestHelper
     */
    protected $paytrRequestHelper;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected Http $_redirect;

    /**
     * Redirect constructor.
     *
     * @param Context            $context
     * @param ManagerInterface   $messageManager
     * @param PaytrHelper        $paytrHelper
     * @param PaytrRequestHelper $paytrRequestHelper
     * @param Http               $redirect
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        PaytrHelper $paytrHelper,
        PaytrRequestHelper $paytrRequestHelper,
        Http $redirect
    ) {
        $this->config = $context->getScopeConfig();
        $this->_messageManager = $messageManager;
        $this->_redirect       = $redirect;
        $this->paytrHelper = $paytrHelper;
        $this->paytrRequestHelper = $paytrRequestHelper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _prepareLayout()
    {
        try {
            $order = $this->paytrHelper->getOrder();
            if (!$order->getRealOrderId()) {
                header('Location: ' . $this->_storeManager->getStore()->getBaseUrl());
                return false;
            }
            $urlBuilder = ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
            $errorUrl = $urlBuilder->getUrl("paytr/error");
            if($order->getState() == Order::STATE_CANCELED) {
                $this->_redirect->setRedirect($errorUrl);
            }
            $paytr_data = [
                'status' => 'success',
                'token' => $this->paytrRequestHelper->getPaytrToken(),
                'timeout' => $this->paytrHelper->getTimeoutLimit(),
                'redirect_url' => $errorUrl,
                'message' => ''
            ];
            $this->setAction(json_encode($paytr_data));
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('An error occurred. Please try again.');
        }
        return true;
    }
}
