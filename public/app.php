<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// 载入配置信息
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(ROOT_PATH . '.env');
unset($dotenv);
$_ENV = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'app.php';
$_ENV['route'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'route.php';

$containerBuilder = new DI\ContainerBuilder();
if ($_ENV['debug'] == false) {
    $containerBuilder->enableCompilation(ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR . 'cache');
}
$containerBuilder->addDefinitions([
    Psr\Log\LoggerInterface::class => function () {
        $logger = new Monolog\Logger($_ENV['app_id']);

        $processor = new Monolog\Processor\UidProcessor();
        $logger->pushProcessor($processor);

        $handler = new Monolog\Handler\StreamHandler(
            ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log'
        );
        $logger->pushHandler($handler);

        return $logger;
    },
]);
AppFactory::setContainer($containerBuilder->build());

$app = AppFactory::create();
// $app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// 请求安全验证
$app->add(new Support\Middleware\CheckSecurity());
$app->add(new Support\Middleware\CheckAuth());
$app->add(new Support\Middleware\CheckTime());
$app->add(new Support\Middleware\CheckRoute());
$app->add(new Support\Middleware\CheckRoute());
$app->add(new Support\Middleware\ProcessParamsSpecial());

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write('hello world');

    return $response;
});

$app->get('/demo', 'Api\Controllers\DemoController:index');

return $app;
