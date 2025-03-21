<?php

namespace App\Services;

use App\Models\FineModel;
use App\Models\UserModel;
use App\Models\VehicleModel;
use App\Utils\AesEncryption;
use App\Utils\HttpError;
use App\Utils\UUID;
use DateTime;

class FineService
{
  private $fineModel;
  private $userModel;
  private $vehicleModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->vehicleModel = new VehicleModel();
    $this->fineModel = new FineModel();
  }

  public function getAll()
  {
    $values = $this->fineModel->all();
    $values = array_map(function ($value) {
      $value['amount'] = (float)$value['amount'];
      $value['vehicle'] = json_decode($value['vehicle'], true);
      $value['employ'] = json_decode($value['employ'], true);
      $value['employ']['name'] = AesEncryption::decrypt($value['employ']['name']);
      $value['employ']['lastname'] = AesEncryption::decrypt($value['employ']['lastname']);
      return $value;
    }, $values);
    return $values;
  }

  public function getAllByPlate(string $plate)
  {
    $values = $this->fineModel->allByPlate($plate);
    $values = array_map(function ($value) {
      $value['amount'] = (float)$value['amount'];
      $value['vehicle'] = json_decode($value['vehicle'], true);
      $value['employ'] = json_decode($value['employ'], true);
      $value['employ']['name'] = AesEncryption::decrypt($value['employ']['name']);
      $value['employ']['lastname'] = AesEncryption::decrypt($value['employ']['lastname']);
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->fineModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Fine $id does not exist!");

    $data['amount'] = (float)$data['amount'];

    $data['vehicle'] = json_decode($data['vehicle'], true);
    $data['employ'] = json_decode($data['employ'], true);
    $data['employ']['name'] = AesEncryption::decrypt($data['employ']['name']);
    $data['employ']['lastname'] = AesEncryption::decrypt($data['employ']['lastname']);

    return $data;
  }

  public function create($data, $image)
  {
    $vehicle = $this->vehicleModel->get($data['id_vehicle']);
    if (!$vehicle)
      HttpError::BadRequest("Vehicle {$data['id_ticket']} does not exist");

    $employ = $this->userModel->getOne($data['id_employ']);
    if (!$employ)
      HttpError::BadRequest("Employ {$data['id_employ']} does not exist");

    if (!($employ['role'] === 'empleado' || $employ['role'] === 'admin'))
      HttpError::BadRequest("Solo empleados y admins pueden crear una multa");

    $path = $_ENV['PATH_STORAGE'] !== '' ? $_ENV['PATH_STORAGE'] : __DIR__ . "/../storage";
    $path = $path . '/fine';
    $filename = UUID::v4() . ($image['type'] === 'image/jpeg' ? '.jpg' : '.png');
    $data['filename'] = $filename;

    $data['created_date'] = (new DateTime())->modify('-5 hours')->format('Y-m-d H:i:s');

    $id = $this->fineModel->create($data);

    move_uploaded_file($image['tmp_name'], "$path/$filename");

    return $id;
  }

  public function pay($id)
  {
    $ticket = $this->getOne($id);

    if ($ticket['state'] !== 'pendiente')
      throw HttpError::BadRequest("Can't pay this fine");

    $result = $this->fineModel->pay($id, (new DateTime())->modify('-5 hours')->format('Y-m-d H:i:s'));

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }

  public function cancel($id)
  {
    $fine = $this->getOne($id);

    if ($fine['state'] !== 'pendiente')
      throw HttpError::BadRequest("Can't cancel this fine");

    $result = $this->fineModel->cancel($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }

  public function getFinesFromUser(int $id)
  {
    return $this->fineModel->getByUser($id);
  }
}
