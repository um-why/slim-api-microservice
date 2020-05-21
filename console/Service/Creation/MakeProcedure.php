<?php
namespace Console\Service\Creation;

use Console\Service\Creation\MakeHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeProcedure extends Command
{
    protected static $defaultName = 'make:rpcclass';

    protected function configure()
    {
        $this->setDescription('创建RPC文件')
            ->addArgument('classname', InputArgument::REQUIRED, '创建的过程文件路径名/类名');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('classname');

        try {
            list($className, $path, $fileName) = MakeHelper::generateFilePath($className, 'console' . DIRECTORY_SEPARATOR . 'Procedure');
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</>');
            return 0;
        }

        $commandGenerateStr = $this->getMarkStr();
        $commandGenerateStr = str_replace('{{path}}', $path, $commandGenerateStr);
        $commandGenerateStr = str_replace('{{classname}}', $className, $commandGenerateStr);

        $generateRs = file_put_contents($fileName, $commandGenerateStr);
        if ($generateRs == true) {
            $output->writeln('<info>文件已生成</>');
        } else {
            $output->writeln('<error>文件生成失败</>');
        }

        return 0;
    }

    private function getMarkStr()
    {
        $commandStr = <<<STR
<?php
namespace Console\Procedure{{path}};

class {{classname}}
{

}

STR;
        return $commandStr;
    }
}
