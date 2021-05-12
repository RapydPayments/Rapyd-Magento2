<?php
namespace Rapyd\Rapydmagento2\Model\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class ControllerActionPredispatch implements ObserverInterface
{
    protected $checkoutSession;
    protected $orderFactory;
    protected $_redirect;
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        \Magento\Framework\App\Response\Http $redirect

    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_redirect = $redirect;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request =$observer->getData('request');
        if ($request->getModuleName() == "checkout" and $request->getActionName()== "success") {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order->getPayment()->getMethodInstance()->getCode()== "rapyd" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_bank" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_cash" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_card" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_ewallet") {
                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                                ->get('Magento\Framework\UrlInterface');
                    $url = $this->urlBuilder->getUrl("rapyd/redirect");
                    $this->_redirect->setRedirect($url);
                }
            }
        }
    }
}
