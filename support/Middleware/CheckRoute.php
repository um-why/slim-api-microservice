<?php
namespace Support\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Support\Engine\Helper;

class CheckRoute
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // 使用route.php文件定义的URL和METHOD方法，确保请求的有效性
        $route = Helper::getRoute($request);
        if (!isset($route['id'])) {
            $response = new Response();
            $response = $response->withStatus(404);
            return $response;
        }
        unset($route);

        return $response;
    }
}
