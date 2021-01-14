<?php
namespace Rapyd\Rapydmagento2\Api;

interface WebhookInterface
{
    /**
    * GET for Post api
    * @param mixed $rapyd_data
    * @return string
    */

    public function getPost($rapyd_data);

    /**
     * GET for Post api
     * @param mixed $rapyd_data
     * @return string
     */

    public function getRefund($rapyd_data);
}
