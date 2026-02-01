<?php 

namespace App\Model\DTOs;

class RespuestaConsultorioDTO {
    public readonly int $id;
    public readonly string $direccion;
    public readonly string $ciudad;
    public readonly string $horario;
    public readonly int|null $idprofesional;

    public function __construct(int $id, string $direccion, string $ciudad, string $horario, int $idprofesional) {
        $this->id = $id;
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->horario = $horario;
        $this->idprofesional = $idprofesional ?? null;
    }
}