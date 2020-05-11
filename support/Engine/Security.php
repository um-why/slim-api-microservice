<?php
namespace Support\Engine;

class Security
{
    public static function checkParameter(array $params): bool
    {
        if (!isset($params['app_name']) || !isset($params['app_id']) ||
            !isset($params['app_secret'])
        ) {
            return false;
        }
        if (!isset($_ENV['authorization'][$params['app_name']])) {
            return false;
        }

        $authRs = $_ENV['authorization'][$params['app_name']];
        if (!isset($authRs['app_id']) || $authRs['app_id'] != $params['app_id'] ||
            !isset($authRs['app_secret'])
        ) {
            return false;
        }
        $secret = $params['app_secret'];
        $params['app_secret'] = $authRs['app_secret'];
        unset($authRs);
        ksort($params, SORT_STRING);
        $params = http_build_query($params);
        $params = md5($params);
        if ($params !== $secret) {
            return false;
        }
        return true;
    }

    public static function getSecret(array $params): string
    {
        if (!isset($params['app_name']) || !isset($params['app_id']) ||
            !isset($params['app_secret'])
        ) {
            return 'params error';
        }
        if (!isset($_ENV['authorization'][$params['app_name']])) {
            return 'config error';
        }

        $authRs = $_ENV['authorization'][$params['app_name']];
        if (!isset($authRs['app_id']) || $authRs['app_id'] != $params['app_id'] ||
            !isset($authRs['app_secret'])
        ) {
            return 'config error.';
        }
        $params['app_secret'] = $authRs['app_secret'];
        unset($authRs);
        ksort($params, SORT_STRING);
        $params = http_build_query($params);
        $params = md5($params);
        return $params;
    }
}
