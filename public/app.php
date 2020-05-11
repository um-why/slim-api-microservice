<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

// 载入配置信息
$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '.env');
unset($dotenv);
$_ENV = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'app.php';
$_ENV['route'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'route.php';

$app = AppFactory::create();
// $app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// 请求安全验证
$app->add(new Support\Middleware\CheckSecurity());
$app->add(new Support\Middleware\CheckAuth());
$app->add(new Support\Middleware\CheckTime());
$app->add(new Support\Middleware\CheckRoute());

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write('hello world');

    return $response;
});

$app->post('/demo', function (Request $request, Response $response, $args) {
    $response->getBody()->write('test');

    return $response;
});

return $app;
