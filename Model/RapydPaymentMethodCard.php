<?php

namespace rapydpayments\rapydmagento2\Model;

include_once __DIR__ . DIRECTORY_SEPARATOR . "RapydPaymentMethodAbstract.php";

class RapydPaymentMethodCard extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_card';

    public function getCategory()
    {
        return "card";
    }
}
