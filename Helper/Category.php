<?php

namespace Paytr\Payment\Helper;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Category
 *
 * @package Paytr\Payment\Helper
 */
class Category
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Category constructor.
     *
     * @param CollectionFactory     $factory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(CollectionFactory $factory, StoreManagerInterface $storeManager)
    {
        $this->collectionFactory = $factory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategories()
    {
        $cat                = [];
        $objectManager      = $objectManager = ObjectManager::getInstance();
        $categoryFactory    = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories         = $categoryFactory->create()->addAttributeToSelect('*')->setStore($this->_storeManager->getStore());
        foreach ($categories as $key => $category) {
            $cat[$key] = $category->getName();
        }
        return $cat;
    }
}
