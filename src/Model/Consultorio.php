<?php
namespace App\Model;

use App\Model\DTOs\RespuestaConsultorioDTO;
use App\Shared\Entity;

class Consultorio extends Entity
{
    private string $ciudad;
    private string $direccion;
    private string $horario_apertura;
    private string $horario_cierre;
    private int|null $idprofesional;

    private function __construct()
    {

    }

    public static function create(string $ciudad, string $direccion, string $horario_apertura, string $horario_cierre, int|null $idprofesional): self
    {
        $consultorio = new self();
        $consultorio->setCiudad($ciudad);
        $consultorio->setDireccion($direccion);
        $consultorio->setHorarioApertura($horario_apertura);
        $consultorio->setHorarioCierre($horario_cierre);
        $consultorio->setIdprofesional($idprofesional);

        return $consultorio;
    }

    public function setCiudad(string $ciudad): void
    {
        $this->maxLength($ciudad, 60, 'ciudad');
        $this->ciudad = $ciudad;
    }

    public function setDireccion(string $direccion): void
    {
        $this->maxLength($direccion, 100, 'direccion');
        $this->direccion = $direccion;
    }

    public function setHorarioApertura(string $horario_apertura): void
    {
        $this->horario_apertura = $horario_apertura;
    }

    public function setHorarioCierre(string $horario_cierre): void
    {
        $this->horario_cierre = $horario_cierre;
    }

    public function setIdprofesional(int|null $idprofesional): void
    {
        $this->idprofesional = $idprofesional;
    }

    public function getCiudad(): string
    {
        return $this->ciudad;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getHorarioApertura(): string
    {
        return $this->horario_apertura;
    }

    public function getHorarioCierre(): string
    {
        return $this->horario_cierre;
    }

    public function getIdProfesional(): int|null
    {
        return $this->idprofesional ?? null;
    }

    public static function fromDatabase(array $data): self
    {
        $consultorio = new self();
        $consultorio->id = (int) $data["id"];
        $consultorio->ciudad = $data["ciudad"];
        $consultorio->direccion = $data["direccion"];
        $consultorio->horario_apertura = $data["horario_apertura"];
        $consultorio->horario_cierre = $data["horario_cierre"];
        $consultorio->idprofesional = $data["idprofesional"] ?? null;

        return $consultorio;
    }
}
