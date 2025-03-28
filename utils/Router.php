<?php

namespace App\Utils;

use App\Middlewares\JsonMiddleware;

class Router
{
  public static array $pathparams = [];
  public static array $queryparams = [];
  public static array $body = [];

  private $routes = [];
  private $middlewares = [];
  private $base = "";

  public function __construct($base = "")
  {
    $this->base = $base;
  }

  public function addCrudRoute($path, $cClass, $middlewares = [])
  {
    $this->addRoute('GET', $path, [$cClass, 'getAll'], $middlewares);
    $this->addRoute('GET', $path . '/[id]', [$cClass, 'getOne'], $middlewares);
    $this->addRoute('POST', $path, [$cClass, 'create'], [[JsonMiddleware::class, 'json'], ...$middlewares]);
    $this->addRoute('PUT', $path . '/[id]', [$cClass, 'update'], [[JsonMiddleware::class, 'json'], ...$middlewares]);
    $this->addRoute('DELETE', $path . '/[id]', [$cClass, 'delete'], $middlewares);
  }

  public function addRoute(string $method, $path, $handler, $middlewares = [])
  {
    $this->routes[] = [
      'method' => $method,
      'path' => $_ENV['PATH_BASE'] . $this->base . $path,
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
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];

    $urlParts = parse_url($requestUri);

    $path = $urlParts['path'];

    if (isset($urlParts['query'])) {
      parse_str($urlParts['query'], $queryParams_);
      self::$queryparams = $queryParams_;
    }

    // Global middlewares
    foreach ($this->middlewares as $middleware) {
      $this->callMiddleware($middleware);
    }

    foreach ($this->routes as $route) {
      $pattern = $this->convertPathToRegex($route['path']);

      if ($route['method'] != $requestMethod)
        continue;
      if (!preg_match($pattern, $path, $matches))
        continue;

      self::$pathparams = $matches;

      // Global middlewares
      // foreach ($this->middlewares as $middleware) {
      // $this->callMiddleware($middleware);
      //}

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
    $pattern = preg_replace('/\[(\w+)\]/', '(?P<$1>[^/]+)', $path);
    return "#^" . $pattern . '$#';
  }
}
