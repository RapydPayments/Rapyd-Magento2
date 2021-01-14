<?php

namespace Rapyd\Rapydmagento2\Model;

class RapydPaymentMethodCash extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_cash';

    public function getCategory()
    {
        return "cash";
    }
}
