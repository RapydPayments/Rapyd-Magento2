<?php
namespace rapydpayments\rapydmagento2\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\OrderFactory;

$ds = DIRECTORY_SEPARATOR;
include_once __DIR__ . "$ds..$ds/lib/consts.php";

class Main extends \Magento\Framework\View\Element\Template
{
    protected $_objectmanager;
    protected $checkoutSession;
    protected $orderFactory;
    protected $urlBuilder;
    protected $config;
    protected $_messageManager;
    protected $transactionBuilder;
    protected $customerSession;
    protected $toolkit_url;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        TransactionBuilder $tb,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->toolkit_url = '';
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->config = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->transactionBuilder = $tb;
        $this->_messageManager = $messageManager;

        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\UrlInterface');
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {
        try {
            $orderId = $this->checkoutSession->getLastOrderId();
            $order = $this->orderFactory->create()->load($orderId);
            if ($order) {
                $billing = $order->getBillingAddress();
                $shipping = $order->getShippingAddress();
                $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $body = [
                'amount' => $this->encode_string($order->getGrandTotal()),
                'currency_code' => $this->encode_string($order->getGlobalCurrencyCode()),
                'reference_id' => $this->encode_string($orderId),
                'user_id' => $this->encode_string($this->customerSession->getCustomer()->getId()),
                'webhook_url' => $this->encode_string($base_url . 'rest/V1/rapyd/webhook/'),
                'refund_url' => $this->encode_string($base_url . 'rest/V1/rapyd/refund/'),
                'complete_payment_url' => $this->encode_string($base_url . 'rapyd/success/'), //$this->urlBuilder->getUrl('checkout/onepage/success/', ['_secure' => true]),//$this->urlBuilder->getUrl("rapyd/response"),
                'error_payment_url' =>$this->encode_string($base_url . 'rapyd/success/'),// $this->urlBuilder->getUrl('checkout/onepage/failure/', ['_secure' => true]),
                'cancel_checkout_url'=> $this->encode_string($base_url . 'rapyd/success/'),//$this->urlBuilder->getUrl('checkout/cart', ['_secure' => true]),//$this->urlBuilder->getUrl("rapyd/response"),
                'order_id' => $this->encode_string($orderId),
                'receipt_email' => $this->encode_string($billing->getEmail()),
                'country_code' => $this->encode_string($billing->getCountryId()),
                'customer_first_name' =>$this->encode_string($billing->getFirstname()),
                'customer_last_name' => $this->encode_string($billing->getLastname()),
                'customer_phone' => $this->encode_string($billing->getTelephone()),
                'shipping_address' => [
                    'line1' => $this->encode_string($shipping->getStreet()[0]),
                    'line2' => $this->getLine2($shipping),
                    'city' => $this->encode_string($shipping->getCity()),
                    'state' =>$this->encode_string($shipping->getRegion()),
                    'phone_number' => $this->encode_string($shipping->getTelephone()),
                    'country' => $this->encode_string($shipping->getCountryId()),
                    'zip' => $this->encode_string($shipping->getPostcode())
                ],
                'billing_address'=>[
                    'line1' => $this->encode_string($billing->getStreet()[0]),
                    'line2' => $this->getLine2($billing),
                    'city' => $this->encode_string($billing->getCity()),
                    'state' =>$this->encode_string($billing->getRegion()),
                    'phone_number' => $this->encode_string($billing->getTelephone()),
                    'country' => $this->encode_string($billing->getCountryId()),
                    'zip' => $this->encode_string($billing->getPostcode())
                ],
                'category' => $this->encode_string($order->getPayment()->getMethodInstance()->getCode())
            ];

                $order->save();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $access_key = $this->config->getValue("payment/rapyd/access_key", $storeScope);
                $secret_key = $this->config->getValue("payment/rapyd/secret_key", $storeScope);
                $testmode = $this->config->getValue("payment/rapyd/testmode", $storeScope);

                $test_access_key = $this->config->getValue("payment/rapyd/test_access_key", $storeScope);
                $test_secret_key = $this->config->getValue("payment/rapyd/test_secret_key", $storeScope);

                $ds = DIRECTORY_SEPARATOR;
                include_once __DIR__ . "$ds..$ds/lib/Rapyd.php";

                $api = new \Rapyd($access_key, $secret_key, $testmode, $test_access_key, $test_secret_key);
                $response = $api->generateRapydToken($body);

                $rapyd_data = [
                'status'=>'failed',
                'token'=>'',
                'message'=>''
            ];
                if (empty($response)) {
                    $rapyd_data['message'] = RAPYD_ERROR_LOADING_TOOLKIT;
                } elseif (!empty($response) && empty($response['token'])) {
                    $rapyd_data['message'] = $response;
                } else {
                    $rapyd_data['status'] = 'success';
                    $rapyd_data['token'] = $response['token'];
                    $rapyd_data['success_url'] = $base_url . 'rapyd/success/';
                }
                if (strpos($base_url, '127.0.0.1') !== false) {
                    $this->toolkit_url = $this->getViewFileUrl('rapydpayments_rapydmagento2::js/toolkit.js');
                } else {
                    $this->toolkit_url = $api->rapyd_get_toolkit_url();
                }
                //set initial state after token created
                $order->setState(Order::STATE_HOLDED)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
                $order->save();

                $this->setAction(json_encode($rapyd_data, JSON_UNESCAPED_SLASHES));
            }
        } catch (\Exception $e) {
            //$this->_messageManager->addErrorMessage($e->getMessage());
            $this->_messageManager->addErrorMessage('An error occurred. Please try again.');
        }
    }

    public function getLine2($object)
    {
        try {
            return $this->encode_string($object->getStreet()[1]);
        } catch (\Exception $e) {
            return "";
        }
    }

    public function rapyd_get_toolkit_url()
    {
        return $this->toolkit_url;
    }
    public function encode_string($str)
    {
        $str = utf8_encode($str);
        return base64_encode($str);
    }
}
