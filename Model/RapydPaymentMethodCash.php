<?php

namespace rapydpayments\rapydmagento2\Model;

include_once __DIR__ . DIRECTORY_SEPARATOR . "RapydPaymentMethodAbstract.php";

class RapydPaymentMethodCash extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_cash';

    public function getCategory()
    {
        return "cash";
    }
}
