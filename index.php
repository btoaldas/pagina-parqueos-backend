<?php

// ini_set('display_errors', 0);

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\FineController;
use App\Controllers\ReportController;
use App\Controllers\RoleController;
use App\Controllers\SpaceController;
use App\Controllers\StorageController;
use App\Controllers\TicketController;
use App\Controllers\UserController;
use App\Controllers\VehicleController;
use App\Controllers\ZoneController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\JsonMiddleware;
use App\Utils\EnvLoader;
use App\Utils\ErrorHandler;
use App\Utils\Router;

EnvLoader::load();

$adminMiddleware = [
  [AuthMiddleware::class, 'checkJwt', 'admin'],
];

$empleadoMiddlware = [
  [AuthMiddleware::class, 'checkJwt', 'admin', 'empleado'],
];

$registeredMiddleware = [
  [AuthMiddleware::class, 'checkJwt'],
];

$router = new Router('/api/v1');

$router->addMiddleware(
  [CorsMiddleware::class, 'cors']
);

$router->addRoute('POST', '/auth/login', [AuthController::class, 'login'], [[JsonMiddleware::class, 'json']]);
$router->addRoute('POST', '/auth/register', [AuthController::class, 'register'], [[JsonMiddleware::class, 'json']]);

$router->addCrudRoute('/role', RoleController::class, $adminMiddleware);
$router->addCrudRoute('/user', UserController::class, $adminMiddleware);
$router->addCrudRoute('/zone', ZoneController::class, $adminMiddleware);
$router->addCrudRoute('/space', SpaceController::class, $adminMiddleware);
$router->addCrudRoute('/vehicle', VehicleController::class, $adminMiddleware);

$router->addRoute('GET', '/ticket', [TicketController::class, 'getAll'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket/completed/[id]', [TicketController::class, 'validateOut'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket/cancel/[id]', [TicketController::class, 'cancel'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket', [TicketController::class, 'register'], [[JsonMiddleware::class, 'json'], ...$empleadoMiddlware]);
$router->addRoute('GET', '/ticket/[id]', [TicketController::class, 'getOne'], $empleadoMiddlware);

$router->addRoute('GET', '/fine', [FineController::class, 'getAll'], $empleadoMiddlware);
$router->addRoute('POST', '/fine/pay/[id]', [FineController::class, 'pay'], $empleadoMiddlware);
$router->addRoute('POST', '/fine/cancel/[id]', [FineController::class, 'cancel'], $empleadoMiddlware);
$router->addRoute('GET', '/fine/[id]', [FineController::class, 'getOne'], $empleadoMiddlware);
$router->addRoute('POST', '/fine', [FineController::class, 'create'], $empleadoMiddlware);

$router->addRoute('GET', '/storage/fine/[filename]', [StorageController::class, 'getFineFile']);

$router->addRoute('GET', '/report/main', [ReportController::class, 'main']);
$router->addRoute('GET', '/report/stats', [ReportController::class, 'report']);

try {
  $router->handlerRequest();
} catch (RuntimeException $e) {
  ErrorHandler::handlerError($e->getMessage(), 500);
}
