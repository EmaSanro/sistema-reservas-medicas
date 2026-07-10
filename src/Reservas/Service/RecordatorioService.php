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
     * Antes del envío, purga ICS temporales viejos para que el directorio no crezca.
     */
    public function enviarPendientes(): void {
        $this->limpiarIcsAntiguos();

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
        $this->ensureTempIcsDir();

        // Nombre aleatorio (128 bits) → no se puede enumerar la carpeta
        // adivinando IDs de reserva. La relación reserva↔archivo se guarda en el log.
        $nombreArchivo = bin2hex(random_bytes(16)) . '.ics';
        $rutaFisica = $this->tempIcsDir() . "/{$nombreArchivo}";

        if (file_put_contents($rutaFisica, $contenido) === false) {
            throw new RuntimeException("No se pudo escribir el ICS temporal en {$rutaFisica}");
        }

        error_log("ICS temporal para reserva {$reservaId} publicado como {$nombreArchivo}");

        $base = $_ENV['ICS_PUBLIC_BASE_URL'] ?? 'https://sistema-reservas.loca.lt/public/temp_ics';
        return rtrim($base, '/') . "/{$nombreArchivo}";
    }

    private function tempIcsDir(): string {
        return __DIR__ . '/../../../public/temp_ics';
    }

    private function ensureTempIcsDir(): void {
        $dir = $this->tempIcsDir();
        // Doble is_dir para tolerar condiciones de carrera (otro proceso lo crea entre medio).
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException("No se pudo crear el directorio de ICS temporales: {$dir}");
        }
    }

    /**
     * Borra archivos ICS con mtime más viejo que $maxAgeHours horas.
     * No propaga fallos: un unlink que falla se logea y sigue.
     */
    private function limpiarIcsAntiguos(int $maxAgeHours = 24): void {
        $dir = $this->tempIcsDir();
        if (!is_dir($dir)) {
            return;
        }

        $limite = time() - ($maxAgeHours * 3600);

        foreach (glob($dir . '/*.ics') ?: [] as $archivo) {
            $mtime = @filemtime($archivo);
            if ($mtime === false || $mtime > $limite) {
                continue;
            }
            if (@unlink($archivo) === false) {
                error_log("No se pudo borrar ICS antiguo: {$archivo}");
            }
        }
    }
}
