<?php
namespace App\Reservas\Service;

use App\Helper\GeneradorIcs;
use App\Reservas\Model\RecordatorioReserva;
use App\Reservas\Repository\ReservasRepository;
use RuntimeException;
use Throwable;

/**
 * Responsable exclusivo del envío de recordatorios de reservas.
 * No conoce nada del ciclo de vida de una reserva (crear, cancelar, actualizar);
 * solo lee las que estan pendientes de notificar y las despacha por email o WhatsApp.
 */
final class RecordatorioService {

    public function __construct(
        private readonly ReservasRepository $reservasRepository,
        private readonly MailService $mailService,
        private readonly WhatsappService $whatsappService,
        private readonly GeneradorIcs $ics,
    ) {}

    /**
     * Recorre las reservas pendientes y las notifica. Aísla los fallos por reserva:
     * un error en una notificación no interrumpe el resto del batch.
     */
    public function enviarPendientes(): void {
        $recordatorios = $this->reservasRepository->recordatoriosPendientes();
        if (empty($recordatorios)) {
            return;
        }

        foreach ($recordatorios as $recordatorio) {
            try {
                $enviado = $this->enviar($recordatorio);
                if ($enviado) {
                    $this->reservasRepository->marcarComoNotificado($recordatorio->id);
                    error_log("Recordatorio enviado para reserva {$recordatorio->id}");
                } else {
                    error_log("Fallo el envio del recordatorio para reserva {$recordatorio->id}");
                }
            } catch (Throwable $e) {
                error_log("Error notificando reserva {$recordatorio->id}: {$e->getMessage()}");
            }
        }
    }

    private function enviar(RecordatorioReserva $recordatorio): bool {
        if (!$recordatorio->tieneContacto()) {
            throw new RuntimeException("Reserva {$recordatorio->id} sin datos de contacto");
        }

        $contenidoIcs = $this->ics->generarIcs(
            $recordatorio->fechaReserva,
            date('Y-m-d H:i:s', strtotime($recordatorio->fechaReserva . ' +30 minutes')),
            "Reserva con {$recordatorio->nombreProfesional}",
            "Recuerda llegar 10 minutos antes. No olvidar estudios previos si fueron solicitados."
        );

        if ($recordatorio->emailPaciente !== null) {
            return $this->mailService->enviarRecordatorio(
                $recordatorio->emailPaciente,
                $recordatorio->fechaReserva,
                $contenidoIcs
            );
        }

        $urlPublica = $this->publicarIcsTemporal($recordatorio->id, $contenidoIcs);
        return $this->whatsappService->enviarRecordatorio(
            $recordatorio->telefonoPaciente,
            $recordatorio->nombrePaciente,
            date('d/m/Y', strtotime($recordatorio->fechaReserva)),
            date('H:i', strtotime($recordatorio->fechaReserva)),
            $urlPublica
        );
    }

    private function publicarIcsTemporal(int $reservaId, string $contenido): string {
        $nombreArchivo = "cita_{$reservaId}.ics";
        $rutaFisica = __DIR__ . "/../../../public/temp_ics/{$nombreArchivo}";

        if (file_put_contents($rutaFisica, $contenido) === false) {
            throw new RuntimeException("No se pudo escribir el ICS temporal en {$rutaFisica}");
        }

        $base = $_ENV['ICS_PUBLIC_BASE_URL'] ?? 'https://sistema-reservas.loca.lt/public/temp_ics';
        return rtrim($base, '/') . "/{$nombreArchivo}";
    }
}
