<?php

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(ROOT_PATH . '.env');
unset($dotenv);

$_ENV = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'app.php';
