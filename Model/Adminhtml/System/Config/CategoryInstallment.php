<?php

namespace Paytr\Payment\Model\Adminhtml\System\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CategoryInstallment
 *
 * @package Paytr\Payment\Model\Adminhtml\System\Config
 */
class CategoryInstallment extends Value
{

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var Json|mixed
     */
    private $serializer;

    /**
     * CategoryInstallment constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param Random                $mathRandom
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     * @param Json|null             $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?Json $serializer = null
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this|CategoryInstallment
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];
        foreach ($value as $data) {
            if (empty($data['category_id']) || empty($data['installment_types'])) {
                continue;
            }
            $category = $data['category_id'];
            if (array_key_exists($category, $result)) {
                $result[$category] = $this->appendUniqueCategories($result[$category], $data['installment_types']);
            } else {
                $result[$category] = $data['installment_types'];
            }
        }
        $this->setValue($this->serializer->serialize($result));
        return $this;
    }

    /**
     * @return $this|CategoryInstallment
     * @throws LocalizedException
     */
    public function afterLoad()
    {
        if ($this->getValue()) {
            $value = $this->serializer->unserialize($this->getValue());
            if (is_array($value)) {
                $this->setValue($this->encodeArrayFieldValue($value));
            }
        }
        return $this;
    }

    /**
     * @param  array $value
     * @return array
     * @throws LocalizedException
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $category => $creditCardType) {
            $id = $this->mathRandom->getUniqueHash('_');
            $result[$id] = ['category_id' => $category, 'installment_types' => $creditCardType];
        }
        return $result;
    }

    /**
     * @param  array $categoriesList
     * @param  array $inputCategoriesList
     * @return array
     */
    private function appendUniqueCategories(array $categoriesList, array $inputCategoriesList)
    {
        $result = array_merge($categoriesList, $inputCategoriesList);
        return array_values(array_unique($result));
    }
}
