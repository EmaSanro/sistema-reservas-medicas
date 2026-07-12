<?php
namespace App\Nota\DTOs\Response;

use OpenApi\Attributes as OA;

/**
 * Respuesta para las operaciones de crear/actualizar una nota que aceptan
 * subida de archivos. Distingue explícitamente lo que se guardó de lo que
 * falló, para que el cliente pueda mostrar el estado real por archivo
 * en lugar de asumir "todo fue bien" cuando alguno no se pudo procesar.
 */
#[OA\Schema(schema: "ResultadoMutacionNota")]
final class ResultadoMutacionNota {
    /**
     * @param list<AdjuntoFallidoInfo> $adjuntosFallidos
     */
    public function __construct(
        #[OA\Property(ref: "#/components/schemas/RespuestaNota")]
        public readonly RespuestaNota $nota,
        #[OA\Property(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/AdjuntoFallidoInfo")
        )]
        public readonly array $adjuntosFallidos = [],
    ) {}
}
