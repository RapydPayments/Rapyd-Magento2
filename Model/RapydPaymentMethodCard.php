<?php

namespace rapyd\rapydmagento2\Model;

class RapydPaymentMethodCard extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_card';

    public function getCategory()
    {
        return "card";
    }
}
