<?php

namespace Rapyd\Rapydmagento2\lib;

class RapydCategories
{
    private static $instance = null;
    private $categories;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new RapydCategories();
        }
        return self::$instance;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function setCategories($categories)
    {
        $this->categories = $categories;
    }
}
