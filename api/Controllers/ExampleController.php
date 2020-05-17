<?php
namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

// use Support\Engine\RabbitMQ;

class ExampleController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response, array $args = [])
    {
        $this->logger->info('/demo', $request->getQueryParams());

        // RabbitMQ::publish('123', '234', []);

        $response->getBody()->write('api demo');

        return $response;
    }
}
