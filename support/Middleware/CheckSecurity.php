<?php
namespace Support\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Support\Engine\Helper;
use Support\Engine\Security;

class CheckSecurity
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $route = Helper::getServiceRoute($request);
        if (!isset($route['level'])) {
            $response = new Response();
            $response = $response->withStatus(412);
            return $response;
        }

        // 不进行安全效验
        if ($route['level'] == 1) {
            return $response;
        }

        $params = array();
        if ($route['level'] == 2) {
            // 使用 queryParams 进行安全效验
            $params = $request->getQueryParams();
        } elseif ($route['level'] == 3) {
            // 使用 queryParams 和 parsedBbody 进行安全效验
            $params = Helper::getParams($request);
        }
        if (!isset($params['app_id'])) {
            $response = new Response();
            $response = $response->withStatus(401);
            return $response;
        }

        if (Security::checkParameter($params) !== true) {
            if ($_ENV['debug'] === true && isset($params['is_debug'])) {
                echo "correct secret:\t" . Security::getSecret($params);
                exit;
            }
            $response = new Response();
            $response = $response->withStatus(407);
            return $response;
        }
        unset($route, $params);

        return $response;
    }
}
