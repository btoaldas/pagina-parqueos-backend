<?php

namespace App\Controllers;

use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Validator;

class FineController
{
  //private $authService;

  public function __construct()
  {
    //$this->authService = new AuthService();
  }

  public function create()
  {
    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../storage/image.jpg');
    print_r(json_decode($_POST['json'], true));
  }
}
