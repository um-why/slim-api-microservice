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
        foreach ($_ENV['service'] as $k => $v) {
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

    /**
     * 返回错误信息
     * @return code APP_ID(...) + API_ID(000) + CODE(000)
     * APP_ID   项目唯一
     * API_ID   URL唯一
     * CODE     接口唯一
     */
    public static function returnNoticeMsg(string $msg, int $apiId = 0, int $code = 0): string
    {
        if ($apiId <= 0 || $apiId > 999) {
            $apiId = 0;
        }
        $apiId = str_pad(strval($apiId), 3, '0', STR_PAD_LEFT);

        if ($code <= 0 || $code > 999) {
            $code = 0;
        }
        $code = str_pad(strval($code), 3, '0', STR_PAD_LEFT);
        return json_encode([
            'code' => $_ENV['app_id'] . $apiId . $code,
            'message' => $msg,
        ]);
    }

    public static function returnSuccessMsg(array $data, int $code = 0): string
    {
        return json_encode([
            'code' => $code,
            'message' => 'succ',
            'data' => $data,
        ]);
    }
}
