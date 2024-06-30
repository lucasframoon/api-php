<?php

//DEBUG
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require '../vendor/autoload.php';
require '../config/diconfig.php';

use Src\Middleware\JwtMiddleware;
use function FastRoute\simpleDispatcher;
use FastRoute\{Dispatcher, RouteCollector};
use Src\Controller\{UserController, AuthController, AddressController};

$dispatcher = simpleDispatcher(function (RouteCollector $r) {

    $r->post('/login', [AuthController::class, 'login']);
    $r->post('/register', [UserController::class, 'register']);

    $r->addGroup('/user', function (RouteCollector $r) {
        $r->get('/address', [AddressController::class, 'getUserAddresses']);

        $r->get('/{id:[0-9]+}', [UserController::class, 'getData']);
        $r->put('/{id:[0-9]+}', [UserController::class, 'update']);
        $r->delete('/{id:[0-9]+}', [UserController::class, 'delete']);
    });

    $r->addGroup('/address', function (RouteCollector $r) {
        $r->get('/{id:[0-9]+}', [AddressController::class, 'getData']);
        $r->post('/new', [AddressController::class, 'new']);
        $r->put('/{id:[0-9]+}', [AddressController::class, 'update']);
        $r->delete('/{id:[0-9]+}', [AddressController::class, 'delete']);
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
        $handler = $routeInfo[1];
        [$controller, $method] = $handler;
        $vars = $routeInfo[2];

        // Check if the route is protected and requires authentication
        if (str_starts_with($uri, '/user')) {
            $next = function () use ($controller, $method, $vars) {
                global $container;
                $controller = $container->get($controller);
                $response = $controller->$method($vars);
                echo json_encode($response);
            };
            (new JwtMiddleware())->handle($next);
        } else {
            $controller = $container->get($controller);
            $response = $controller->$method($vars);

            echo json_encode($response);
        }
        break;
}
