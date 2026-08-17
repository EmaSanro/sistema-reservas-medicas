<?php

namespace App\Auth\Model;

/**
 * Proyeccion de solo lectura usada exclusivamente por el flujo de login.
 *
 * No es una entidad: representa lo minimo necesario para verificar unas
 * credenciales y armar el payload del JWT. Es el unico tipo del proyecto
 * que transporta el hash de la password.
 */
final class CredencialesUsuario
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombreCompleto,
        public readonly string $rol,
        public readonly ?string $email,
        public readonly ?string $telefono,
        public readonly string $passwordHash,
        public readonly bool $activo,
    ) {}

    public static function fromDatabase(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['nombre'] . ' ' . $data['apellido'],
            $data['rol'],
            $data['email'],
            $data['telefono'],
            $data['password'],
            (bool) $data['activo'],
        );
    }
}
