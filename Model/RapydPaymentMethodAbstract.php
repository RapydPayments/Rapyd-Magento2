<?php

namespace Rapyd\Rapydmagento2\Model;

abstract class RapydPaymentMethodAbstract extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_isInitializeNeeded      = false;
    protected $redirect_uri;
    protected $_canOrder = true;
    protected $_isGateway = true;
    protected $_canUseInternal = false;

    abstract public function getCategory();

    public function getOrderPlaceRedirectUrl()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
                            ->get('Magento\Framework\UrlInterface')->getUrl("rapyd/redirect");
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        try {
            $api = $this->getRapydObject();
            $instance = \Rapyd\Rapydmagento2\lib\RapydCategories::getInstance();
            if (empty($instance->getCategories())) {
                $instance->setCategories($api->make_request_to_rapyd('get', \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_CATEGORIES_PATH));
            }

            if (!($instance->getCategories()) || (!empty($instance->getCategories()['status']) &&  'ERROR' == $instance->getCategories()['status']['status'])) {
                return false;
            }
            foreach ($instance->getCategories() as $value) {
                if ($value == $this->getCategory()) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            //handle exception
            return false;
        }
    }

    public function getRapydObject()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $access_key = $this->_scopeConfig->getValue("payment/rapyd/access_key", $storeScope);
        $secret_key = $this->_scopeConfig->getValue("payment/rapyd/secret_key", $storeScope);
        $testmode =$this->_scopeConfig->getValue("payment/rapyd/testmode", $storeScope);

        $test_access_key = $this->_scopeConfig->getValue("payment/rapyd/test_access_key", $storeScope);
        $test_secret_key = $this->_scopeConfig->getValue("payment/rapyd/test_secret_key", $storeScope);
        $api = new \Rapyd\Rapydmagento2\lib\RapydRequest($access_key, $secret_key, $testmode, $test_access_key, $test_secret_key);
        return $api;
    }
}
