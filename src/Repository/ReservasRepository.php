<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Exceptions\Reservas\ReservaNotFoundException;
use App\Model\Reserva;
use App\Model\Roles;
use AppConfig\Database;
use DateTime;
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
                $reserva["fecha_reserva"]
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
                $reserva["fecha_reserva"]
            );
        }
        return $reservas;
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
                INSERT INTO reservas(idprofesional, idpaciente, fecha_reserva) VALUES(?,?,?)
            ");
            $reservaCreated = $reservar->execute([$dto->getIdProfesional(), $idPaciente, $dto->getFecha()]);
            $this->db->commit();
            if(!$reservaCreated) {
                throw new DatabaseException("Error al crear la reserva");
            }
            return new Reserva(
                $this->db->lastInsertId(),
                $idPaciente,
                $dto->getIdProfesional(),
                $dto->getFecha()
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

    public function cancelarReserva($id): bool {
        $reserva = $this->db->prepare("
            DELETE FROM reservas WHERE id = ?
        ");
        $reserva->execute([$id]);
        if($reserva->rowCount() === 0) {
            throw new ReservaNotFoundException("No se pudo encontrar una reserva con ese id");
        }
        return $reserva->rowCount() > 0;
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
}