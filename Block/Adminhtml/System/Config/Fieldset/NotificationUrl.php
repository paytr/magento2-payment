<?php

namespace Paytr\Payment\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class NotificationUrl
 *
 * @package Paytr\Payment\Block\Adminhtml\System\Config\Fieldset
 */
class NotificationUrl extends Field
{

    /**
     * @param  AbstractElement $element
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_storeManager->getStore()->getBaseUrl().'rest/V1/paytr/callback/';
    }
}
