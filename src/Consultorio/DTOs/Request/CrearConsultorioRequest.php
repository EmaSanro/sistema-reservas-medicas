<?php

namespace App\Consultorio\DTOs\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "CrearConsultorioRequest", required: ["direccion", "ciudad", "horario_apertura", "horario_cierre"])]
class CrearConsultorioRequest {
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

    public function __construct(string $ciudad, string $direccion, string $horario_apertura, string $horario_cierre, int|null $idProfesional) {
        $this->ciudad = $ciudad;
        $this->direccion = $direccion;
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

    public function getIdProfesional() : ?int {
        return $this->idProfesional;
    }
}