<?php

declare(strict_types=1);

namespace Src\Route;

use DI\Container;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Src\Middleware\JwtMiddleware;
use Src\Controller\{UserController, AuthController, AddressController};

class Router
{
    private Dispatcher $dispatcher;

    public function __construct(
        private Container $container
    ) {
        $this->dispatcher = simpleDispatcher($this->getRoutes());
    }

    public function dispatch()
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        try {
            $uri = rawurldecode($uri);
            $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    http_response_code(404);
                    echo json_encode(['status' => 'NOT_FOUND', 'message' => 'Route not found']);
                    exit;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    http_response_code(405);
                    echo json_encode(['status' => 'NOT_ALLOWED', 'message' => 'Method not allowed']);
                    exit;

                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    [$controller, $method] = $handler;
                    $vars = $routeInfo[2];

                    // Check if the route requires authentication
                    if (!str_starts_with($uri, '/login') && !str_starts_with($uri, '/register')) {
                        $next = function () use ($controller, $method, $vars) {
                            $controller = $this->container->get($controller);
                            $response = $controller->$method($vars);
                            echo json_encode($response);
                        };
                        (new JwtMiddleware())->handle($next);
                    } else {
                        $controller = $this->container->get($controller);
                        $response = $controller->$method($vars);
                        echo json_encode($response);
                    }
                    break;
            }
        } catch (\Exception $e) {
            // TODO Log the error
            http_response_code(500);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }

    private function getRoutes(): callable
    {
        return function (RouteCollector $r) {
            $r->post('/login',              [AuthController::class,     'login']);
            $r->post('/register',           [UserController::class,     'register']);

            $r->addGroup('/user', function (RouteCollector $r) {
                $r->get('/address',         [AddressController::class,  'getUserAddresses']);
                $r->get('/list',            [UserController::class,     'listUsers']);

                $r->get('/{id:[0-9]+}',     [UserController::class,     'getData']);
                $r->put('/{id:[0-9]+}',     [UserController::class,     'update']);
                $r->delete('/{id:[0-9]+}',  [UserController::class,     'delete']);
            });

            $r->addGroup('/address', function (RouteCollector $r) {
                $r->get('/list',            [AddressController::class,  'listAddresses']);
                $r->get('/{id:[0-9]+}',     [AddressController::class,  'getData']);
                $r->post('/new',            [AddressController::class,  'new']);
                $r->put('/{id:[0-9]+}',     [AddressController::class,  'update']);
                $r->delete('/{id:[0-9]+}',  [AddressController::class,  'delete']);
            });
        };
    }
}
