<?php

return [
    ['GET', '/demo', 'Api\Controllers\ExampleController:index'],
    ['GET', '/demo/{name}', 'Api\Controllers\ExampleController:name'],
    ['GET', '/demo/user/[{user}]', 'Api\Controllers\ExampleController:user'],
    ['GET', '/demo/id/{id:[0-9]+}', 'Api\Controllers\ExampleController:id'],
];
