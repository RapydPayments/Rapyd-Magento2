<?php

namespace rapyd\rapydmagento2\Model;

class RapydPaymentMethodCash extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_cash';

    public function getCategory()
    {
        return "cash";
    }
}
