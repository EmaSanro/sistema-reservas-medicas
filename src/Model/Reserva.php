<?php
namespace App\Model;

use App\Model\DTOs\RespuestaReservaDTO;

class Reserva {
    private int $id;
    private int $idPaciente;
    private int $idProfesional;
    private string $fecha_reserva;

    public function __construct($id, $idPaciente, $idProfesional, $fecha_reserva) {
        $this->id = $id;
        $this->idPaciente = $idPaciente;
        $this->idProfesional = $idProfesional;
        $this->fecha_reserva = $fecha_reserva;
    }
    public function getId(): int {
        return $this->id;
    }

    public function getIdPaciente(): int {
        return $this->idPaciente;
    }

    public function getIdProfesional(): int {
        return $this->idProfesional;
    }

    public function getFechaReserva(): string {
        return $this->fecha_reserva;
    }

    public function toDTO() {
        return new RespuestaReservaDTO(
            $this->id,
            $this->idPaciente,
            $this->idProfesional,
            $this->fecha_reserva
        );
    }
}