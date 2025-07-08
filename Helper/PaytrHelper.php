<?php

namespace Paytr\Payment\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PaytrHelper
 *
 * @package Paytr\Payment\Helper
 */
class PaytrHelper
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config               = $context->getScopeConfig();
        $this->checkoutSession      = $checkoutSession;
        $this->orderFactory         = $orderFactory;
        $this->_storeManager        = $storeManager;
    }

    /**
     * @return string
     */
    public function getScopeInterface()
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->config->getValue('payment/paytr_iframe/merchant_id', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getMerchantSalt()
    {
        return $this->config->getValue('payment/paytr_iframe/merchant_salt', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getTimeoutLimit()
    {
        return $this->config->getValue('payment/paytr_iframe/timeout_limit', $this->getScopeInterface());
    }

    public function getOrderStatus()
    {
        return $this->config->getValue('payment/paytr_iframe/order_status', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getMerchantKey()
    {
        return $this->config->getValue('payment/paytr_iframe/merchant_key', $this->getScopeInterface()) ?? 'NnYLzPw6CTtoNk5K';
    }

    /**
     * @return mixed
     */
    public function getDebugOn()
    {
        return $this->config->getValue('payment/paytr_iframe/debug_on', $this->getScopeInterface());
    }

    /**
     * @return int|mixed
     */
    public function getNoInstallment()
    {
        return $this->calculateInstallment($this->getCategoryInstallment(), $this->getCategoryIds())['no_installment'] ?? [];
    }

    /**
     * @return int|mixed
     */
    public function getMaxInstallment()
    {
        return $this->calculateInstallment($this->getCategoryInstallment(), $this->getCategoryIds())['max_installment'] ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTestMode()
    {
        return $this->config->getValue('payment/paytr_iframe/test_mode', $this->getScopeInterface()) ?? 1;
    }

    /**
     * @return mixed
     */
    public function getCategoryInstallment()
    {
        return json_decode($this->config->getValue('payment/paytr_iframe/categoryinstallment', $this->getScopeInterface()), true);
    }

    /**
     * @return mixed
     */
    public function getInstallmentTableTab()
    {
        return $this->config->getValue('payment/paytr_iframe/installment_table_tab', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getInstallmentTableShowAll()
    {
        return $this->config->getValue('payment/paytr_iframe/installment_table_show_all', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getInstallmentTableToken()
    {
        return $this->config->getValue('payment/paytr_iframe/installment_table_token', $this->getScopeInterface());
    }

    /**
     * @return mixed
     */
    public function getRealOrderId()
    {
        return $this->checkoutSession->getLastRealOrder()->getId();
    }

    /**
     * @return Order|OrderFactory
     */
    public function getOrder()
    {
        return $this->orderFactory->create()->load($this->getRealOrderId());
    }

    /**
     * @return mixed
     */
    public function getUserIp()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    /**
     * @return string
     */
    public function getUserBasket()
    {
        $user_basket = [];
        foreach ($this->checkoutSession->getLastRealOrder()->getAllVisibleItems() as $items) {
            $user_basket[] = [
                $items->getName(),
                number_format($items->getBaseOriginalPrice(), 2, '.', '.'),
                $items->getQtyToShip()
            ];
        }
        return base64_encode(json_encode($user_basket));
    }

    /**
     * @return string
     */
    public function getMerchantOid()
    {
        return 'SP' . $this->getRealOrderId() . 'MG' . strtotime($this->getOrder()->getUpdatedAt());
    }

    /**
     * @return OrderAddressInterface|null
     */
    public function getBilling()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getBilling()->getEmail();
    }

    /**
     * @return false|string
     */
    public function getPaymentAmount()
    {
        return substr(str_replace('.', '', $this->getOrder()->getGrandTotal()), 0, -2);
    }

    /**
     * @return mixed|string
     */
    public function getCurrency()
    {
        $currency = $this->getOrder()->getOrderCurrency()->getId();
        return $currency === 'TRY' ? 'TL' : $currency;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getBilling()->getFirstname() . ' ' . $this->getBilling()->getLastname();
    }

    /**
     * @return string
     */
    public function getUserPhone()
    {
        return $this->getBilling()->getTelephone();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMerchantOkUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl() . "paytr/success";
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMerchantFailUrl()
    {
        return $this->getMerchantOkUrl();
    }

    /**
     * @return string
     */
    public function getUserAddress()
    {
        return $this->getBilling()->getStreet()[0]
            . ' ' . $this->getBilling()->getCity()
            . ' ' . $this->getBilling()->getRegion()
            . ' ' . $this->getBilling()->getRegion()
            . ' ' . $this->getBilling()->getCountryId()
            . ' ' . $this->getBilling()->getPostcode();
    }

    /**
     * @return string
     */
    public function makeHashStr()
    {
        return
            $this->getMerchantId()
            . $this->getUserIp()
            . $this->getMerchantOid()
            . $this->getEmail()
            . $this->getPaymentAmount()
            . $this->getUserBasket()
            . $this->getNoInstallment()
            . $this->getMaxInstallment()
            . $this->getCurrency()
            . $this->getTestMode();
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return base64_encode(hash_hmac('sha256', $this->makeHashStr() . $this->getMerchantSalt(), $this->getMerchantKey(), true));
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        $ids = [];
        foreach ($this->checkoutSession->getLastRealOrder()->getAllItems() as $items) {
            $ids[] = $items->getProduct()->getCategoryIds();
        }
        return $ids;
    }

    /**
     * @param  $categoryInstallment
     * @param  $categoryIds
     * @param  false $in_table
     * @return array|int[]
     */
    public function calculateInstallment($categoryInstallment, $categoryIds, $in_table = false)
    {
        $current_installments = [];
        if(!is_array($categoryInstallment)) {
            $categoryInstallment = [$categoryInstallment];
        }
        if ($in_table) {
            foreach ($categoryIds as $id) {
                if (array_key_exists($id, $categoryInstallment)) {
                    $current_installments[] = $categoryInstallment[$id][0];
                }
            }
            return $this->getCurrentInstallment($current_installments);
        }
        foreach ($categoryIds as $key => $ids) {
            foreach ($ids as $id) {
                if (array_key_exists($id, $categoryInstallment)) {
                    $current_installments[] = $categoryInstallment[$id][0];
                }
            }
        }
        return $this->getCurrentInstallment($current_installments);
    }

    /**
     * @param  $installments
     * @return array|int[]
     */
    public function getCurrentInstallment($installments)
    {
        if (in_array('1', $installments)) {
            return [
                'no_installment'    => 1,
                'max_installment'   => 0,
            ];
        } elseif (($key = array_search('0', $installments)) !== false && count($installments) > 1) {
            unset($installments[$key]);
            return [
                'no_installment'    => 0,
                'max_installment'   => min($installments),
            ];
        }
        return [
            'no_installment'    => 0,
            'max_installment'   => count($installments) ? min($installments) : 0,
        ];
    }

    /**
     * @return mixed|string
     */
    public function getLang()
    {
        $objectManager = ObjectManager::getInstance()->get('Magento\Framework\Locale\Resolver')->getLocale();
        return explode('_', $objectManager)[0] ?? 'en';
    }

    /**
     * @return int
     */
    public function getIFrameV1()
    {
        return $this->config->getValue('payment/paytr_iframe/iframe_v1', $this->getScopeInterface()) ?? 0;
    }

    /**
     * @return int
     */
    public function getIFrameV2DarkMode()
    {
        return $this->config->getValue('payment/paytr_iframe/iframe_v2_dark', $this->getScopeInterface()) ?? 0;
    }


    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function makePostVariables()
    {
        return [
            'merchant_id'       =>  $this->getMerchantId(),
            'user_ip'           =>  $this->getUserIp(),
            'merchant_oid'      =>  $this->getMerchantOid(),
            'email'             =>  $this->getEmail(),
            'payment_amount'    =>  $this->getPaymentAmount(),
            'paytr_token'       =>  $this->getToken(),
            'user_basket'       =>  $this->getUserBasket(),
            'debug_on'          =>  $this->getDebugOn(),
            'no_installment'    =>  $this->getNoInstallment(),
            'max_installment'   =>  $this->getMaxInstallment(),
            'user_name'         =>  $this->getUsername(),
            'user_address'      =>  $this->getUserAddress(),
            'user_phone'        =>  $this->getUserPhone(),
            'merchant_ok_url'   =>  $this->getMerchantOkUrl(),
            'merchant_fail_url' =>  $this->getMerchantFailUrl(),
            'timeout_limit'     =>  $this->getTimeoutLimit(),
            'currency'          =>  $this->getCurrency(),
            'test_mode'         =>  $this->getTestMode(),
            'lang'              =>  $this->getLang(),
            'iframe_v2'         =>  !$this->getIFrameV1(),
            'iframe_v2_dark'    =>  $this->getIFrameV2DarkMode(),
        ];
    }
}
