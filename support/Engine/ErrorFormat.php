<?php
namespace Support\Engine;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ErrorFormat
{
    public static function noticeParse(Request $request, Response $response)
    {
        $parse = [
            'request' => [
                'url' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
                'query' => $request->getQueryParams(),
                'body' => $request->getParsedBody(),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'body' => strval($response->getBody()),
            ],
        ];
        return $parse;
    }
}
