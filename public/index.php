<?php

date_default_timezone_set('Asia/Shanghai');
define("ROOT_PATH", dirname(__DIR__) . DIRECTORY_SEPARATOR);
require ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use Slim\Factory\AppFactory;

// 载入配置信息
require_once ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'settings.php';

// PHP-DI 容器
$containerBuilder = new DI\ContainerBuilder();
if ($_ENV['debug'] == false) {
    $containerBuilder->enableCompilation(ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR . 'cache');
}
$dependencies = require ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'dependencies.php';
$dependencies($containerBuilder);
AppFactory::setContainer($containerBuilder->build());

// APP 实例
$app = AppFactory::create();
// $app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// 定义DB门面
Illuminate\Support\Facades\DB::setFacadeApplication([
    'db' => $app->getContainer()
        ->get(Illuminate\Database\Capsule\Manager::class)
        ->getDatabaseManager(),
    'date' => new Illuminate\Support\DateFactory,
]);

// 通用中间件
$app->add(new Support\Middleware\ProcessParamsSpecial());

// 路由定义
$routes = require ROOT_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'routes.php';
$routes($app);

$app->run();
