<?php

namespace Rapyd\Rapyd\Model;

include_once __DIR__ . DIRECTORY_SEPARATOR . "RapydPaymentMethodAbstract.php";

class RapydPaymentMethodBank extends RapydPaymentMethodAbstract
{
    protected $_code = 'rapyd_bank';

    public function getCategory()
    {
        return "bank";
    }

}
