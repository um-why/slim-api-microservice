<?php
namespace Support\Engine;

use Medoo\Medoo as MedooBasic;

final class Medoo
{
    private static $instance;

    public static function getInstance(): MedooBasic
    {
        if (static::$instance === null) {
            static::$instance = new MedooBasic($_ENV['database']);
        }
        return static::$instance;
    }

    private function __construct()
    {}
    private function __clone()
    {}
    private function __wakeup()
    {}
}
