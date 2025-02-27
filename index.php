<?php

// ini_set('display_errors', 0);
declare(strict_types=1);

require_once 'utils/EnvLoader.php';

EnvLoader::load();

require_once 'utils/Response.php';
require_once 'utils/Validator.php';
require_once 'utils/ErrorHandler.php';
require_once 'utils/JWT.php';

require_once 'controllers/AuthController.php';
require_once 'controllers/RoleController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/ZoneController.php';
require_once 'controllers/SpaceController.php';

require_once 'controllers/TicketController.php';

require_once 'middlewares/AuthMiddleware.php';

require_once 'utils/Router.php';

$adminMiddleware = [
  [AuthMiddlware::class, 'checkJwt', 'admin'],
];

$registeredMiddleware = [
  [AuthMiddlware::class, 'checkJwt'],
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
