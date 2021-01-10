<?php
namespace rapyd\rapydmagento2\Model\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class ControllerActionPredispatch implements ObserverInterface
{
    protected $checkoutSession;
    protected $orderFactory;
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request =$observer->getData('request');
        if ($request->getModuleName() == "checkout" and $request->getActionName()== "success") {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if (($order->getPayment()->getMethodInstance()->getCode()== "rapyd" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_bank" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_cash" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_card" || $order->getPayment()->getMethodInstance()->getCode()== "rapyd_ewallet") and $order->getState()== Order::STATE_NEW) {
                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                                ->get('Magento\Framework\UrlInterface');
                    $url = $this->urlBuilder->getUrl("rapyd/redirect");
                    header("Location:$url");

                    exit;
                }
            }
        }
    }
}
