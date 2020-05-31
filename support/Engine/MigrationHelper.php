<?php
namespace Support\Engine;

use Illuminate\Support\Facades\DB;

class MigrationHelper
{
    /**
     * 获取数据迁移文件的根目录路径
     */
    public static function migrateFileBasePath(): string
    {
        return ROOT_PATH . 'console' . DIRECTORY_SEPARATOR . 'Migrations';
    }

    /**
     * 获取迁移批次
     */
    public static function getCurrentBatch(): int
    {
        $infoRs = DB::table('migrations')->select('id', 'batch')
            ->orderBy('id', 'desc')->first();
        if (!isset($infoRs->id)) {
            return 1;
        } else {
            return $infoRs->batch + 1;
        }
    }

    /**
     * 迁移文件名转换为类名
     * @param string $fileName 迁移文件
     * @return string 类名
     */
    public static function getMigrateFileClass(string $fileName): string
    {
        $tmp = substr($fileName, 0, 4);
        if (!is_numeric($tmp)) {
            return '';
        }
        $fileName = substr($fileName, 5);
        $tmp = substr($fileName, 0, 2);
        if (!is_numeric($tmp)) {
            return '';
        }
        $fileName = substr($fileName, 3);
        $tmp = substr($fileName, 0, 2);
        if (!is_numeric($tmp)) {
            return '';
        }
        $fileName = substr($fileName, 3);
        $tmp = substr($fileName, 0, 6);
        if (!is_numeric($tmp)) {
            return '';
        }
        $fileName = substr($fileName, 7);
        $fileName = array_map(function ($v) {
            return ucfirst($v);
        }, explode('_', $fileName));

        $fileName = implode('', $fileName);
        return $fileName;
    }

    /**
     * 迁移表名转换为文件名
     * @param string $tableName 迁移表名
     * @return string 文件路径及文件名
     */
    public static function tablenameConvertFilename(string $tableName): string
    {
        $fileName = self::migrateFileBasePath() . DIRECTORY_SEPARATOR;
        $fileName .= date('Y_m_d_His_') . $tableName . '.php';
        return $fileName;
    }
}
