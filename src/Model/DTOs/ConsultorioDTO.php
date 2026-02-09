<?php

namespace App\Model\DTOs;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "Consultorio", required: ["direccion", "ciudad", "horario_apertura", "horario_cierre"])]
class ConsultorioDTO {
    #[OA\Property(example: "Avenida Valve 200")]
    private string $direccion;
    #[OA\Property(example: "Lomas de zamora")]
    private string $ciudad;
    #[OA\Property(example: "08:00")]
    private string $horario_apertura;
    #[OA\Property(example: "19:00")]
    private string $horario_cierre;
    #[OA\Property(example: "12")]
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