<?php

// ini_set('display_errors', 0);

// cdeclare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\RoleController;
use App\Controllers\SpaceController;
use App\Controllers\TicketController;
use App\Controllers\UserController;
use App\Controllers\ZoneController;
use App\Middlewares\AuthMiddleware;
use App\Utils\EnvLoader;
use App\Utils\ErrorHandler;
use App\Utils\Router;

EnvLoader::load();

$adminMiddleware = [
  [AuthMiddleware::class, 'checkJwt', 'admin'],
];

$registeredMiddleware = [
  [AuthMiddleware::class, 'checkJwt'],
];

$router = new Router('/api/v1');

$router->addRoute('POST', '/auth/login', [AuthController::class, 'login']);
$router->addRoute('POST', '/auth/register', [AuthController::class, 'register']);

$router->addCrudRoute('/role', RoleController::class, $adminMiddleware);
$router->addCrudRoute('/user', UserController::class, $adminMiddleware);
$router->addCrudRoute('/zone', ZoneController::class, $adminMiddleware);
$router->addCrudRoute('/space', SpaceController::class, $adminMiddleware);

$router->addRoute('GET', '/ticket', [TicketController::class, 'getAll'], $registeredMiddleware);
$router->addRoute('GET', '/ticket/[id]', [TicketController::class, 'getOne'], $registeredMiddleware);

try {
  $router->handlerRequest();
} catch (RuntimeException $e) {
  ErrorHandler::handlerError($e->getMessage(), 500);
}
