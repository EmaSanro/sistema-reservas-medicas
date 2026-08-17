<?php
namespace App\Nota\DTOs\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaArchivoNota")]
class RespuestaArchivoNota {
    #[OA\Property(example: 7)]
    public readonly int $id;
    #[OA\Property(example: "estudio.pdf")]
    public readonly string $nombre_original;
    #[OA\Property(example: "application/pdf")]
    public readonly string $tipo_archivo;
    #[OA\Property(description: "Peso del archivo en bytes", example: 204800)]
    public readonly int $peso;
    #[OA\Property(example: "2026-06-15 14:32:34")]
    public readonly string $fecha_subida;
    #[OA\Property(example: 1)]
    public readonly int $nota_id;

    public function __construct(int $id, string $nombre_original, string $tipo_archivo, int $peso, string $fecha_subida, int $nota_id) {
        $this->id = $id;
        $this->nombre_original = $nombre_original;
        $this->tipo_archivo = $tipo_archivo;
        $this->peso = $peso;
        $this->fecha_subida = $fecha_subida;
        $this->nota_id = $nota_id;
    }
}
