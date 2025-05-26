<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = DotEnv::createMutable(__DIR__);
$dotenv->load();
$dotenv->required(['LAPOSTA_API_KEY', 'APPROVED_SENDER_ADDRESS']);
