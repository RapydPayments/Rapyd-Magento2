<?php

namespace Rapyd\Rapydmagento2\Model;

class RapydPaymentMethodBank extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_bank';

    public function getCategory()
    {
        return "bank";
    }

}
