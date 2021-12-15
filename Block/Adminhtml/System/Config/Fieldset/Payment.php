<?php

namespace Paytr\Payment\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;

/**
 * Class Payment
 *
 * @package Paytr\Payment\Block\Adminhtml\System\Config\Fieldset
 */
class Payment extends Fieldset
{

    protected $_backendConfig;

    /**
     * Payment constructor.
     *
     * @param Context $context
     * @param Session $authSession
     * @param Js      $jsHelper
     * @param Config  $backendConfig
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $backendConfig,
        array $data = []
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element): string
    {
        $enabledString = $this->_isPaymentEnabled($element) ? ' enabled' : 'enabled';
        return parent::_getFrontendClass($element) . ' with-button' . $enabledString;
    }

    /**
     * @param  $element
     * @return bool
     */
    protected function _isPaymentEnabled($element)
    {
        $groupConfig = $element->getGroup();
        $activityPaths = isset($groupConfig['activity_path']) ? $groupConfig['activity_path'] : [];
        if (!is_array($activityPaths)) {
            $activityPaths = [$activityPaths];
        }
        $isPaymentEnabled = true;
        foreach ($activityPaths as $activityPath) {
            $isPaymentEnabled = $isPaymentEnabled || (bool)(string)$this->_backendConfig
                ->getConfigDataValue($activityPath);
        }
        return $isPaymentEnabled;
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element): string
    {
        $html = '<div class="config-heading" >';
        $groupConfig = $element->getGroup();
        $disabledAttributeString = $this->_isPaymentEnabled($element) ? '' : ' disabled="disabled"';
        $disabledClassString = $this->_isPaymentEnabled($element) ? '' : ' disabled';
        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"' .
            $disabledAttributeString .
            ' class="button action-configure' .
            (empty($groupConfig['paytr_ec_separate']) ? '' : ' paytr-ec-separate') .
            $disabledClassString .
            '" id="' .
            $htmlId .
            '-head" onclick="paytrToggleSolution.call(this, \'' .
            $htmlId .
            "', '" .
            $this->getUrl(
                'adminhtml/*/state'
            ) . '\'); return false;"><span class="state-closed">' . __(
                'Settings'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= '</div>';
        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element): string
    {
        return '';
    }

    /**
     * @param  AbstractElement $element
     * @return false
     */
    protected function _isCollapseState($element): bool
    {
        return false;
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element): string
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.paytrToggleSolution = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
