<?php
namespace App\Model;

use App\Model\DTOs\RespuestaNotaDTO;
use App\Shared\Entity;

class Nota extends Entity
{
    private string $motivo_visita;
    private string $texto_nota;
    private int $reserva_id;
    private array $adjuntos = [];

    private function __construct()
    {

    }

    public static function create(string $motivo_visita, string $texto_nota, int $reserva_id): self
    {
        $nota = new self();
        $nota->setMotivoVisita($motivo_visita);
        $nota->setTextoNota($texto_nota);
        $nota->setReservaId($reserva_id);

        return $nota;
    }

    public function setMotivoVisita(string $motivo_visita): void
    {
        $this->maxLength($motivo_visita, 150, 'motivo_visita');
        $this->motivo_visita = $motivo_visita;
    }

    public function setTextoNota(string $texto_nota): void
    {
        $this->texto_nota = $texto_nota;
    }

    public function setReservaId(int $reserva_id): void
    {
        $this->reserva_id = $reserva_id;
    }

    public function getMotivoVisita(): string
    {
        return $this->motivo_visita;
    }

    public function getTextoNota(): string
    {
        return $this->texto_nota;
    }

    public function getReservaId(): int
    {
        return $this->reserva_id;
    }

    public function getAdjuntos(): array
    {
        return $this->adjuntos;
    }

    public function setAdjuntos(array $array): void
    {
        $this->adjuntos = $array;
    }

    public function agregarAdjuntos(ArchivoNota $archivo): void
    {
        $this->adjuntos[] = $archivo;
    }

    public function tieneAdjuntos(): bool
    {
        return !empty($this->adjuntos);
    }

    public function cantidadAdjuntos(): int
    {
        return count($this->adjuntos);
    }

    public static function fromDatabase(array $data): self
    {
        $nota = new self();
        $nota->id = (int) $data["id"];
        $nota->motivo_visita = $data["motivo_visita"];
        $nota->texto_nota = $data["texto_nota"];
        $nota->reserva_id = (int) $data["reserva_id"];

        return $nota;
    }
}
