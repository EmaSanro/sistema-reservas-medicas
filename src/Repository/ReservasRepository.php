<?php
namespace App\Repository;

use App\Model\Roles;
use AppConfig\Database;
use DateTime;

class ReservasRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTodas() {
        $reservas = $this->db->prepare("
            SELECT r.id, r.fecha_reserva, CONCAT(upa.nombre, ' ', upa.apellido) as paciente, CONCAT(upr.nombre, ' ', upr.apellido) as profesional 
            FROM reservas r 
            JOIN usuario upa ON upa.id = r.idpaciente 
            JOIN usuario upr ON upr.id = r.idprofesional;
        ");
        $reservas->execute();
        return $reservas->fetchAll();
    }

    public function obtenerReservasPorUsuarioId(int $id, string $rol) {
        $columna = ($rol == "Paciente") ? "idpaciente" : "idprofesional";
        $reservas = $this->db->prepare("
            SELECT r.id, r.fecha_reserva, CONCAT(upa.nombre, ' ', upa.apellido) as paciente, CONCAT(upr.nombre, ' ', upr.apellido) as profesional 
            FROM reservas r 
            JOIN usuario upa ON upa.id = r.idpaciente 
            JOIN usuario upr ON upr.id = r.idprofesional 
            WHERE $columna = ?
        ");
        $reservas->execute([$id]);
        return $reservas->fetchAll();
    }

    public function obtenerReservaEspecifica($idPaciente, $idProfesional, $fecha) {
        $reserva = $this->db->prepare("
            SELECT r.id, r.fecha_reserva, CONCAT(upa.nombre, ' ', upa.apellido) as paciente, CONCAT(upr.nombre, ' ', upr.apellido) as profesional 
            FROM reservas r 
            JOIN usuario upa ON upa.id = r.idpaciente 
            JOIN usuario upr ON upr.id = r.idprofesional
            WHERE idpaciente = ? AND idprofesional = ? and fecha_reserva = ?
        ");
        $reserva->execute([$idPaciente, $idProfesional, $fecha]);
        return $reserva->fetch();
    }

    public function reservar(int $idProfesional, int $idPaciente, string $date) {
        $reservar = $this->db->prepare("
            INSERT INTO reservas(idprofesional, idpaciente, fecha_reserva) VALUES(?,?,?)
        ");
        $reservar->execute([$idProfesional, $idPaciente, $date]);
        return $reservar->rowCount() > 0;
    }

    public function buscarCoincidencia($idPaciente, $idProfesional, $fecha) {
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

    public function perteneceAlPaciente($id, $idPaciente) {
        $reserva = $this->db->prepare("
            SELECT 1 FROM reserva WHERE id = ? AND idPaciente = ?
        ");
        $reserva->execute([$id, $idPaciente]);
        return $reserva->fetch();
    }

    public function cancelarReserva($id) {
        $reserva = $this->db->prepare("
            DELETE FROM reservas WHERE id = ?
        ");
        $reserva->execute([$id]);
        return $reserva->rowCount() > 0;
    }
}