<?php
namespace Console\Service\Creation;

use Console\Service\Creation\MakeHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeApiService extends Command
{
    protected static $defaultName = 'make:apiservice';

    protected function configure()
    {
        $this->setDescription('创建服务接口')
            ->addArgument('classname', InputArgument::REQUIRED, '创建的服务接口文件路径名/类名');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('classname');

        try {
            list($className, $path, $fileName) = MakeHelper::generateFilePath($className, 'api' . DIRECTORY_SEPARATOR . 'Service');
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
namespace Api\Service{{path}};

use Api\Service\BaseService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Support\Exception\ErrorNotice;

class {{classname}} extends BaseService
{
    public static \$errorMsg = [
        1 => 'custome notice 1',
    ];

    public function ready(Request \$request): void
    {
        \$params = \$request->getQueryParams();
        \$this->logger->info('1');

        if (!isset(\$params['demo'])) {
            throw new ErrorNotice(1);
        }
    }

    public function action(): array
    {
        return [];
    }
}

STR;
        return $commandStr;
    }
}
