<?php
namespace Api\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Support\Exception\ErrorNotice;

class DemoService extends BaseService
{
    public static $errorMsg = [
        1 => 'custome notice 1',
        2 => 'custome notice 2',
        3 => 'custome notice 3',
    ];

    public function ready(Request $request): void
    {
        $params = $request->getQueryParams();
        $this->logger->info('1');

        if (!isset($params['demo'])) {
            throw new ErrorNotice(1);
        }
        if (!isset($params['test'])) {
            throw new ErrorNotice(2);
        }
        if (!isset($params['ceshi'])) {
            throw new ErrorNotice(3);
        }
    }

    public function action(): array
    {
        return [
            0 => 1,
            1 => 2,
        ];
    }
}
