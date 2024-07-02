<?php

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;
use DI\ContainerBuilder;

//Loading .env file
$dotenv = Dotenv::createImmutable('../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => function () {
        $host = 'mysql';
        $port = $_ENV['MYSQL_PORT'];
        $dbname = $_ENV['MYSQL_DATABASE'];

        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    },
]);

$container = $containerBuilder->build();
