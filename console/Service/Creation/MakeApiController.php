<?php
namespace Console\Service\Creation;

use Console\Service\Creation\MakeHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeApiController extends Command
{
    protected static $defaultName = 'make:controller';

    protected function configure()
    {
        $this->setDescription('创建接口控制器')
            ->addArgument('classname', InputArgument::REQUIRED, '创建的接口控制器文件路径名/类名');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('classname');

        try {
            list($className, $path, $fileName) = MakeHelper::generateFilePath($className, 'api' . DIRECTORY_SEPARATOR . 'Controllers');
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
namespace Api\Controllers{{path}};

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class {{classname}}
{
    public function __construct()
    {
    }

    public function index(Request \$request, Response \$response)
    {
        \$params = \$request->getQueryParams();

        return \$response;
    }
}

STR;
        return $commandStr;
    }
}
