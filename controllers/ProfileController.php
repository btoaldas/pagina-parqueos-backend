<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class ProfileController
{
  private $userService;

  public function __construct()
  {
    $this->userService = new UserService();
  }
}
