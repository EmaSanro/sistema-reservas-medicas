<?php
namespace App\Model\DTOs;

use OpenApi\Attributes as OA;
#[OA\Schema(schema: "Reserva", required: ["idProfesional", "fecha"])]
class ReservaDTO {

    #[OA\Property(example: 1)]
    private int $idProfesional;
    #[OA\Property(example: "2026-03-15 18:45:00")]
    private string $fecha;

    public function __construct(int $idProfesional, string $fecha) {
        $this->idProfesional = $idProfesional;
        $this->fecha = $fecha;
    }

    public function getIdProfesional() {
        return $this->idProfesional;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public static function fromArray($input) {
        if (!isset($input['idprofesional'], $input['fecha'])) {
            throw new \InvalidArgumentException('ERROR: Los campos de idprofesional y fecha son requeridos');
        }
        if (!is_numeric($input['idprofesional'])) {
            throw new \InvalidArgumentException('ERROR: idProfesional debe ser un numero valido');
        }

        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $input['fecha'])) {
            throw new \InvalidArgumentException('ERROR: La fecha debe ser un formato válido (YYYY-MM-DD HH:MM:SS)');
        }

        return new self($input['idprofesional'], $input['fecha']);
    }
}