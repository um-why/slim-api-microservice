<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

$_ENV['service'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'service.php';

$_ENV['restful'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'restful.php';

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('hello world');
        return $response;
    });

    // API接口定义
    foreach ($_ENV['restful'] as $v) {
        if (!isset($v[0]) || !isset($v[1]) ||
            !isset($v[2]) || isset($v[3])
        ) {
            continue;
        }

        $app->map([$v[0]], $v[1], $v[2]);
    }

    // 服务接口实现
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
                    $response->getBody()->write(
                        Support\Engine\Helper::returnNoticeMsg(
                            $e->getMessage(), $k, $e->getCode()
                        )
                    );

                    // 程序异常日志
                    $notice = Support\Engine\ErrorFormat::noticeParse($request, $response);
                    $this->get(Psr\Log\LoggerInterface::class)->notice('接口异常', $notice);

                    return $response->withHeader('Content-Type', 'application/json');
                } catch (\Exception $e) {
                    var_dump($e->getMessage(), $e->getCode());
                    exit;
                }

                $response->getBody()->write(Support\Engine\Helper::returnSuccessMsg($result));
                return $response->withHeader('Content-Type', 'application/json');
            }
        )->add(new Support\Middleware\CheckSecurity())
            ->add(new Support\Middleware\CheckAuth())
            ->add(new Support\Middleware\CheckTime())
            ->add(new Support\Middleware\CheckRoute());
    }
};
