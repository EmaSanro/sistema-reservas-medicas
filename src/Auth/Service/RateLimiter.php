<?php

namespace App\Auth\Service;

use App\Auth\Exceptions\TooManyAttemptsException;
use App\Auth\Repository\AccessAttemptRepository;
use DateTimeImmutable;

/**
 * Limita los intentos de login y de registro para frenar la automatizacion.
 *
 * Se cuenta en dos claves independientes (identificador e IP) y se bloquea si
 * cualquiera supera su umbral:
 *
 *  - solo por IP no frena ataques distribuidos y castiga a quienes comparten
 *    NAT (una clinica entera sale por una sola IP);
 *  - solo por cuenta permite que un atacante bloquee a un usuario a proposito.
 *
 * El bloqueo escala con la cantidad de intentos y corre desde el ultimo, asi
 * que decae solo. Nunca es permanente: un bloqueo permanente convierte esta
 * proteccion en el ataque.
 */
final class RateLimiter
{
    /** Historial que se mira hacia atras para contar intentos. */
    private const WINDOW_MINUTES = 1440;

    /** Retencion de filas. Solo afecta al almacenamiento: las consultas ya estan acotadas por WINDOW_MINUTES. */
    private const RETENTION_HOURS = 48;

    /** 1 de cada N escrituras dispara la purga de filas viejas. */
    private const PURGE_PROBABILITY = 100;

    /**
     * intentos => minutos de bloqueo, de mayor a menor severidad.
     * Gana el primer nivel que aplica.
     */
    private const LEVELS_IDENTIFIER = [20 => 1440, 10 => 60, 5 => 15];

    /** Umbrales mas altos que los del identificador: una IP compartida tiene trafico legitimo de varias personas. */
    private const LEVELS_IP = [60 => 1440, 30 => 60, 15 => 15];

    private const LEVELS_REGISTRATION_IP = [30 => 1440, 15 => 60, 8 => 15];

    public function __construct(private readonly AccessAttemptRepository $repo) {}

    /**
     * @throws TooManyAttemptsException si el identificador o la IP estan bloqueados
     */
    public function checkLogin(string $identifier, string $ip): void
    {
        $this->assertNotBlocked(
            AccessAttemptRepository::ACTION_LOGIN,
            AccessAttemptRepository::KEY_IDENTIFIER,
            $this->normalize($identifier),
            self::LEVELS_IDENTIFIER
        );

        $this->assertNotBlocked(
            AccessAttemptRepository::ACTION_LOGIN,
            AccessAttemptRepository::KEY_IP,
            $ip,
            self::LEVELS_IP
        );
    }

    /**
     * Registra el fallo sobre ambas claves.
     *
     * Se cuenta el identificador tal como se envio, exista o no ese usuario:
     * si un identificador inexistente no acumulara intentos, comparar sus
     * respuestas con las de uno real revelaria que cuentas existen.
     */
    public function recordLoginFailure(string $identifier, string $ip): void
    {
        $this->repo->record(
            AccessAttemptRepository::ACTION_LOGIN,
            AccessAttemptRepository::KEY_IDENTIFIER,
            $this->normalize($identifier)
        );

        $this->repo->record(
            AccessAttemptRepository::ACTION_LOGIN,
            AccessAttemptRepository::KEY_IP,
            $ip
        );

        $this->maybePurge();
    }

    /**
     * Un login exitoso limpia el contador del identificador.
     *
     * El contador por IP se deja intacto a proposito: en un ataque de
     * credential stuffing el atacante acierta algunas cuentas, y no queremos
     * que cada acierto le resetee el limite de su IP.
     */
    public function clearLoginAttempts(string $identifier): void
    {
        $this->repo->clear(
            AccessAttemptRepository::ACTION_LOGIN,
            AccessAttemptRepository::KEY_IDENTIFIER,
            $this->normalize($identifier)
        );
    }

    /**
     * @throws TooManyAttemptsException si la IP esta bloqueada
     */
    public function checkRegistration(string $ip): void
    {
        $this->assertNotBlocked(
            AccessAttemptRepository::ACTION_REGISTRATION,
            AccessAttemptRepository::KEY_IP,
            $ip,
            self::LEVELS_REGISTRATION_IP
        );
    }

    /**
     * Se registran todos los intentos de registro, exitosos o no: el abuso a
     * frenar es el sondeo de emails, no el fallo.
     */
    public function recordRegistrationAttempt(string $ip): void
    {
        $this->repo->record(
            AccessAttemptRepository::ACTION_REGISTRATION,
            AccessAttemptRepository::KEY_IP,
            $ip
        );

        $this->maybePurge();
    }

    /**
     * @param array<int, int> $levels intentos => minutos de bloqueo
     * @throws TooManyAttemptsException
     */
    private function assertNotBlocked(string $action, string $keyType, string $keyValue, array $levels): void
    {
        $conteo = $this->repo->countSince($action, $keyType, $keyValue, self::WINDOW_MINUTES);

        if ($conteo['attempts'] === 0 || $conteo['last'] === null) {
            return;
        }

        $minutosBloqueo = $this->blockMinutesFor($conteo['attempts'], $levels);
        if ($minutosBloqueo === null) {
            return;
        }

        $bloqueadoHasta = (new DateTimeImmutable($conteo['last']))
            ->modify("+{$minutosBloqueo} minutes");

        $ahora = new DateTimeImmutable();
        if ($ahora >= $bloqueadoHasta) {
            return;
        }

        throw new TooManyAttemptsException($bloqueadoHasta->getTimestamp() - $ahora->getTimestamp());
    }

    /**
     * @param array<int, int> $levels intentos => minutos, ordenado de mayor a menor
     */
    private function blockMinutesFor(int $attempts, array $levels): ?int
    {
        foreach ($levels as $umbral => $minutos) {
            if ($attempts >= $umbral) {
                return $minutos;
            }
        }

        return null;
    }

    /**
     * Se normaliza antes de contar: sin esto, alternar mayusculas generaria
     * claves distintas y saltearia el limite, porque la busqueda del login es
     * case-insensitive por la collation de la tabla.
     */
    private function normalize(string $identifier): string
    {
        return mb_strtolower(trim($identifier));
    }

    /**
     * Purga oportunista, al estilo del GC de sesiones de PHP: el contenedor no
     * corre ningun cron, asi que depender de uno dejaria la tabla creciendo.
     */
    private function maybePurge(): void
    {
        if (random_int(1, self::PURGE_PROBABILITY) === 1) {
            $this->repo->purgeOlderThan(self::RETENTION_HOURS);
        }
    }
}
