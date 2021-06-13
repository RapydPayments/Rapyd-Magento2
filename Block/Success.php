<?php
namespace Rapyd\Rapydmagento2\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;

class Success extends \Magento\Framework\View\Element\Template
{
    protected $request;
    protected $orderFactory;
    protected $urlBuilder;
    protected $checkoutSession;
    protected $_messageManager;
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        OrderFactory $orderFactory,
        Context $context,
        Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_messageManager = $messageManager;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\UrlInterface');
        parent::__construct($context);
    }
    public function _prepareLayout()
    {
        try {
            $orderId = $this->checkoutSession->getLastOrderId();
            $queries = [];
            parse_str($_SERVER['QUERY_STRING'], $queries);
            if ((getenv('RAPYD_QA_AUTO')==1 || getenv('RAPYD_QA_AUTO')=='1') && !empty($queries['order_id'])) {
                $orderId = $queries['order_id'];
            }
            $order = $this->orderFactory->create()->load($orderId);
            if (!$order) {
                return;//hacking attempt
            }
            $status = $order->getStatus();
            $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $rapyd_data = [
                'title'=> \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_ON_HOLD_TITLE,
                'message'=> \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_ON_HOLD,
                'shopping_url'=>$base_url,
                'order_id'=>$orderId
            ];
            if ('processing' == $status) {
                $rapyd_data['title'] = \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_SUCCESS_TITLE;
                $rapyd_data['message'] = \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_SUCCESS;
            } elseif ('canceled' == $status) {
                $rapyd_data['title'] = \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_ON_CANCEL_TITLE;
                $rapyd_data['message'] = \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_THANKYOU_PAGE_ON_CANCEL;
            }
            $this->setAction(json_encode($rapyd_data, JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            //handle exception
            $this->_messageManager->addErrorMessage('An error occurred. Please try again.');
        }
    }
}
