<?php

require_once __DIR__ . '/ErrorHandler.php';

$pathparams = [];
$queryparams = [];

class Router
{
  private $routes = [];
  private $middlewares = [];

  public function addCrudRoute($path, $cClass, $middlewares = [])
  {
    $this->addRoute('GET', $path, [$cClass, 'getAll'], $middlewares);
    $this->addRoute('GET', $path . '/[id]', [$cClass, 'getOne'], $middlewares);
    $this->addRoute('POST', $path, [$cClass, 'create'], $middlewares);
    $this->addRoute('PUT', $path . '/[id]', [$cClass, 'update'], $middlewares);
    $this->addRoute('DELETE', $path . '/[id]', [$cClass, 'delete'], $middlewares);
  }

  public function addRoute(string $method, $path, $handler, $middlewares = [])
  {
    $this->routes[] = [
      'method' => $method,
      'path' => $_ENV['PATH_BASE'] . $path,
      'handler' => $handler,
      'middlewares' => $middlewares,
    ];
  }

  public function addMiddleware($middleware)
  {
    $this->middlewares[] = $middleware;
  }

  public function handlerRequest()
  {
    global $pathparams;
    global $queryparams;

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];

    $urlParts = parse_url($requestUri);

    $path = $urlParts['path'];

    if (isset($urlParts['query'])) {
      parse_str($urlParts['query'], $queryParams_);
      $queryparams = $queryParams_;
    }

    foreach ($this->routes as $route) {
      $pattern = $this->convertPathToRegex($route['path']);

      if ($route['method'] != $requestMethod)
        continue;
      if (!preg_match($pattern, $path, $matches))
        continue;

      $pathparams = $matches;

      // Global middlewares
      foreach ($this->middlewares as $middleware) {
        call_user_func($middleware);
      }

      // Specific middleware
      foreach ($route['middlewares'] as $middleware) {
        $this->callMiddleware($middleware);
      }

      $this->callInstance($route['handler']);
      return;
    }

    ErrorHandler::handlerError('Route Not Found!', 404);
  }

  private function callMiddleware($middleware)
  {
    if (is_array($middleware)) {
      $mClass = $middleware[0];
      $method = $middleware[1];
      $middlewareParams = array_slice($middleware, 2);

      $middlewareInstance = new $mClass();
      call_user_func_array([$middlewareInstance, $method], $middlewareParams);
    } else {
      call_user_func($middleware);
    }
  }

  private function callInstance($handler)
  {
    list($cClass, $method) = $handler;
    $object = new $cClass();
    call_user_func([$object, $method]);
  }

  private function convertPathToRegex(string $path): string
  {
    $pattern = preg_replace('/\[(\w+)\]/', '(?P<$1>\w+)', $path);
    return "#^" . $pattern . '$#';
  }
}
