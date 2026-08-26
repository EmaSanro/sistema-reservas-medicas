<?php
namespace App\Nota\DTOs\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "AdjuntoFallidoInfo")]
final class AdjuntoFallidoInfo {
    public function __construct(
        #[OA\Property(example: "estudio.pdf")]
        public readonly string $nombre,
        #[OA\Property(example: "El archivo no puede superar 5MB")]
        public readonly string $motivo,
    ) {}
}
