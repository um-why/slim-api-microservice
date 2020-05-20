<?php
namespace Console\Service;

use Console\Service\Creation\MakeHelper;
// use Exception;
use Hprose\ResultMode;
use Hprose\Socket\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartRpcServer extends Command
{
    protected static $defaultName = 'server:rpc';

    protected function configure()
    {
        $this->setDescription('开启由Hprose-RPC提供TCP服务');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = new Server($_ENV['hprose']['tcp_server_uri']);
        $server->setDebugEnabled($_ENV['debug']);
        $output->writeln('Hprose Tcp Server' . PHP_EOL);
        $output->writeln('服务器URI:<comment>' . $_ENV['hprose']['tcp_server_uri'] . '</>' . PHP_EOL);

        $functions = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'rpc.php';

        $defaultOptions = array(
            'mode' => ResultMode::Normal,
            'simple' => false,
            'oneway' => false,
            'async' => false,
            'passContext' => false,
        );
        $output->writeln('可调用方法:');

        foreach ($functions as $k => $v) {
            $server->addFunction(explode(':', $v), $k, $defaultOptions);
            $output->writeln('· <info>' . $k . '</>');
        }

        $server->start();
    }
}
