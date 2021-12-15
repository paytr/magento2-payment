<?php

namespace Paytr\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaytrPaymentMethodIframe
 *
 * @package Paytr\Payment\Model
 */
class PaytrPaymentMethodIframe extends AbstractMethod
{

    protected $_code                = 'paytr_iframe';
    protected $_isInitializeNeeded  = true;
    protected $_isOffline           = true;

    /**
     * @return string[][][]
     */
    public function getConfig(): array
    {
        $objectManager   = ObjectManager::getInstance();
        $logo            = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('payment/paytr_iframe/paytr_logo');
        return [
            'payment' => [
                'paytr' => [
                    'logo_url' => 'https://www.paytr.com/img/general/paytr.svg',
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
}
