<?php

namespace Paytr\Payment\Block\Adminhtml\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class CategoryInstallment
 *
 * @package Paytr\Payment\Block\Adminhtml\Form\Field
 */
class CategoryInstallment extends AbstractFieldArray
{

    protected $categoryRenderer;
    protected $installmentTypesRenderer;

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getCategoryRenderer()
    {
        if (!$this->categoryRenderer) {
            $this->categoryRenderer = $this->getLayout()->createBlock(
                Categories::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->categoryRenderer;
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getInstallmentTypesRenderer()
    {
        if (!$this->installmentTypesRenderer) {
            $this->installmentTypesRenderer = $this->getLayout()->createBlock(
                InstallmentTypes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->installmentTypesRenderer;
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'category_id',
            [
                'label'     => __('Select Category'),
                'renderer'  => $this->getCategoryRenderer(),
            ]
        );
        $this->addColumn(
            'installment_types',
            [
                'label' => __('Installment'),
                'renderer'  => $this->getInstallmentTypesRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Installment Rule');
    }

    /**
     * @param  DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $category = $row->getCategoryId();
        $options = [];
        if ($category) {
            $options['option_' . $this->getCategoryRenderer()->calcOptionHash($category)]
                = 'selected="selected"';
            $installmentTypes = $row->getInstallmentTypes();
            foreach ($installmentTypes as $installmentType) {
                $options['option_' . $this->getInstallmentTypesRenderer()->calcOptionHash($installmentType)]
                    = 'selected="selected"';
            }
        }
        $row->setData('option_extra_attrs', $options);
    }
}
