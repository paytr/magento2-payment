<?php

namespace Paytr\Payment\Block;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Paytr\Payment\Helper\PaytrHelper;

/**
 * Class Installment
 *
 * @package Paytr\Payment\Block
 */
class Installment extends Template
{
    protected $_product = null;
    protected $_registry;
    protected $_productFactory;
    protected $paytrHelper;
    protected $productLinks;

    /**
     * Installment constructor.
     *
     * @param Context        $context
     * @param Registry       $registry
     * @param ProductFactory $productFactory
     * @param PaytrHelper    $paytrHelper
     * @param array          $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $productFactory,
        PaytrHelper $paytrHelper,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_productFactory = $productFactory;
        $this->paytrHelper = $paytrHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * @return array|int[]
     */
    public function installmentSettings()
    {
        return $this->paytrHelper->calculateInstallment(
            $this->paytrHelper->getCategoryInstallment(),
            $this->getCurrentProduct()->getCategoryIds(),
            true
        );
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->paytrHelper->getMerchantId();
    }

    /**
     * @return mixed|null
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function getInstallmentTableToken()
    {
        return $this->paytrHelper->getInstallmentTableToken();
    }

    /**
     * @return mixed
     */
    public function getInstallmentTableShowAll()
    {
        return $this->paytrHelper->getInstallmentTableShowAll();
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        $product_ids    = [];
        $productId      = $this->getCurrentProduct()->getId();
        $_objectManager = ObjectManager::getInstance();
        $_product       = $_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $_childProducts = $_product->getTypeInstance()->getUsedProducts($_product);
        foreach ($_childProducts as $simpleProduct) {
            $product_ids[$simpleProduct->getId()]
                = number_format($simpleProduct->getPrice(), 2, '.', '.');
        }
        return $product_ids;
    }
}
