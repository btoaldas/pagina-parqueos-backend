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

require_once 'middlewares/AuthMiddleware.php';

require_once 'utils/Router.php';


$router = new Router();

$router->addRoute('POST', '/auth/login', [AuthController::class, 'login']);
$router->addRoute('POST', '/auth/register', [AuthController::class, 'register']);

$rolemiddlewares = [
  [AuthMiddlware::class, 'checkJwt', 'admin'],
];
$router->addRoute('GET', '/role', [RoleController::class, 'getAll'], $rolemiddlewares);
$router->addRoute('GET', '/role/[id]', [RoleController::class, 'getOne'], $rolemiddlewares);
$router->addRoute('POST', '/role', [RoleController::class, 'create'], $rolemiddlewares);
$router->addRoute('PUT', '/role/[id]', [RoleController::class, 'update'], $rolemiddlewares);
$router->addRoute('DELETE', '/role/[id]', [RoleController::class, 'delete'], $rolemiddlewares);

try {
  $router->handlerRequest();
} catch (RuntimeException $e) {
  ErrorHandler::handlerError($e->getMessage(), 500);
}
