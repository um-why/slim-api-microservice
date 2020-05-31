<?php
namespace Console\Service\Creation;

use Exception;
use Support\Engine\MigrationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigration extends Command
{
    protected static $defaultName = 'make:migration';

    protected function configure()
    {
        $this->setDescription('创建数据库迁移文件')
            ->addArgument('tablename', InputArgument::REQUIRED, '创建的数据库表名称');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $tablename = $input->getArgument('tablename');

        // 表名参数中非法字符检查
        $tablename = strtolower($tablename);
        $allowedCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        $allowedCharacters = str_split($allowedCharacters);
        $tablename = array_map(function ($v) use ($allowedCharacters) {
            if (!in_array($v, $allowedCharacters)) {
                throw new Exception('表名中的字符【' . $v . '】是非法字符');
            }
            return $v;
        }, str_split($tablename));
        $tablename = implode('', $tablename);
        unset($allowedCharacters);

        $isCreate = $this->isCreateTable($tablename);

        $className = array_map(function ($v) {
            return ucfirst($v);
        }, explode('_', $tablename));
        $className = implode('', $className);
        $fileName = MigrationHelper::tablenameConvertFilename($tablename);
        if ($isCreate === true) {
            $tablename = substr($tablename, 7, -6);

            $commandGenerateStr = $this->getMarkCreateStr();
            $commandGenerateStr = str_replace('{{tablename}}', $tablename, $commandGenerateStr);
        } else {
            $commandGenerateStr = $this->getMarkStr();
        }
        $commandGenerateStr = str_replace('{{classname}}', $className, $commandGenerateStr);

        $generateRs = file_put_contents($fileName, $commandGenerateStr);
        if ($generateRs == true) {
            $output->writeln('<info>文件已生成</>');
        } else {
            $output->writeln('<error>文件生成失败</>');
        }

        return 0;
    }

    /**
     * 是否新增数据表
     * @param string $tableName 表名称
     */
    private function isCreateTable(string $tableName): bool
    {
        $prefix = substr($tableName, 0, 7);
        $suffix = substr($tableName, -6);

        if ($prefix == 'create_' && $suffix == '_table') {
            return true;
        } else {
            return false;
        }
    }

    private function getMarkCreateStr()
    {
        $commandStr = <<<STR
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {{classname}} extends Migration
{
    public function up()
    {
        Schema::create('{{tablename}}', function (Blueprint \$table) {
            \$table->increments('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('{{tablename}}');
    }
}

STR;
        return $commandStr;
    }

    private function getMarkStr()
    {
        $commandStr = <<<STR
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {{classname}} extends Migration
{
    public function up()
    {
    }

    public function down()
    {
    }
}

STR;
        return $commandStr;
    }
}
