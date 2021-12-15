<?php

namespace Paytr\Payment\Block\Adminhtml\Form\Field;

use Paytr\Payment\Helper\InstallmentType;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class InstallmentTypes
 *
 * @package Paytr\Payment\Block\Adminhtml\Form\Field
 */
class InstallmentTypes extends Select
{

    private $installmentTypeHelper;

    /**
     * InstallmentTypes constructor.
     *
     * @param Context         $context
     * @param InstallmentType $installmentTypeHelper
     * @param array           $data
     */
    public function __construct(
        Context $context,
        InstallmentType $installmentTypeHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->installmentTypeHelper = $installmentTypeHelper;
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->installmentTypeHelper->getInstallmentTypes());
        }
        $this->setClass('installment-type-select');
        return parent::_toHtml();
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}
