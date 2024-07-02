<?php

require '../vendor/autoload.php';
require '../config/diconfig.php';

//DEBUG
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');

use Src\Route\Router;

$router = new Router($container);
$router->dispatch();