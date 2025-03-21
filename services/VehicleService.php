<?php

namespace App\Services;

use App\Models\VehicleModel;
use App\Models\UserModel;
use App\Utils\AesEncryption;
use App\Utils\HttpError;

class VehicleService
{
  private $vehicleModel;
  private $userModel;

  public function __construct()
  {
    $this->vehicleModel = new VehicleModel();
    $this->userModel = new UserModel();
  }

  public function getWithNoUser()
  {
    $vehicles = $this->vehicleModel->getWithNoUser();
    return $vehicles;
  }

  public function getByUser(int $id)
  {
    $vehicles = $this->vehicleModel->getByUser($id);
    return $vehicles;
  }

  public function updateUser(int $idVehicle, int $idUser)
  {
    $vehicle = $this->getOne($idVehicle);
    $user = $this->userModel->getOne($idUser);

    if (!$user) {
      throw HttpError::BadRequest("There is no user with ID $idUser");
    }

    $result = $this->vehicleModel->updateUser($idVehicle, $idUser);

    if (!$result) {
      throw HttpError::InternalServer("Server Error On Update");
    }

    return true;
  }

  // Obtener todos los vehículos con paginación
  public function getAll()
  {
    $vehicles = $this->vehicleModel->all();
    $vehicles = array_map(function ($vehicle) {
      if (is_null($vehicle['user'])) return $vehicle;
      $vehicle['user'] = json_decode($vehicle['user'], true);
      $vehicle['user']['name'] = AesEncryption::decrypt($vehicle['user']['name']);
      $vehicle['user']['lastname'] = AesEncryption::decrypt($vehicle['user']['lastname']);
      return $vehicle;
    }, $vehicles);
    return $vehicles;
  }

  public function getAllFromUser(int $id)
  {
    $vehicles = $this->vehicleModel->allFromUser($id);
    return $vehicles;
  }

  // Obtener un vehículo por su ID
  public function getOne($id, $throws = true)
  {
    $vehicle = $this->vehicleModel->get($id);

    if ($throws && !$vehicle) {
      throw HttpError::NotFound("Vehicle $id does not exist!");
    }

    if (is_null($vehicle['user'])) return $vehicle;

    $vehicle['user'] = json_decode($vehicle['user'], true);
    $vehicle['user']['name'] = AesEncryption::decrypt($vehicle['user']['name']);
    $vehicle['user']['lastname'] = AesEncryption::decrypt($vehicle['user']['lastname']);

    return $vehicle;
  }

  // Crear un nuevo vehículo
  public function create($data)
  {
    if (!is_null($data['id_user'])) {
      $user = $this->userModel->getOne($data['id_user']);
      if (!$user) {
        throw HttpError::BadRequest("There is no user with ID {$data['id_user']}");
      }
    }

    // Validar si la placa ya existe
    $existingVehicle = $this->vehicleModel->getByPlate($data['plate']);
    if ($existingVehicle) {
      throw HttpError::BadRequest("A vehicle with plate {$data['plate']} already exists!");
    }

    return $this->vehicleModel->create($data);
  }

  // Actualizar un vehículo por su ID
  public function update($id, $data)
  {
    $vehicle = $this->getOne($id);


    if (!is_null($data['id_user'])) {
      $user = $this->userModel->getOne($data['id_user']);
      if (!$user) {
        throw HttpError::BadRequest("There is no user with ID {$data['id_user']}");
      }
    }

    // Si la placa que se intenta actualizar es diferente a la actual, validar si ya existe
    if ($vehicle['plate'] !== $data['plate']) {
      $existingVehicle = $this->vehicleModel->getByPlate($data['plate']);
      if ($existingVehicle) {
        throw HttpError::BadRequest("A vehicle with plate {$data['plate']} already exists!");
      }
    }

    $result = $this->vehicleModel->update($id, $data);

    if (!$result) {
      throw HttpError::InternalServer("Server Error On Update");
    }

    return true;
  }

  // Eliminar un vehículo por su ID
  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->vehicleModel->delete($id);

    if (!$result) {
      throw HttpError::InternalServer("Server Error On Delete");
    }

    return true;
  }

  // Obtener un vehículo por su placa
  public function getByPlate($plate, $throws = true)
  {
    $vehicle = $this->vehicleModel->getByPlate($plate);

    if ($throws && !$vehicle) {
      throw HttpError::NotFound("Vehicle with plate $plate does not exist!");
    }

    $vehicle['user'] = json_decode($vehicle['user'], true);

    return $vehicle;
  }
}
