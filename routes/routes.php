<?php

require '../vendor/autoload.php';
require '../config/diconfig.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Src\Controller\UserController;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r) {


    $r->post('/login', [UserController::class, 'login']);
    $r->post('/register', [UserController::class, 'register']);
    $r->addGroup('/user', function (RouteCollector $r) {

        $r->get('/{id:[0-9]+}', [UserController::class, 'getData']);
        $r->put('/{id:[0-9]+}', [UserController::class, 'update']);
        $r->delete('/{id:[0-9]+}', [UserController::class, 'delete']);
    });
});
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['message' => 'Not found']);

        exit;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);

        exit;
    case Dispatcher::FOUND:
        [$controller, $method] = $handler;
        $vars = $routeInfo[2];
        $controller = $container->get($controller);
        $response = $controller->$method($vars);
        echo json_encode($response);

        exit;
}
