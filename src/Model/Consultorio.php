<?php
namespace App\Model;

use App\Model\DTOs\RespuestaConsultorioDTO;

class Consultorio {
    private int $id;
    private string $ciudad;
    private string $direccion;
    private string $horario_apertura;
    private string $horario_cierre;
    private int|null $idprofesional;

    public function __construct(int $id, string $ciudad, string $direccion, string $horario_apertura, string $horario_cierre, int|null $idprofesional) {
        $this->id = $id;
        $this->ciudad = $ciudad;
        $this->direccion = $direccion;
        $this->horario_apertura = $horario_apertura;
        $this->horario_cierre = $horario_cierre;
        $this->idprofesional = $idprofesional ?? null;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCiudad(): string {
        return $this->ciudad;
    }

    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getHorarioApertura(): string {
        return $this->horario_apertura;
    }

    public function getHorarioCierre(): string {
        return $this->horario_cierre;
    }

    public function getIdProfesional(): int|null {
        return $this->idprofesional;
    }

    public function toDTO() {
        return new RespuestaConsultorioDTO(
            $this->id,
            $this->direccion,
            $this->ciudad,
            "$this->horario_apertura - $this->horario_cierre",
            $this->idprofesional ?? null
        );
    }
}