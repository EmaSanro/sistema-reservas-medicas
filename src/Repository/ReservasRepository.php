<?php

namespace App\Repository;

use App\Exceptions\Reservas\ReservaAlreadyCancelledException;
use App\Exceptions\Reservas\ReservaCompletedException;
use App\Model\EstadoReserva;
use App\Model\Reserva;
use App\Model\Roles;
use App\Shared\Repository;

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

    public function obtenerReservasPorUsuarioId(int $id, string $rol): array
    {
        $columna = ($rol == Roles::PACIENTE) ? "idpaciente" : "idprofesional";
        $sql = "SELECT * FROM reservas WHERE $columna = :id";
        $data = $this->findByQuery($sql, ["id" => $id]);
        return $data;
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

    public function reservar(Reserva $reserva, int $idPaciente): Reserva
    {
        try {
            $this->db->beginTransaction();
            $reservar = $this->db->prepare("
                INSERT INTO reservas(idprofesional, idpaciente, fecha_reserva, estado) VALUES(:idprofesional,:idpaciente,:fecha_reserva,:estado)
            ");
            $reservar->execute([
                "idprofesional" => $reserva->getIdProfesional(),
                "idpaciente" => $idPaciente,
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

    public function buscarCoincidencia(int $idPaciente, int $idProfesional, string $fecha): Reserva|null
    {
        $sqlProfesional = "SELECT 1 FROM profesional WHERE idprofesional = :idprofesional";
        $profesional = $this->findOneByQuery($sqlProfesional, ["idprofesional" => $idProfesional]);
        if (!$profesional) {
            throw new \DomainException();
        }
        $sqlCoincidencia = "
            SELECT 1 FROM reservas 
            WHERE (idpaciente = :idpaciente OR idprofesional = :idprofesional) AND fecha_reserva = :fecha_reserva";
        $coincidencia = $this->findOneByQuery($sqlCoincidencia, [
            "idpaciente" => $idPaciente,
            "idprofesional" => $idProfesional,
            "fecha_reserva" => $fecha
        ]);
        return $coincidencia;
    }

    public function perteneceAlPaciente(int $id, int $idPaciente): Reserva|null
    {
        $sql = "SELECT 1 FROM reservas WHERE id = :id AND idPaciente = :idpaciente";
        $reserva = $this->findOneByQuery($sql, ["id" => $id, "idpaciente" => $idPaciente]);
        return $reserva;
    }

    public function cancelarReserva(Reserva $reserva): bool
    {
        switch ($reserva->getEstadoReserva()) {
            case EstadoReserva::CANCELADA:
                throw new ReservaAlreadyCancelledException("La reserva ya se encuentra cancelada");
            case EstadoReserva::COMPLETADA:
                throw new ReservaCompletedException("No se puede cancelar una reserva ya completada");
        }

        $update = $this->db->prepare("
            UPDATE reserva SET estado = :estado fecha_cancelacion = NOW() WHERE id = :id AND estado = :estado AND fecha_reserva > NOW() + INTERVAL 24 HOUR
        ");
        $update->execute(["estado" => EstadoReserva::CANCELADA, "id" => $reserva->getId(), "fecha_reserva" => EstadoReserva::CONFIRMADA]);

        return $update->rowCount() > 0;
    }

    public function ReservasPendientesNotificacion(): array
    {
        $sql = "
            SELECT r.*, pac.nombre as paciente, pac.email, pac.telefono, prof.nombre as profesional FROM reservas r
            JOIN usuario pac ON pac.id = r.idpaciente
            JOIN usuario prof ON prof.id = r.idprofesional
            WHERE DATE(r.fecha_reserva) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND r.notificado = 0
        ";
        $reservas = $this->findByQuery($sql);
        return $reservas;
    }

    public function marcarComoNotificado(int $id): void
    {
        $reserva = $this->db->prepare("
            UPDATE reservas SET notificado = 1 WHERE id = :id");
        $reserva->execute(["id" => $id]);
    }

    public function tieneFuturasReservasProfesional(int $id): Reserva|null
    {
        $sql = "SELECT 1 FROM reservas 
                WHERE idprofesional = :idprofesional
                AND estado = :estado
                AND fecha_reserva > NOW()
                LIMIT 1";
        $reservas = $this->findOneByQuery($sql, ["idprofesional" => $id, "estado" => EstadoReserva::CONFIRMADA]);
        return $reservas;
    }

    public function tieneFuturasReservasPaciente(int $id): Reserva|null
    {
        $sql = "SELECT 1 FROM reservas
                WHERE idpaciente = :idpaciente
                AND estado = :estado
                AND fecha_reserva > NOW()
                LIMIT 1";
        $reservas = $this->findOneByQuery($sql, ["idpaciente" => $id, "estado" => EstadoReserva::CONFIRMADA]);
        return $reservas;
    }
}
