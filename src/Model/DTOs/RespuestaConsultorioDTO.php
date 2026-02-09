<?php 

namespace App\Model\DTOs;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaConsultorio")]
class RespuestaConsultorioDTO {
    #[OA\Property(example: 24)]
    public readonly int $id;
    #[OA\Property(example: "Avenida Lovamba 234")]
    public readonly string $direccion;
    #[OA\Property(example: "Santa fe")]
    public readonly string $ciudad;
    #[OA\Property(example: "08:00 - 17:00")]
    public readonly string $horario;
    #[OA\Property(example: 12)]
    public readonly int|null $idprofesional;

    public function __construct(int $id, string $direccion, string $ciudad, string $horario, int $idprofesional) {
        $this->id = $id;
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->horario = $horario;
        $this->idprofesional = $idprofesional ?? null;
    }
}