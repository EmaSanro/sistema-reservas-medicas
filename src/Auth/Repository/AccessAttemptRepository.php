<?php

namespace App\Auth\Repository;

use App\Shared\Repository;
use LogicException;

/**
 * Acceso a la tabla de contadores del rate limiting.
 *
 * A diferencia del resto de los repositorios, esta tabla no mapea a ninguna
 * entidad de dominio: son contadores tecnicos. Se extiende Repository solo
 * para reutilizar prepareAndExecute() y fetchOneRow().
 */
final class AccessAttemptRepository extends Repository
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_REGISTRATION = 'registration';

    public const KEY_IDENTIFIER = 'identifier';
    public const KEY_IP = 'ip';

    protected function getTableName(): string
    {
        return "access_attempt";
    }

    /**
     * Esta tabla no tiene entidad asociada. Se lanza en vez de devolver un
     * valor de relleno para que findAll()/findById()/findByQuery() fallen
     * ruidosamente si alguien los invoca sobre este repositorio.
     */
    protected function getEntityClass(): string
    {
        throw new LogicException(
            "access_attempt no mapea a una entidad: usar los metodos especificos de AccessAttemptRepository."
        );
    }

    /**
     * Cantidad de intentos y fecha del ultimo, dentro de la ventana indicada.
     *
     * @return array{attempts: int, last: ?string}
     */
    public function countSince(string $action, string $keyType, string $keyValue, int $windowMinutes): array
    {
        // INTERVAL no admite placeholders en MySQL, asi que la ventana se
        // interpola. El cast a int la hace segura: siempre viene de una
        // constante del RateLimiter, nunca de entrada del usuario.
        $ventana = (int) $windowMinutes;

        $sql = "SELECT COUNT(*) AS attempts, MAX(created_at) AS last
                FROM access_attempt
                WHERE action = :action
                  AND key_type = :key_type
                  AND key_value = :key_value
                  AND created_at > NOW() - INTERVAL {$ventana} MINUTE";

        $row = $this->fetchOneRow($sql, [
            "action" => $action,
            "key_type" => $keyType,
            "key_value" => $keyValue,
        ]);

        return [
            "attempts" => (int) ($row['attempts'] ?? 0),
            "last" => $row['last'] ?? null,
        ];
    }

    public function record(string $action, string $keyType, string $keyValue): void
    {
        $sql = "INSERT INTO access_attempt (action, key_type, key_value)
                VALUES (:action, :key_type, :key_value)";

        $this->prepareAndExecute($sql, [
            "action" => $action,
            "key_type" => $keyType,
            "key_value" => $keyValue,
        ]);
    }

    public function clear(string $action, string $keyType, string $keyValue): void
    {
        $sql = "DELETE FROM access_attempt
                WHERE action = :action AND key_type = :key_type AND key_value = :key_value";

        $this->prepareAndExecute($sql, [
            "action" => $action,
            "key_type" => $keyType,
            "key_value" => $keyValue,
        ]);
    }

    public function purgeOlderThan(int $hours): void
    {
        $sql = "DELETE FROM access_attempt WHERE created_at < NOW() - INTERVAL " . (int) $hours . " HOUR";
        $this->prepareAndExecute($sql);
    }
}
