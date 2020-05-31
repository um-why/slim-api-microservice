<?php
namespace Console\Service;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Support\Engine\MigrationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationExecution extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        $this->setDescription('执行数据库迁移');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $initializationRs = $this->initiateMigration();
        if ($initializationRs !== true) {
            $output->writeln('<error>初始化数据迁移记录表错误</>');
            return 0;
        }

        $taskRs = $this->getMigrationFile();
        if (!isset($taskRs[0])) {
            $output->writeln('<info>无迁移</>');
            return 0;
        }

        $batch = MigrationHelper::getCurrentBatch();
        foreach ($taskRs as $v) {
            $output->write($v . "\t");
            $runRs = $this->runMigration($v, $batch, $output);
            if ($runRs == true) {
                $output->write('<info>succ.</>' . PHP_EOL);
            } else {
                $output->write(PHP_EOL);
            }
        }

        return 0;
    }

    /**
     * 初始化迁移记录表
     */
    private function initiateMigration(): bool
    {
        $isExist = DB::getSchemaBuilder()->hasTable('migrations');
        if ($isExist == true) {
            return true;
        }

        DB::getSchemaBuilder()->create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('migration', 255)->default('');
            $table->mediumInteger('batch', false, true)->default(0);
            $table->timestamp('created_at')->nullable();
        });
        return true;
    }

    /**
     * 获取待执行的迁移文件列表
     */
    private function getMigrationFile(): array
    {
        $fileRs = scandir(MigrationHelper::migrateFileBasePath());
        $result = array();
        foreach ($fileRs as $v) {
            $tmp = strpos($v, '.php');
            if ($tmp === false) {
                continue;
            }
            if (($tmp + 4) != strlen($v)) {
                continue;
            }
            $v = substr($v, 0, -4);
            $result[] = $v;
        }
        unset($fileRs);
        if (!isset($result[0])) {
            return [];
        }

        $runMigrations = DB::table('migrations')->pluck('migration')->toArray();
        foreach ($result as $k => $v) {
            if (in_array($v, $runMigrations)) {
                unset($result[$k]);
            }
        }
        unset($runMigrations);
        return array_values($result);
    }

    /**
     * 运行迁移文件
     * @param string $fileName 迁移文件名称
     * @param int $batch 迁移批次
     */
    private function runMigration(string $fileName, int $batch, OutputInterface $output): bool
    {
        $filePath = MigrationHelper::migrateFileBasePath();
        $filePath .= DIRECTORY_SEPARATOR . $fileName . '.php';
        if (!file_exists($filePath)) {
            return false;
        }
        $className = MigrationHelper::getMigrateFileClass($fileName);
        if ($className == '') {
            return false;
        }
        if (class_exists($className)) {
            return false;
        }

        require_once $filePath;
        unset($filePath);

        $classMethod = get_class_methods($className);
        if (!is_array($classMethod) || !in_array('up', $classMethod)) {
            return false;
        }
        unset($classMethod);

        $migrate = new $className;
        try {
            $migrate->up();
            unset($className, $migrate);
        } catch (Exception $e) {
            $output->write(PHP_EOL);
            $output->writeln('<error>' . $e->getMessage() . '</>');
            return false;
        }

        $lastId = DB::table('migrations')->insertGetId([
            'migration' => $fileName,
            'batch' => $batch,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if ($lastId <= 0) {
            throw new Exception('迁移文件执行后，插入到迁移记录表错误');
        }
        return true;
    }
}
