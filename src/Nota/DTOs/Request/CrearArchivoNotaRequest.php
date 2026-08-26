<?php
namespace App\Nota\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CrearArchivoNotaRequest",
    required: ["nombre_original", "nombre_sistema", "ruta", "tipo_archivo", "tamanio", "fecha_subida", "nota_id"]
)]
class CrearArchivoNotaRequest {
    #[OA\Property(example: "nombre_original.jpg")]
    private string $nombre_original;
    #[OA\Property(example: "nombre_sistema_12345.jpg")]
    private string $nombre_sistema;
    #[OA\Property(example: "/ruta/completa/nombre_sistema_12345.jpg")]
    private string $ruta;
    #[OA\Property(example: "image/jpeg")]
    private string $tipo_archivo;
    #[OA\Property(example: 204800)]
    private int $peso;
    #[OA\Property(example: "2024-06-01 15:00:00")]
    private string $fecha_subida;
    #[OA\Property(example: 1)]
    private int $nota_id;

    public function __construct(string $nombre_original, string $nombre_sistema, string $ruta, string $tipo_archivo, int $peso, string $fecha_subida, int $nota_id) {
        $this->nombre_original = $nombre_original;
        $this->nombre_sistema = $nombre_sistema;
        $this->ruta = $ruta;
        $this->tipo_archivo = $tipo_archivo;
        $this->peso = $peso;
        $this->fecha_subida = $fecha_subida;
        $this->nota_id = $nota_id;
    }

    public function getNombreOriginal(): string {
        return $this->nombre_original;
    }

    public function getNombreSistema(): string {
        return $this->nombre_sistema;
    }

    public function getRuta(): string {
        return $this->ruta;
    }

    public function getTipoArchivo(): string {
        return $this->tipo_archivo;
    }

    public function getPeso(): int {
        return $this->peso;
    }

    public function getFechaSubida(): string {
        return $this->fecha_subida;
    }

    public function getNotaId(): int {
        return $this->nota_id;
    }
}