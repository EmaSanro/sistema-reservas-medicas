<?php
namespace App\Shared;

use App\Shared\Exceptions\BusinessValidationException;
use DateTime;

abstract class Entity
{
    protected ?int $id = null;
    protected ?DateTime $created_at = null;
    protected ?DateTime $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    protected function maxLength(string $valor, string $max, string $campo)
    {
        if (mb_strlen($valor) > $max) {
            throw BusinessValidationException::forField($campo, "El campo {$campo} no puede contener mas de {$max} caracteres");
        }
    }
    protected function minLength(string $valor, string $min, string $campo)
    {
        if (mb_strlen($valor) < $min) {
            throw BusinessValidationException::forField($campo, "El campo {$campo} debe contener al menos {$min} caracteres");
        }
    }

    protected function matchPattern(string $valor, string $patron, string $campo, string $mensaje)
    {
        if (!preg_match($patron, $valor)) {
            throw BusinessValidationException::forField($campo, $mensaje);
        }
    }

    abstract protected static function fromDatabase(array $data): self;
}