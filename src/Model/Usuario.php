<?php

class Usuario {
    private int $id;
    private string $nombre;
    private string $apellido;
    private string $rol;
    private string $email;
    private string $telefono;
    private string $password;

    public function __construct(int $id, string $nombre, string $apellido, string $rol, string $email, string $telefono, string $password) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->password = $password;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getApellido(): string {
        return $this->apellido;
    }

    public function getRol(): string {
        return $this->rol;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelefono(): string {
        return $this->telefono;
    }
}