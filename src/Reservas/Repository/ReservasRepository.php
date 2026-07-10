<?php

namespace App\Reservas\Repository;

use App\Auth\Model\Roles;
use App\Reservas\Exceptions\ReservaAlreadyCancelledException;
use App\Reservas\Exceptions\ReservaCompletedException;
use App\Reservas\Model\EstadoReserva;
use App\Reservas\Model\RecordatorioReserva;
use App\Reservas\Model\Reserva;
use App\Reservas\Validators\ReservaSearchValidator;
use App\Shared\Repository;
use App\Shared\Search\SearchQueryBuilder;
use PDO;

class ReservasRepository extends Repository
{

    protected function getTableName(): string
    {
        return "reservas";
    }

    protected function getEntityClass(): string
    {
        return Reserva::class;
    }

    public function obtenerReservasPorUsuarioId(int $id, string $rol, int $page = 1, int $limit = 10): array
    {
        $columna = ($rol == Roles::PACIENTE) ? "idpaciente" : "idprofesional";
        $sql = "SELECT * FROM reservas WHERE $columna = :id";
        return $this->findPaginatedByQuery($sql, ["id" => $id], $page, $limit);
    }

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array
    {
        $built = SearchQueryBuilder::build(ReservaSearchValidator::definitions(), $filtros);

        $sql = "SELECT * FROM reservas";
        if(!empty($built['where'])) {
            $sql .= " WHERE " . implode(" AND ", $built['where']);
        }

        return $this->findPaginatedByQuery($sql, $built['params'], $page, $limit);
    }

    // public function obtenerReservaEspecifica($idPaciente, $idProfesional, $fecha) {
    //     $reserva = $this->db->prepare("
    //         SELECT r.id, r.fecha_reserva, CONCAT(upa.nombre, ' ', upa.apellido) as paciente, CONCAT(upr.nombre, ' ', upr.apellido) as profesional 
    //         FROM reservas r 
    //         JOIN usuario upa ON upa.id = r.idpaciente 
    //         JOIN usuario upr ON upr.id = r.idprofesional
    //         WHERE idpaciente = ? AND idprofesional = ? and fecha_reserva = ?
    //     ");
    //     $reserva->execute([$idPaciente, $idProfesional, $fecha]);
    //     return $reserva->fetch();
    // }

