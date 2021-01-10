<?php

namespace rapyd\rapydmagento2\Model;

class RapydPaymentMethodEwallet extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_ewallet';

    public function getCategory()
    {
        return "ewallet";
    }
}
