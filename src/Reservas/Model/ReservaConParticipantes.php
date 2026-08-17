<?php
namespace App\Reservas\Model;

/**
 * Snapshot inmutable de una reserva con sus dos participantes ya resueltos
 * por JOIN. Read model: sale del repo, entra al mapper — no valida, no se
 * persiste. Para escribir se usa la entidad Reserva.
 */
final class ReservaConParticipantes {
    public function __construct(
        public readonly int $id,
        public readonly string $fechaReserva,
        public readonly string $estado,
        public readonly int $idPaciente,
        public readonly string $nombrePaciente,
        public readonly string $apellidoPaciente,
        public readonly int $idProfesional,
        public readonly string $nombreProfesional,
        public readonly string $apellidoProfesional,
        public readonly string $profesion,
    ) {}

    /**
     * Columnas de la proyeccion, con los alias que consume fromDatabase().
     * Mantener ambas en sincronia: si se agrega un campo hay que tocarlas juntas.
     *
     * Asume los alias de tabla que define ReservasRepository: r (reservas),
     * pac y prof (usuario) y p (profesional).
     */
    public static function columnasSelect(): string {
        return implode(', ', [
            'r.id',
            'r.fecha_reserva',
            'r.estado',
            'r.idpaciente AS paciente_id',
            'pac.nombre AS paciente_nombre',
            'pac.apellido AS paciente_apellido',
            'r.idprofesional AS profesional_id',
            'prof.nombre AS profesional_nombre',
            'prof.apellido AS profesional_apellido',
            'p.profesion AS profesional_profesion',
        ]);
    }

    public static function fromDatabase(array $row): self {
        return new self(
            id: (int) $row['id'],
            fechaReserva: $row['fecha_reserva'],
            estado: $row['estado'],
            idPaciente: (int) $row['paciente_id'],
            nombrePaciente: $row['paciente_nombre'],
            apellidoPaciente: $row['paciente_apellido'],
            idProfesional: (int) $row['profesional_id'],
            nombreProfesional: $row['profesional_nombre'],
            apellidoProfesional: $row['profesional_apellido'],
            profesion: $row['profesional_profesion'],
        );
    }
}