    public function reservar(Reserva $reserva): Reserva
    {
        try {
            $this->db->beginTransaction();
            $reservar = $this->db->prepare("
                INSERT INTO reservas(idprofesional, idpaciente, fecha_reserva, estado) 
                VALUES(:idprofesional,:idpaciente,:fecha_reserva,:estado)
            ");
            $reservar->execute([
                "idprofesional" => $reserva->getIdProfesional(),
                "idpaciente" => $reserva->getIdPaciente(),
                "fecha_reserva" => $reserva->getFechaReserva(),
                "estado" => $reserva->getEstadoReserva()
            ]);

            $id = $this->db->lastInsertId();

            $this->db->commit();
            $reserva->setId((int) $id);

            return $reserva;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarReserva(int $id, Reserva $reserva): Reserva {
        try {
            $this->db->beginTransaction();
            $update = $this->db->prepare("
                UPDATE reservas SET fecha_reserva = :fecha_reserva, estado = :estado
                WHERE id = :id
            ");
            $update->execute([
                "fecha_reserva" => $reserva->getFechaReserva(),
                "estado" => $reserva->getEstadoReserva(),
                "id" => $id
            ]);

            if($update->rowCount() === 0) {
                throw new \Exception("No se pudo actualizar la reserva");
            }
            $id = $this->db->lastInsertId();

            $reserva->setId($id);

            $this->db->commit();

            return $reserva;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Devuelve una reserva CONFIRMADA que ocupe el slot pedido (mismo paciente
     * o mismo profesional a la misma fecha_reserva), o null si el slot esta libre.
     * Las reservas canceladas o completadas no bloquean nuevas reservas.
     */
    public function buscarCoincidencia(int $idPaciente, int $idProfesional, string $fecha): ?Reserva
    {
        $sql = "SELECT * FROM reservas
                WHERE (idpaciente = :idpaciente OR idprofesional = :idprofesional)
                  AND fecha_reserva = :fecha_reserva
                  AND estado = :estado
                LIMIT 1";
        return $this->findOneByQuery($sql, [
            "idpaciente" => $idPaciente,
            "idprofesional" => $idProfesional,
            "fecha_reserva" => $fecha,
            "estado" => EstadoReserva::CONFIRMADA,
        ]);
    }

    public function perteneceAlPaciente(int $id, int $idPaciente): Reserva|null
    {
        $sql = "SELECT * FROM reservas WHERE id = :id AND idPaciente = :idpaciente";
        $reserva = $this->findOneByQuery($sql, ["id" => $id, "idpaciente" => $idPaciente]);
        return $reserva;
    }

    public function cancelarReserva(Reserva $reserva): void
    {
        switch ($reserva->getEstadoReserva()) {
            case EstadoReserva::CANCELADA:
                throw new ReservaAlreadyCancelledException("La reserva ya se encuentra cancelada");
            case EstadoReserva::COMPLETADA:
                throw new ReservaCompletedException("No se puede cancelar una reserva ya completada");
        }

        $update = $this->db->prepare("
            UPDATE reservas SET estado = :estado, fecha_cancelacion = NOW() WHERE id = :id AND estado = :estadoActual AND fecha_reserva > NOW() + INTERVAL 24 HOUR
        ");
        $update->execute(["estado" => EstadoReserva::CANCELADA, "id" => $reserva->getId(), "estadoActual" => EstadoReserva::CONFIRMADA]);

        if($update->rowCount() === 0) {
            throw new \Exception("No se pudo cancelar la reserva");
        }
    }

    /**
     * Reservas CONFIRMADAS de mañana que todavía no se notificaron.
     *
     * @return list<RecordatorioReserva>
     */
    public function recordatoriosPendientes(): array
    {
        $sql = "
            SELECT r.id,
                   r.fecha_reserva,
                   CONCAT(pac.nombre, ' ', pac.apellido) AS paciente,
                   pac.email,
                   pac.telefono,
                   CONCAT(prof.nombre, ' ', prof.apellido) AS profesional
            FROM reservas r
            JOIN usuario pac  ON pac.id  = r.idpaciente
            JOIN usuario prof ON prof.id = r.idprofesional
            WHERE DATE(r.fecha_reserva) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              AND r.notificado = 0
              AND r.estado = :estado
        ";
        $stmt = $this->prepareAndExecute($sql, ["estado" => EstadoReserva::CONFIRMADA]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn(array $row): RecordatorioReserva => RecordatorioReserva::fromDatabase($row),
            $rows
        );
    }

    public function marcarComoNotificado(int $id): void
    {
        $reserva = $this->db->prepare("
            UPDATE reservas SET notificado = 1 WHERE id = :id");
        $reserva->execute(["id" => $id]);
    }

    public function tieneFuturasReservasProfesional(int $id): Reserva|null
    {
        $sql = "SELECT * FROM reservas 
                WHERE idprofesional = :idprofesional
                AND estado = :estado
                AND fecha_reserva > NOW()
                LIMIT 1";
        $reservas = $this->findOneByQuery($sql, ["idprofesional" => $id, "estado" => EstadoReserva::CONFIRMADA]);
        return $reservas;
    }

    public function tieneFuturasReservasPaciente(int $id): Reserva|null
    {
        $sql = "SELECT * FROM reservas
                WHERE idpaciente = :idpaciente
                AND estado = :estado
                AND fecha_reserva > NOW()
                LIMIT 1";
        $reservas = $this->findOneByQuery($sql, ["idpaciente" => $id, "estado" => EstadoReserva::CONFIRMADA]);
        return $reservas;
    }
}
