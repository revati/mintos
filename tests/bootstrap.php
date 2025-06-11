<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';
