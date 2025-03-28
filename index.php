<?php

// ini_set('display_errors', 0);

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\FineController;
use App\Controllers\ProfileController;
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
use App\Utils\HttpError;
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
$router->addRoute('POST', '/auth/request-password', [AuthController::class, 'requestPassword'], [[JsonMiddleware::class, 'json']]);
$router->addRoute('POST', '/auth/validate-request', [AuthController::class, 'validateToken'], [[JsonMiddleware::class, 'json']]);
$router->addRoute('POST', '/auth/update-password', [AuthController::class, 'updatePassword'], [[JsonMiddleware::class, 'json']]);

$router->addRoute('POST', '/auth/two-factor/login', [AuthController::class, 'loginWithAccess'], [[JsonMiddleware::class, 'json']]);
$router->addRoute('POST', '/auth/two-factor/token', [AuthController::class, 'tokenWithAccess'], [[JsonMiddleware::class, 'json']]);

$router->addCrudRoute('/role', RoleController::class, $adminMiddleware);
$router->addCrudRoute('/user', UserController::class, $adminMiddleware);
$router->addCrudRoute('/zone', ZoneController::class, $adminMiddleware);
$router->addCrudRoute('/space', SpaceController::class, $registeredMiddleware);
$router->addRoute('GET', '/space-available', [SpaceController::class, 'available', $registeredMiddleware]);
$router->addRoute('GET', '/space/zone/[id]', [SpaceController::class, 'getAllByZone', $empleadoMiddlware]);

$router->addRoute('GET', '/vehicle/without-user', [VehicleController::class, 'getWithNoUSer'], $empleadoMiddlware);
$router->addRoute('GET', '/vehicle/user/[id]', [VehicleController::class, 'getByUser'], $empleadoMiddlware);
$router->addRoute('PUT', '/vehicle/update', [VehicleController::class, 'updateUser'], [[JsonMiddleware::class, 'json'], ...$empleadoMiddlware]);
$router->addCrudRoute('/vehicle', VehicleController::class, $empleadoMiddlware);

$router->addRoute('POST', '/user/[id]/enable', [UserController::class, 'enable'], $adminMiddleware);
$router->addRoute('POST', '/user/[id]/disable', [UserController::class, 'disable'], $adminMiddleware);

$router->addRoute('GET', '/profile', [ProfileController::class, 'getProfile'], $registeredMiddleware);
$router->addRoute('POST', '/profile/update', [ProfileController::class, 'update'], [[JsonMiddleware::class, 'json'], ...$registeredMiddleware]);
$router->addRoute('POST', '/profile/password', [ProfileController::class, 'updatePassword'], [[JsonMiddleware::class, 'json'], ...$registeredMiddleware]);
$router->addRoute('GET', '/profile/tickets', [ProfileController::class, 'getTicketsFromUser'], $registeredMiddleware);
$router->addRoute('GET', '/profile/fines', [ProfileController::class, 'getFinesFromUser'], $registeredMiddleware);
$router->addRoute('GET', '/profile/vehicles', [ProfileController::class, 'getVehiclesFromUser'], $registeredMiddleware);

$router->addRoute('GET', '/ticket', [TicketController::class, 'getAll'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket/completed/[id]', [TicketController::class, 'validateOut'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket/cancel/[id]', [TicketController::class, 'cancel'], $empleadoMiddlware);
$router->addRoute('POST', '/ticket', [TicketController::class, 'register'], [[JsonMiddleware::class, 'json'], ...$empleadoMiddlware]);
$router->addRoute('GET', '/ticket/[id]', [TicketController::class, 'getOne'], $empleadoMiddlware);
$router->addRoute('GET', '/ticket/plate/[plate]', [TicketController::class, 'getAllByPlate'], $empleadoMiddlware);

$router->addRoute('GET', '/fine', [FineController::class, 'getAll'], $empleadoMiddlware);
$router->addRoute('POST', '/fine/pay/[id]', [FineController::class, 'pay'], $empleadoMiddlware);
$router->addRoute('POST', '/fine/cancel/[id]', [FineController::class, 'cancel'], $empleadoMiddlware);
$router->addRoute('GET', '/fine/[id]', [FineController::class, 'getOne'], $empleadoMiddlware);
$router->addRoute('POST', '/fine', [FineController::class, 'create'], $empleadoMiddlware);
$router->addRoute('GET', '/fine/plate/[plate]', [FineController::class, 'getAllByPlate'], $empleadoMiddlware);

$router->addRoute('GET', '/storage/fine/[filename]', [StorageController::class, 'getFineFile']);

$router->addRoute('GET', '/report/main', [ReportController::class, 'main'], $empleadoMiddlware);
$router->addRoute('GET', '/report/stats', [ReportController::class, 'report'], $empleadoMiddlware);
$router->addRoute('GET', '/report/pdf', [ReportController::class, 'downloadPdf']);
$router->addRoute('GET', '/report/xlsx', [ReportController::class, 'downloadExcel']);
$router->addRoute('GET', '/profile/report/xlsx/[id]', [ProfileController::class, 'downloadExcel']);

try {
  $router->handlerRequest();
} catch (HttpError $e) {
  ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
} catch (Exception $e) {
  ErrorHandler::handlerError($e->getMessage(), 500);
}
