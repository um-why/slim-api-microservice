<?php
namespace Console\Service;

use Exception;
use Illuminate\Support\Facades\DB;
use Support\Engine\MigrationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationRollback extends Command
{
    protected static $defaultName = 'migrate:rollback';

    protected function configure()
    {
        $this->setDescription('回滚数据库迁移');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $batch = MigrationHelper::getCurrentBatch();
        $batch -= 1;
        if ($batch <= 0) {
            $output->writeln('<info>无回滚</>');
            return 0;
        }

        $taskRs = $this->getRollbackList($batch);
        if (!isset($taskRs[0])) {
            $output->writeln('<info>无需回滚</>');
            return 0;
        }

        foreach ($taskRs as $v) {
            if (!isset($v['id']) || !isset($v['migration'])) {
                continue;
            }
            $output->write($v['migration'] . "\t");
            $runRs = $this->runRollback($v['id'], $v['migration'], $output);
            if ($runRs == true) {
                $output->write('<info>rollback</>' . PHP_EOL);
            } else {
                $output->write(PHP_EOL);
            }
        }

        return 0;
    }

    /**
     * 获取待回滚的文件列表
     * @param int $batch 迁移批次
     */
    private function getRollbackList(int $batch): array
    {
        $listRs = DB::table('migrations')->select('id', 'migration')
            ->where('batch', $batch)
            ->orderBy('id', 'desc')
            ->get();
        $return = array();
        foreach ($listRs as $v) {
            $return[] = [
                'id' => $v->id,
                'migration' => $v->migration,
            ];
        }
        return $return;
    }

    /**
     * 运行迁移回滚
     * @param int $migrateId 迁移记录ID
     * @param string $fileName 迁移文件名称
     */
    private function runRollback(int $migrateId, string $fileName, OutputInterface $output): bool
    {
        $filePath = MigrationHelper::migrateFileBasePath();
        $filePath .= DIRECTORY_SEPARATOR . $fileName . '.php';
        if (!file_exists($filePath)) {
            throw new Exception($fileName . '文件不存在');
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
        if (!is_array($classMethod) || !in_array('down', $classMethod)) {
            return false;
        }
        unset($classMethod);

        $migrate = new $className;
        try {
            $migrate->down();
            unset($className, $migrate);
        } catch (Exception $e) {
            $output->write(PHP_EOL);
            $output->writeln('<error>' . $e->getMessage() . '</>');
            return false;
        }

        $delRs = DB::table('migrations')->where([
            'id' => $migrateId,
            'migration' => $fileName,
        ])->delete();
        if ($delRs !== 1) {
            throw new Exception('迁移文件回滚后，删除迁移记录错误');
        }

        return true;
    }
}
