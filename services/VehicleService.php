<?php

namespace App\Services;

use App\Models\VehicleModel;
use App\Models\UserModel;
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

  // Obtener todos los vehículos con paginación
  public function getAll($limit, $offset)
  {
    $vehicles = $this->vehicleModel->all($limit, $offset);
    $vehicles = array_map(function ($vehicle) {
      $vehicle['user'] = json_decode($vehicle['user'], true);
      return $vehicle;
    }, $vehicles);
    return $vehicles;
  }

  // Obtener un vehículo por su ID
  public function getOne($id, $throws = true)
  {
    $vehicle = $this->vehicleModel->get($id);

    if ($throws && !$vehicle) {
      throw HttpError::NotFound("Vehicle $id does not exist!");
    }

    $vehicle['user'] = json_decode($vehicle['user'], true);

    return $vehicle;
  }

  // Crear un nuevo vehículo
  public function create($data)
  {
    $user = $this->userModel->getOne($data['id_user']);
    if (!$user) {
      throw HttpError::BadRequest("There is no user with ID {$data['id_user']}");
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

    $user = $this->userModel->getOne($data['id_user']);
    if (!$user) {
      throw HttpError::BadRequest("There is no user with ID {$data['id_user']}");
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
