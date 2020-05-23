<?php

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function () {
            $logger = new Logger($_ENV['app_id']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler(
                ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR .
                'logs' . DIRECTORY_SEPARATOR . 'app-' . date('Ymd') . '.log'
            );
            $logger->pushHandler($handler);

            return $logger;
        },
    ]);

    // sillevl/slim-database-example
    $containerBuilder->addDefinitions([
        Capsule::class => function () {},
    ]);
};
