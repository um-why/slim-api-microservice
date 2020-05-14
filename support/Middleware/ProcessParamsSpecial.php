<?php
namespace Support\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ProcessParamsSpecial
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $params = $request->getQueryParams();
        $isChange = false;
        foreach ($params as $k => $v) {
            if (strpos($k, '?') === 0) {
                $params[substr($k, 1)] = $v;
                unset($params[$k]);
                $isChange = true;
                break;
            }
        }
        if ($isChange == true) {
            $request = $request->withQueryParams($params);
        }

        $response = $handler->handle($request);

        return $response;
    }
}
