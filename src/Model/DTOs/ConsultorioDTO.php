<?php

namespace App\Model\DTOs;

class ConsultorioDTO {
    private string $direccion;
    private string $ciudad;
    private string $horario_apertura;
    private string $horario_cierre;
    private int|null $idProfesional;

    public function __construct(string $direccion, string $ciudad, string $horario_apertura, string $horario_cierre, int|null $idProfesional) {
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->horario_apertura = $horario_apertura;
        $this->horario_cierre = $horario_cierre;
        $this->idProfesional = $idProfesional ?? null;
    }
    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getCiudad(): string {
        return $this->ciudad;
    }

    public function getHorarioApertura(): string {
        return $this->horario_apertura;
    }

    public function getHorarioCierre(): string {
        return $this->horario_cierre;
    }

    public function getIdProfesional() : int {
        return $this->idProfesional;
    }

    public static function fromArray(array $input) {
        if(!isset($input['direccion'], $input['ciudad'], $input['horario_apertura'], $input['horario_cierre'])) {
            throw new \InvalidArgumentException("Datos incompletos para crear Consultorio(direccion, ciudad, horario_apertura, horario_cierre)");
        }
        if(!\DateTime::createFromFormat('H:i', $input['horario_apertura']) || !\DateTime::createFromFormat('H:i', $input['horario_cierre'])) {
            throw new \InvalidArgumentException('ERROR: el horario de apertura y cierre debe ser un formato válido (HH:MM)');
        }

        if(\DateTime::createFromFormat("H:i", $input['horario_apertura']) >= \DateTime::createFromFormat('H:i', $input['horario_cierre'])) {
            throw new \InvalidArgumentException('ERROR: el horario de apertura debe ser antes que el horario de cierre');
        }

        return new self(
            $input["direccion"],
            $input["ciudad"],
            $input["horario_apertura"],
            $input["horario_cierre"],
            $input["idProfesional"] ?? null
        );
    }
}