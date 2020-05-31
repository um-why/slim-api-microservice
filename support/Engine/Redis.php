<?php
namespace Support\Engine;

use Predis\Client;

class Redis
{
    public static function __callStatic(string $name, array $argument)
    {
        $client = new Client($_ENV['redis']['parameters'], $_ENV['redis']['options']);
        return $client->$name(...$argument);
    }

    private function __construct()
    {}
    private function __clone()
    {}
    private function __wakeup()
    {}
}
