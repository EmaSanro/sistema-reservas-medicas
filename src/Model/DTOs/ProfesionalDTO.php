<?php
namespace App\Model\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "Profesional", required: ["nombre", "apellido", "profesion", "email", "telefono"])]
class ProfesionalDTO {
    #[Oa\Property(example: "Roberto")]
    private string $nombre;
    #[Oa\Property(example: "Falcao")]
    private string $apellido;
    #[Oa\Property(example: "Odontologo")]
    private string $profesion;
    #[Oa\Property(example: "roberFalcao@outlook.com")]
    private string $email;
    #[Oa\Property(example: "0984728910")]
    private string $telefono;

    public function __construct(string $nombre, string $apellido, string $profesion, string $email, string $telefono) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->profesion = $profesion;
        $this->email = $email;
        $this->telefono = $telefono;
    }

    public function getNombre() {
        return $this->nombre;
    }
    public function getApellido() {
        return $this->apellido;
    }

    public function getProfesion() {
        return $this->profesion;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public static function fromArray($input) {
        if(!isset($input["nombre"], $input["apellido"], $input["email"], $input["profesion"], $input["telefono"])) {
            throw new \InvalidArgumentException("ERROR: Todos los campos son requeridos(nombre, apellido, profesion, email, telefono)");
        }
        if(!filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("ERROR : Email invalido");
        }

        return new self(
            ucwords(strtolower($input["nombre"])), 
            ucwords(strtolower($input["apellido"])), 
            ucwords(strtolower($input["profesion"])), 
            $input["email"], 
            $input["telefono"]);
    } 
}