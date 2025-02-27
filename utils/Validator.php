<?php

namespace App\Utils;

class Validator
{
  private array $ks;
  private array $data;

  public static function with(&$data, $ks = []): Validator
  {
    $val = new Validator();

    if (!is_array($ks))
      $ks = [$ks];

    $val->ks = $ks;
    $val->data = &$data;

    return $val;
  }

  public function required($nullable = false)
  {
    $nkeys = [];
    foreach ($this->ks as $key) {
      if (!array_key_exists($key, $this->data))
        throw HttpError::BadRequest("'$key' Is required");

      if (!is_null($this->data[$key])) {
        $nkeys[] = $key;
        continue;
      }

      if (!$nullable)
        throw HttpError::BadRequest("'$key' Is null");
    }
    $this->ks = $nkeys;
    return $this;
  }

  public function optional($nullable = false)
  {
    $nkeys = [];
    foreach ($this->ks as $key) {
      if (!array_key_exists($key, $this->data))
        continue;

      if (is_null($this->data[$key])) {
        if ($nullable)
          continue;
        else
          throw HttpError::BadRequest("'$key' Is null");
      }

      $nkeys[] = $key;
    }
    $this->ks = $nkeys;
    return $this;
  }

  public function setDefault($value)
  {
    foreach ($this->ks as $key)
      if (!array_key_exists($key, $this->data) || is_null($this->data[$key]))
        $this->data[$key] = $value;
    return $this;
  }

  public function isString()
  {
    foreach ($this->ks as $key)
      if (!is_string($this->data[$key]))
        throw HttpError::BadRequest("'$key'({$this->data[$key]}) Is not string");
    return $this;
  }

  public function minLength(int $length)
  {
    foreach ($this->ks as $key)
      if (strlen($this->data[$key]) < $length)
        throw HttpError::BadRequest("'$key' Length is less than $length");
    return $this;
  }

  public function maxLength(int $length)
  {
    foreach ($this->ks as $key)
      if (strlen($this->data[$key]) > $length)
        throw HttpError::BadRequest("'$key' Length is greater than $length");
    return $this;
  }

  public function isEmail()
  {
    foreach ($this->ks as $key)
      if (!filter_var($this->data[$key], FILTER_VALIDATE_EMAIL))
        throw HttpError::BadRequest("Invalid Email");
    return $this;
  }

  public function isInteger()
  {
    foreach ($this->ks as $key) {
      $value = $this->data[$key];
      if (!(is_numeric($value) && (int)$value == $value))
        throw HttpError::BadRequest("$key is not an int number");
    }
    return $this;
  }

  public function isNumb()
  {
    foreach ($this->ks as $key) {
      $value = $this->data[$key];
      if (!is_numeric($value))
        throw HttpError::BadRequest("$key is not a number");
    }
    return $this;
  }

  public function limitOffset($limit = '10', $offset = '0')
  {
    $this->ks = ['limit'];
    $this->setDefault($limit);
    $this->isInteger();
    $this->ks = ['offset'];
    $this->setDefault($offset);
    $this->isInteger();
    return $this;
  }
}
