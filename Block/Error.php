<?php

namespace Paytr\Payment\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Paytr\Payment\Helper\PaytrHelper;
use Paytr\Payment\Helper\PaytrRequestHelper;

/**
 * Class Redirect
 *
 * @package Paytr\Payment\Block
 */
class Error extends \Magento\Framework\View\Element\Template
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
     * Redirect constructor.
     *
     * @param Context            $context
     * @param ManagerInterface   $messageManager
     * @param PaytrHelper        $paytrHelper
     * @param PaytrRequestHelper $paytrRequestHelper
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        PaytrHelper $paytrHelper,
        PaytrRequestHelper $paytrRequestHelper
    ) {
        $this->config = $context->getScopeConfig();
        $this->_messageManager = $messageManager;
        $this->paytrHelper = $paytrHelper;
        $this->paytrRequestHelper = $paytrRequestHelper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _prepareLayout()
    {
        $this->setAction($this->_storeManager->getStore()->getBaseUrl());
        return true;
    }
}