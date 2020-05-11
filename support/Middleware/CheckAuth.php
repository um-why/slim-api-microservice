<?php
namespace Support\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Support\Engine\Helper;

class CheckAuth
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $route = Helper::getRoute($request);
        if (!isset($route['auth'])) {
            $response = new Response();
            $response = $response->withStatus(403);
            return $response;
        }

        $params = Helper::getParams($request);
        if (!isset($params['app_name'])) {
            $response = new Response();
            $response = $response->withStatus(400);
            return $response;
        }

        $authRs = explode(',', $route['auth']);
        if (!in_array($params['app_name'], $authRs)) {
            $response = new Response();
            $response = $response->withStatus(410);
            return $response;
        }
        unset($route, $params, $authRs);

        return $response;
    }
}
