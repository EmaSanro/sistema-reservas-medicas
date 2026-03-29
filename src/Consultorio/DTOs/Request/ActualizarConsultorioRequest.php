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
        private ?string $horarioApertura = null,
        #[OA\Property(example: "17:00", nullable: true)]
        private ?string $horarioCierre = null
    ) {}

    public function getCiudad(): ?string {
        return $this->ciudad;
    }

    public function getDireccion(): ?string {
        return $this->direccion;
    }

    public function getHorarioApertura(): ?string {
        return $this->horarioApertura;
    }

    public function getHorarioCierre(): ?string {
        return $this->horarioCierre;
    }

    public function setCiudad(?string $ciudad): void {
        $this->ciudad = $ciudad;
    }

    public function setDireccion(?string $direccion): void {
        $this->direccion = $direccion;
    }

    public function setHorarioApertura(?string $horarioApertura): void {
        $this->horarioApertura = $horarioApertura;
    }

    public function setHorarioCierre(?string $horarioCierre): void {
        $this->horarioCierre = $horarioCierre;
    }
}