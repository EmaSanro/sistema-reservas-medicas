<?php
namespace App\Model;

use App\Model\DTOs\RespuestaReservaDTO;
use App\Shared\Entity;

class Reserva extends Entity
{
    private int $idPaciente;
    private int $idProfesional;
    private string $fecha_reserva;
    private string $estado;

    private function __construct()
    {

    }

    public static function create(int $idPaciente, int $idProfesional, string $fecha_reserva, string $estado): self
    {
        $reserva = new self();
        $reserva->setIdPaciente($idPaciente);
        $reserva->setIdProfesional($idProfesional);
        $reserva->setFechaReserva($fecha_reserva);
        $reserva->setEstado($estado);

        return $reserva;
    }

    public function setIdPaciente(int $idPaciente): void
    {
        $this->idPaciente = $idPaciente;
    }

    public function setIdProfesional(int $idProfesional): void
    {
        $this->idProfesional = $idProfesional;
    }

    public function setFechaReserva(string $fecha_reserva): void
    {
        $this->fecha_reserva = $fecha_reserva;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function getIdPaciente(): int
    {
        return $this->idPaciente;
    }

    public function getIdProfesional(): int
    {
        return $this->idProfesional;
    }

    public function getFechaReserva(): string
    {
        return $this->fecha_reserva;
    }

    public function getEstadoReserva(): string
    {
        return $this->estado;
    }

    public static function fromDatabase(array $data): self
    {
        $reserva = new self();
        $reserva->id = (int) $data["id"];
        $reserva->idPaciente = (int) $data["idpaciente"];
        $reserva->idProfesional = (int) $data["idprofesional"];
        $reserva->fecha_reserva = $data["fecha_reserva"];
        $reserva->estado = $data["estado"];

        return $reserva;
    }
}
