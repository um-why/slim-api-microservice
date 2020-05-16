<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// 载入配置信息
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(ROOT_PATH . '.env');
unset($dotenv);
$_ENV = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'app.php';
$_ENV['service'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'service.php';

// 注入monolog服务
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
            ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR .
            'logs' . DIRECTORY_SEPARATOR . 'app-' . date('Ymd') . '.log'
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

// $app->get('/demo', 'Api\Controllers\DemoController:index');

foreach ($_ENV['service'] as $k => $v) {
    if (!isset($v['method']) || !isset($v['url'])) {
        continue;
    }
    $v['url'] = strtolower($v['url']);
    $app->map(
        [$v['method']],
        '/' . $v['url'],
        function (Request $request, Response $response, $args) use ($k, $v) {
            $v['url'] = array_map(function ($v1) {
                $v1 = array_map(function ($v2) {
                    return ucfirst(strtolower($v2));
                }, explode('-', $v1));
                return implode('', $v1);
            }, explode('/', $v['url']));
            $v['url'] = implode('\\', $v['url']);
            try {
                $ref = new \ReflectionClass('Api\Service\\' . $v['url'] . 'Service');
                $instance = $ref->newInstance($this);
                $method = $ref->getMethod('ready');
                $method->invoke($instance, $request);
                $method = $ref->getMethod('action');
                $result = $method->invoke($instance);
            } catch (Support\Exception\ErrorNotice $e) {
                $response->getBody()->write(Support\Engine\Helper::returnNoticeMsg(
                    $e->getMessage(), $k, $e->getCode()
                ));

                // 程序异常日志
                $notice = Support\Engine\ErrorFormat::noticeParse($request, $response);
                $this->get(Psr\Log\LoggerInterface::class)->notice(json_encode($notice));

                return $response->withHeader('Content-Type', 'application/json');
            } catch (\Exception $e) {
                var_dump($e->getMessage(), $e->getCode());
                exit;
            }
            $response->getBody()->write(Support\Engine\Helper::returnSuccessMsg(
                $result
            ));
            return $response->withHeader('Content-Type', 'application/json');
        }
    );
}

return $app;
