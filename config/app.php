<?php

return [
    'app_url' => isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost',
    'debug' => isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] == 'true' ? true : false,
    // 项目唯一标识
    'app_id' => isset($_ENV['APP_ID']) ? $_ENV['APP_ID'] : 0,

    'database' => [
        'database_type' => isset($_ENV['DB_CONNECTION']) ? $_ENV['DB_CONNECTION'] : '',
        'database_name' => isset($_ENV['DB_DATABASE']) ? $_ENV['DB_DATABASE'] : '',
        'server' => isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : '',
        'port' => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '',
        'username' => isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : '',
        'password' => isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '',
        'charset' => isset($_ENV['DB_CHARSET']) ? $_ENV['DB_CHARSET'] : '',
        'collation' => isset($_ENV['DB_COLLATION']) ? $_ENV['DB_COLLATION'] : '',
    ],
    'redis' => [
        'parameters' => [
            'host' => isset($_ENV['REDIS_HOST']) ? $_ENV['REDIS_HOST'] : '',
            'port' => isset($_ENV['REDIS_PORT']) ? $_ENV['REDIS_PORT'] : '',
        ],
        'options' => [
            'password' => isset($_ENV['REDIS_PASSWORD']) ? $_ENV['REDIS_PASSWORD'] : '',
        ],
    ],

    // 接口授权用户列表
    'authorization' => [
        isset($_ENV['AUTH_1_NAME']) ? $_ENV['AUTH_1_NAME'] : 1 => [
            'app_id' => isset($_ENV['AUTH_1_ID']) ? $_ENV['AUTH_1_ID'] : 1,
            'app_secret' => isset($_ENV['AUTH_1_SECRET']) ? $_ENV['AUTH_1_SECRET'] : 1,
        ],
        isset($_ENV['AUTH_2_NAME']) ? $_ENV['AUTH_2_NAME'] : 2 => [
            'app_id' => isset($_ENV['AUTH_2_ID']) ? $_ENV['AUTH_2_ID'] : 2,
            'app_secret' => isset($_ENV['AUTH_2_SECRET']) ? $_ENV['AUTH_2_SECRET'] : 2,
        ],
        isset($_ENV['AUTH_3_NAME']) ? $_ENV['AUTH_3_NAME'] : 3 => [
            'app_id' => isset($_ENV['AUTH_3_ID']) ? $_ENV['AUTH_3_ID'] : 3,
            'app_secret' => isset($_ENV['AUTH_3_SECRET']) ? $_ENV['AUTH_3_SECRET'] : 3,
        ],
    ],
];
