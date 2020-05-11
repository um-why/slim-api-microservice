<?php
namespace Support\Engine;

use Psr\Http\Message\ServerRequestInterface as Request;

class Helper
{
    public static function getRoute(Request $request): array
    {
        $uriPath = $request->getUri()->getPath();
        $uriPath = ltrim($uriPath, '/');
        $requestMethod = $request->getMethod();

        $route = array();
        foreach ($_ENV['route'] as $k => $v) {
            if (!isset($v['url']) || !isset($v['method'])) {
                continue;
            }
            if ($v['url'] == $uriPath && $v['method'] == $requestMethod) {
                $route = $v;
                $route['id'] = $k;
                break;
            }
        }
        return $route;
    }

    public static function getParams(Request $request): array
    {
        $params = $request->getQueryParams();
        $params = array_merge($params, $request->getParsedBody());
        return $params;
    }
}
