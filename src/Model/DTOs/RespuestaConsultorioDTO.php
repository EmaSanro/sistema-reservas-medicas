<?php 

namespace App\Model\DTOs;

class RespuestaConsultorioDTO {
    public readonly int $id;
    public readonly string $direccion;
    public readonly string $ciudad;
    public readonly string $horario;

    public function __construct(int $id, string $direccion, string $ciudad, string $horario) {
        $this->id = $id;
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->horario = $horario;
    }

    public static function fromArray(array $input) {
        return new self(
            $input["id"],
            $input["direccion"],
            $input["ciudad"],
            $input["horario_apertura"] . " - " . $input["horario_cierre"]
        );
    }
}