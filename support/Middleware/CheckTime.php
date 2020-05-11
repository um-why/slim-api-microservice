<?php
namespace Support\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Support\Engine\Helper;

class CheckTime
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $params = Helper::getParams($request);
        if (!isset($params['app_time']) || strtotime($params['app_time']) === false) {
            $response = new Response();
            $response = $response->withStatus(423);
            return $response;
        }

        list($minTime, $maxTime) = $this->compareSize(strtotime($params['app_time']), time());
        $checkLength = 60 * 5;
        if ($_ENV['debug'] === true) {
            $checkLength = 3600 * 24;
        }
        if (($maxTime - $minTime) >= $checkLength) {
            $response = new Response();
            $response = $response->withStatus(408);
            return $response;
        }

        unset($params, $minTime, $maxTime, $checkLength);

        return $response;
    }

    private function compareSize(int $value1, int $value2): array
    {
        if ($value1 <= $value2) {
        } else {
            $tmp = 0;
            $tmp = $value1;
            $value1 = $value2;
            $value2 = $tmp;
            unset($tmp);
        }
        return [$value1, $value2];
    }
}
