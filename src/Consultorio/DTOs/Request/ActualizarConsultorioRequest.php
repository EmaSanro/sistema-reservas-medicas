<?php
namespace App\Consultorio\DTOs\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "ActualizarConsultorioRequest")]
class ActualizarConsultorioRequest {

    public function __construct(
        #[OA\Property(example: "Tres Arroyos", nullable: true)]
        private ?string $ciudad = null,
        #[OA\Property(example: "Avenida Rivadavia 2200", nullable: true)]
        private ?string $direccion = null,
        #[OA\Property(example: "08:00", nullable: true)]
        private ?string $horario_apertura = null,
        #[OA\Property(example: "17:00", nullable: true)]
        private ?string $horario_cierre = null
    ) {}

    public function getCiudad(): ?string {
        return $this->ciudad;
    }

    public function getDireccion(): ?string {
        return $this->direccion;
    }

    public function getHorarioApertura(): ?string {
        return $this->horario_apertura;
    }

    public function getHorarioCierre(): ?string {
        return $this->horario_cierre;
    }

    public function setCiudad(?string $ciudad): void {
        $this->ciudad = $ciudad;
    }

    public function setDireccion(?string $direccion): void {
        $this->direccion = $direccion;
    }

    public function setHorarioApertura(?string $horario_apertura): void {
        $this->horario_apertura = $horario_apertura;
    }

    public function setHorarioCierre(?string $horario_cierre): void {
        $this->horario_cierre = $horario_cierre;
    }
}