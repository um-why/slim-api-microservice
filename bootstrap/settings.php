<?php

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(ROOT_PATH . '.env');
unset($dotenv);

$_ENV = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'app.php';
$_ENV['service'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'service.php';
$_ENV['restful'] = require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'restful.php';
