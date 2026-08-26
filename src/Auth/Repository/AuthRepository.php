<?php
namespace App\Auth\Repository;

use App\Auth\Model\CredencialesUsuario;
use App\Auth\Model\Usuario;
use App\Shared\Repository;

class AuthRepository extends Repository {

    protected function getTableName(): string {
        return "usuario";
    }

    protected function getEntityClass(): string {
        return Usuario::class;
    }

    /**
     * Unico metodo del proyecto que proyecta la columna password.
     * Devuelve una proyeccion, no una entidad: el resto del sistema nunca
     * deberia tener acceso al hash.
     */
    public function buscarCredenciales(string $emailOTelefono): ?CredencialesUsuario {
        $sql = "SELECT id, nombre, apellido, rol, email, telefono, activo, password
                FROM usuario
                WHERE email = :email OR telefono = :telefono
                LIMIT 1";

        $row = $this->fetchOneRow($sql, [
            "email" => $emailOTelefono,
            "telefono" => $emailOTelefono,
        ]);

        return $row === null ? null : CredencialesUsuario::fromDatabase($row);
    }

    /**
     * $excluirId permite ignorar al propio usuario al actualizar sus datos,
     * para que reenviar su mismo email no cuente como duplicado.
     */
    public function emailEnUso(string $email, ?int $excluirId = null): bool {
        return $this->valorEnUso("email", $email, $excluirId);
    }

    public function telefonoEnUso(string $telefono, ?int $excluirId = null): bool {
        return $this->valorEnUso("telefono", $telefono, $excluirId);
    }

    /**
     * @param string $columna Solo se invoca con literales de esta clase; nunca
     *                        con entrada del usuario.
     */
    private function valorEnUso(string $columna, string $valor, ?int $excluirId): bool {
        $sql = "SELECT 1 FROM usuario WHERE {$columna} = :valor";
        $params = ["valor" => $valor];

        if ($excluirId !== null) {
            $sql .= " AND id <> :excluirId";
            $params["excluirId"] = $excluirId;
        }

        return $this->existsByQuery($sql . " LIMIT 1", $params);
    }
}
