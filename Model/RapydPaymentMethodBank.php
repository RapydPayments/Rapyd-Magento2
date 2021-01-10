<?php

namespace rapyd\rapydmagento2\Model;

class RapydPaymentMethodBank extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_bank';

    public function getCategory()
    {
        return "bank";
    }

}
