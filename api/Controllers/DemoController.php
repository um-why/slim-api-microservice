<?php
namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Support\Engine\RabbitMQ;

class DemoController
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response, array $args = [])
    {
        $this->logger->info('123');

        RabbitMQ::publish('123', '234', []);

        RabbitMQ::consume('test', function ($message, $resolver) {
            var_dump($message->body);

            $resolver->reject($message, true);
        });

        $response->getBody()->write('hello');
        return $response;
    }
}
