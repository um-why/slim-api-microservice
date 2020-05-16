<?php
namespace Api\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class BaseService
{
    protected $logger;

    public static $errorMsg = [];

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerInterface::class);
    }

    protected function ready(Request $request): void
    {
    }

    protected function action(): array
    {
        return [];
    }
}
