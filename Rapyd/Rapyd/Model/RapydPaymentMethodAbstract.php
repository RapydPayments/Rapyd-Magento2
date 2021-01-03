<?php

namespace Rapyd\Rapyd\Model;

$ds = DIRECTORY_SEPARATOR;
include_once __DIR__ . "$ds..$ds/lib/Rapyd.php";
include_once __DIR__ . "$ds..$ds/lib/consts.php";

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
            if (empty($GLOBALS['categories'])) {
                $GLOBALS['categories'] = $api->make_request_to_rapyd('get', RAPYD_CATEGORIES_PATH);
            }

            if (!($GLOBALS['categories']) || (!empty($GLOBALS['categories']['status']) &&  'ERROR' == $GLOBALS['categories']['status']['status'])) {
                return false;
            }
            foreach ($GLOBALS['categories'] as $value) {
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
        $api = new \Rapyd($access_key, $secret_key, $testmode, $test_access_key, $test_secret_key);
        return $api;
    }
}
