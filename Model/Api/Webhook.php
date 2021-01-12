<?php
namespace Rapyd\Rapydmagento2\Model\Api;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\OrderFactory;

class Webhook
{
    protected $orderFactory;
    protected $config;
    protected $transactionBuilder;
    protected $transactionRepository;
    public function __construct(OrderFactory $orderFactory, Context $context, TransactionBuilder $tb, \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository)
    {
        $this->orderFactory = $orderFactory;
        $this->config = $context->getScopeConfig();
        $this->transactionBuilder = $tb;
        $this->transactionRepository = $transactionRepository;
    }

    /**
    * @inheritdoc
    */

    public function getPost($rapyd_data)
    {
        try {
            if (!$this->is_signature_valid($rapyd_data)) {
                return "auth failed";
            }
            $body = $rapyd_data['body'];
            $order_id = $body['order_id'];
            $order = $this->orderFactory->create()->load($order_id);
            $orderState = $body['magento_status'];
            $order_note = $body['magento_order_note'];
            $order->setState($orderState)->setStatus($orderState);
            $order->save();
            if ($orderState=="pending_payment") {//rapyd act
                $this->addTransactionToOrder($order, $body['payment_token'], 0, $order_note);
            } elseif ($orderState=="processing" || $orderState=="canceled") {
                if (!$this->updateTransaction($order, $body['payment_token'], $order_note)) {
                    $this->addTransactionToOrder($order, $body['payment_token'], 1, $order_note);
                }
            }
            $order->save();
            return "webhook success";
        } catch (\Exception $e) {
            //handle exception
        }
    }
    public function getRefund($rapyd_data)
    {
        try {
            if (!$this->is_signature_valid($rapyd_data)) {
                return "auth failed";
            }
            $body = $rapyd_data['body'];
            $order_id = $body['order_id'];
            $order = $this->orderFactory->create()->load($order_id);
            $amount = $body['amount'];
            $refund_token = $body['refund_token'];
            $message = $body['reason'];
            $this->addRefundTransactionToOrder($order, $refund_token, 1, $message, $amount);
            $order->setTotalRefunded($amount);
            $order->save();
            return "refund success";
        } catch (\Exception $e) {
            //handle exception
        }
    }

    public function is_signature_valid($rapyd_data)
    {
        try {
            $headers = $rapyd_data['headers'];
            if (empty($headers) || empty($headers['salt']) || empty($headers['timestamp']) || empty($headers['accessKey']) || empty($headers['signature'])) {
                return false;
            }
            $body = $rapyd_data['body'];
            $http_method = 'post';
            $path = 'RAPYD_PATH';
            $salt = $headers['salt'];
            $timestamp = $headers['timestamp'];
            $access_key = $headers['accessKey'];
            $api = $this->getRapydObject();
            $secret_key = $api->rapyd_get_secret_key();

            $body_string = json_encode($body, JSON_UNESCAPED_SLASHES);

            $sig_string = $http_method . $path . $salt . $timestamp . $access_key . $secret_key . $body_string;

            $hash_sig_string = hash_hmac('sha256', $sig_string, $secret_key);

            $signature = base64_encode($hash_sig_string);

            if ($headers['signature'] == $signature) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            //handle exception
        }
    }

    public function getRapydObject()
    {
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $access_key = $this->config->getValue("payment/rapyd/access_key", $storeScope);
            $secret_key = $this->config->getValue("payment/rapyd/secret_key", $storeScope);
            $testmode = $this->config->getValue("payment/rapyd/testmode", $storeScope);

            $test_access_key = $this->config->getValue("payment/rapyd/test_access_key", $storeScope);
            $test_secret_key = $this->config->getValue("payment/rapyd/test_secret_key", $storeScope);

            $api = new \Rapyd\Rapydmagento2\lib\RapydRequest($access_key, $secret_key, $testmode, $test_access_key, $test_secret_key);
            return $api;
        } catch (\Exception $e) {
            //handle exception
        }
    }

    public function addTransactionToOrder($order, $payment_token, $isClosed, $message)
    {
        try {
            $payment = $order->getPayment();

            $payment->setTransactionId($payment_token);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [$message]]
            );
            $trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER, null, true);//////TYPE_PAYMENT
            $trn->setIsClosed($isClosed)->save();
            $payment->addTransactionCommentsToOrder(
                $trn,
                $message
            );

            $payment->setParentTransactionId(null);
            $payment->save();
        } catch (\Exception $e) {
            //handle exception
        }
    }

    public function addRefundTransactionToOrder($order, $refund_token, $isClosed, $message, $amount)
    {
        try {
            $payment = $order->getPayment();

            $payment->setTransactionId($refund_token);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [$message]]
            );
            $trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND, null, true);//////TYPE_PAYMENT
            $trn->setIsClosed($isClosed)->save();
            $payment->addTransactionCommentsToOrder(
                $trn,
                $message
            );
            $payment->setAmountRefunded($amount);

            $payment->setParentTransactionId(null);
            $payment->save();
        } catch (\Exception $e) {
            //handle exception
        }
    }

    public function updateTransaction($order, $transactionId, $message)
    {
        try {
            $payment = $order->getPayment();
            $transaction = $this->transactionRepository->getByTransactionId(
                $transactionId,
                $payment->getId(),
                $order->getId()
            );
            if ($transaction) {
                $transaction->setTxnId($transactionId);
                $payment->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [$message]]
                );
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    $message
                );
                $transaction->setIsClosed(1);
                $transaction->save();
                $payment->save();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            //handle exception
        }
    }
}
