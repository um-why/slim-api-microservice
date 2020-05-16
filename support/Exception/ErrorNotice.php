<?php
namespace Support\Exception;

use Exception;

class ErrorNotice extends Exception
{
    public function __construct(int $code)
    {
        $errorMsg = $this->errorObject(debug_backtrace()[0]);
        $msg = '';
        if (!isset($errorMsg[$code])) {
            $code = 999;
            $msg = '未知错误';
        } else {
            $msg = $errorMsg[$code];
        }
        $this->message = $msg;
        $this->code = $code;
        unset($errorMsg);
    }

    private function errorObject(array $object): array
    {
        if (!isset($object['object']) || !is_object($object['object'])) {
            return [];
        }
        $trace = $object['object']->getTrace()[0];
        unset($object);
        if (!isset($trace['class']) || !class_exists($trace['class'])) {
            return [];
        }
        $vars = get_class_vars($trace['class']);
        unset($trace);
        if (!isset($vars['errorMsg']) || !is_array($vars['errorMsg'])) {
            return [];
        }
        return $vars['errorMsg'];
    }
}
