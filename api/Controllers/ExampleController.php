<?php
namespace Api\Controllers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Support\Engine\Medoo;

// use Support\Engine\RabbitMQ;

class ExampleController
{
    private $logger;

    private $db;

    public function __construct(
        LoggerInterface $logger,
        Capsule $db
    ) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info('/demo', $request->getQueryParams());

        $listRs = Medoo::getInstance()->select('migrations', '*', [
            'LIMIT' => 3,
        ]);
        var_dump($listRs);

        $listRs = $this->db->table('migrations')->limit(3)->get();
        var_dump($listRs);

        $listRs = DB::table('migrations')->limit(3)->get();
        var_dump($listRs);

        // RabbitMQ::publish('123', '234', []);

        $response->getBody()->write('api demo');

        return $response;
    }

    public function name(Request $request, Response $response, array $args = [])
    {
        $response->getBody()->write('hello ' . $args['name']);

        return $response;
    }

    public function user(Request $request, Response $response, array $args = [])
    {
        $response->getBody()->write('user is: ' . json_encode($args));

        return $response;
    }

    public function id(Request $request, Response $response, array $args = [])
    {
        $response->getBody()->write('id is: ' . $args['id']);

        return $response;
    }
}
