<?php

date_default_timezone_set('Asia/Shanghai');

define("ROOT_PATH", dirname(__DIR__) . DIRECTORY_SEPARATOR);

require ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$app = require ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'app.php';

$app->run();
