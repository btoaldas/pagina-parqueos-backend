<?php

include_once __DIR__ . '/../utils/HttpError.php';

class Validator
{
  public static function validateEmail($email)
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      throw HttpError::BadRequest("Invalid Email");
  }

  public static function validatePassword($password, $length = 8)
  {
    if (strlen($password) < $length)
      throw HttpError::BadRequest("Password needs more than $length of length");
  }

  public static function validateRequiredFields($data, $requiredFields)
  {
    foreach ($requiredFields as $field) {
      if (empty($data[$field]))
        throw HttpError::BadRequest("Required Fields: " . implode(', ', $requiredFields));
    }
  }

  public static function isInt($data, $name)
  {
    $value = $data[$name];
    if (!(is_numeric($value) && (int)$value == $value))
      throw HttpError::BadRequest("$name is not an int number");
  }
}
