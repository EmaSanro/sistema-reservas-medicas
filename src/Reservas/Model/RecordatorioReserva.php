<?php
namespace App\Reservas\Model;

/**
 * Snapshot inmutable de una reserva lista para ser notificada,
 * con los datos de contacto del paciente y el nombre del profesional
 * ya resueltos por JOIN. Sale del repo, entra al service — nada más.
 */
final class RecordatorioReserva {
    public function __construct(
        public readonly int $id,
        public readonly string $fechaReserva,
        public readonly string $nombrePaciente,
        public readonly ?string $emailPaciente,
        public readonly ?string $telefonoPaciente,
        public readonly string $nombreProfesional,
    ) {}

    public static function fromDatabase(array $row): self {
        $email = isset($row['email']) && $row['email'] !== '' ? $row['email'] : null;
        $telefono = isset($row['telefono']) && $row['telefono'] !== '' ? $row['telefono'] : null;

        return new self(
            id: (int) $row['id'],
            fechaReserva: $row['fecha_reserva'],
            nombrePaciente: $row['paciente'],
            emailPaciente: $email,
            telefonoPaciente: $telefono,
            nombreProfesional: $row['profesional'],
        );
    }

    public function tieneContacto(): bool {
        return $this->emailPaciente !== null || $this->telefonoPaciente !== null;
    }
}
