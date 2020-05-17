<?php
namespace Console\Service\Creation;

use Exception;

class MakeHelper
{
    public static function generateFilePath(string $className)
    {
        $className = str_replace("\\", '/', $className);
        $className = trim($className, '/');

        if (strpos($className, '-') !== false) {
            $className = array_map(function ($v) {
                $v = ucfirst($v);
                return $v;
            }, explode('-', $className));
            $className = implode('', $className);
        }
        if (strpos($className, '/') !== false) {
            $className = array_map(function ($v) {
                $v = ucfirst($v);
                return $v;
            }, explode('/', $className));
            $className = implode('/', $className);
        }

        $fileName = ROOT_PATH . 'console' . DIRECTORY_SEPARATOR;
        $fileName .= 'Command' . DIRECTORY_SEPARATOR;

        if (strpos($className, '/') !== false) {
            $path = substr($className, 0, strrpos($className, '/'));
            $className = substr($className, strrpos($className, '/') + 1);

            $fileName .= $path . DIRECTORY_SEPARATOR;

            $path = str_replace('/', '\\', $path);
            $path = '\\' . $path;
        } else {
            $path = '';
        }
        $fileName .= $className . '.php';
        if (file_exists($fileName)) {
            throw new Exception('class file is exist');
        } else {
            if (!is_dir(dirname($fileName))) {
                mkdir(dirname($fileName), 0777, true);
            }
        }
        return [$className, $path, $fileName];
    }
}
