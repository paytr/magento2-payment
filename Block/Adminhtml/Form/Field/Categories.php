<?php

namespace Paytr\Payment\Block\Adminhtml\Form\Field;

use Paytr\Payment\Helper\Category;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Categories
 *
 * @method setName(string $value)
 */
class Categories extends Select
{

    /**
     * @var Category
     */
    private $categoryHelper;

    /**
     * Categories constructor.
     *
     * @param Context  $context
     * @param Category $categoryHelper
     * @param array    $data
     */
    public function __construct(Context $context, Category $categoryHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->categoryHelper->getCategories());
        }
        return parent::_toHtml();
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
