<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Exceptions\Reservas\ReservaCompletedException;
use App\Exceptions\Reservas\ReservaNotFoundException;
use App\Model\EstadoReserva;
use App\Model\Reserva;
use App\Model\Roles;
use AppConfig\Database;
use PDO;

class ReservasRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTodas(): array {
        $query = $this->db->prepare("
            SELECT * FROM reservas 
        ");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        $reservas = [];
        foreach($data as $reserva) {
            $reservas[] = new Reserva(
                $reserva["id"],
                $reserva["idpaciente"],
                $reserva["idprofesional"],
                $reserva["fecha_reserva"],
                $reserva["estado"]
            );
        }
        return $reservas;
    }

    public function obtenerReservasPorUsuarioId(int $id, string $rol): array {
        $columna = ($rol == Roles::PACIENTE) ? "idpaciente" : "idprofesional";
        $query = $this->db->prepare("
            SELECT * FROM reservas WHERE $columna = ?
        ");
        $query->execute([$id]);
        $data = $query->fetchAll();
        $reservas = [];

        foreach($data as $reserva) {
            $reservas[] = new Reserva(
                $reserva["id"],
                $reserva["idpaciente"],
                $reserva["idprofesional"],
                $reserva["fecha_reserva"],
                $reserva["estado"]
            );
        }
        return $reservas;
    }

    public function obtenerReserva($id) {
        $stmtReserva = $this->db->prepare("
            SELECT * FROM reserva WHERE id = ?
        ");
        $stmtReserva->execute([$id]);
        $data = $stmtReserva->fetch(PDO::FETCH_ASSOC);
        if(!$data) return null;
        return new Reserva(
            $id,
            $data["idpaciente"],
            $data["idprofesional"],
            $data["fecha_reserva"],
            $data["estado"],
        );
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

    public function reservar($dto, $idPaciente): Reserva {
        try {
            $this->db->beginTransaction();
            $reservar = $this->db->prepare("
                INSERT INTO reservas(idprofesional, idpaciente, fecha_reserva, estado) VALUES(?,?,?,?)
            ");
            $reservaCreated = $reservar->execute([$dto->getIdProfesional(), $idPaciente, $dto->getFecha(), $dto->getEstadoReserva()]);
            $this->db->commit();
            if(!$reservaCreated) {
                throw new DatabaseException("Error al crear la reserva");
            }
            return new Reserva(
                $this->db->lastInsertId(),
                $idPaciente,
                $dto->getIdProfesional(),
                $dto->getFecha(),
                $dto->getEstadoReserva()
            );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("No se pudo realizar la reserva");
        }
        
    }

    public function buscarCoincidencia($idPaciente, $idProfesional, $fecha): bool {
        $profesional = $this->db->prepare("
            SELECT 1 FROM profesional WHERE idprofesional = ?
        ");
        $profesional->execute([$idProfesional]);
        if(!$profesional->fetch()) {
            throw new \DomainException();
        }
        $coincidencia = $this->db->prepare("
            SELECT 1 FROM reservas WHERE (idpaciente = ? OR idprofesional = ?) AND fecha_reserva = ? 
        ");
        $coincidencia->execute([$idPaciente, $idProfesional, $fecha]);
        return $coincidencia->rowCount() > 0;
    }

    public function perteneceAlPaciente($id, $idPaciente): mixed {
        $reserva = $this->db->prepare("
            SELECT 1 FROM reservas WHERE id = ? AND idPaciente = ?
        ");
        $reserva->execute([$id, $idPaciente]);
        return $reserva->fetch();
    }

    public function cancelarReserva(Reserva $reserva): bool {
        switch ($reserva->getEstadoReserva()) {
            case EstadoReserva::CANCELADA: return false;
            case EstadoReserva::COMPLETADA: throw new ReservaCompletedException("No se puede cancelar una reserva ya completada");
        }

        $update = $this->db->prepare("
            UPDATE reserva SET estado = ? fecha_cancelacion = NOW() WHERE id = ? AND estado = ? AND fecha_reserva > NOW() + INTERVAL 24 HOUR
        ");
        $update->execute([EstadoReserva::CANCELADA, $reserva->getId(), EstadoReserva::CONFIRMADA]);

        return $update->rowCount() > 0;
    }

    public function ReservasPendientesNotificacion(): array {
        $reserva = $this->db->prepare("
            SELECT r.*, pac.nombre as paciente, pac.email, pac.telefono, prof.nombre as profesional FROM reservas r
            JOIN usuario pac ON pac.id = r.idpaciente
            JOIN usuario prof ON prof.id = r.idprofesional
            WHERE DATE(r.fecha_reserva) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND r.notificado = 0
        ");
        $reserva->execute();
        return $reserva->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoNotificado($id): void {
        $reserva = $this->db->prepare("
            UPDATE reservas SET notificado = 1 WHERE id = ?");
        $reserva->execute([$id]);
    }

    public function tieneFuturasReservasProfesional($id) {
        $stmtReservas = $this->db->prepare("
            SELECT 1 FROM reservas 
            WHERE idprofesional = ?
                AND estado = ?
                AND fecha_reserva > NOW()
            LIMIT 1
        ");
        $stmtReservas->execute([$id, EstadoReserva::CONFIRMADA]);
        return $stmtReservas->fetch() !== false;
    }

    public function tieneFuturasReservasPaciente($id) {
        $stmtReservas = $this->db->prepare("
            SELECT 1 FROM reservas
            WHERE idpaciente = ?
                AND estado = ?
                AND fecha_reserva > NOW()
            LIMIT 1
        ");
        $stmtReservas->execute([$id, EstadoReserva::CONFIRMADA]);
        return $stmtReservas->fetch() !== false;
    }
}