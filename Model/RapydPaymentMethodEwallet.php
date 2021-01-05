<?php

namespace rapydpayments\rapydmagento2\Model;

include_once __DIR__ . DIRECTORY_SEPARATOR . "RapydPaymentMethodAbstract.php";

class RapydPaymentMethodEwallet extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_ewallet';

    public function getCategory()
    {
        return "ewallet";
    }
}
