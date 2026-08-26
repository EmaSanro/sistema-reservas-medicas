<?php
namespace App\Nota\DTOs\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaNota")]
class RespuestaNota {
    #[OA\Property(example: 5)]
    public readonly int $id;
    #[OA\Property(example: "Consulta general")]
    public readonly string $motivo_visita;
    #[OA\Property(example: "El paciente refiere dolor de cabeza desde hace 3 días...")]
    public readonly string $texto_nota;
    #[OA\Property(example: 123)]
    public readonly int $reserva_id;
    /** @var list<RespuestaArchivoNota> */
    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: "#/components/schemas/RespuestaArchivoNota")
    )]
    public readonly array $adjuntos;

    public function __construct(int $id, string $motivo_visita, string $texto_nota, int $reserva_id, array $adjuntos = []) {
        $this->id = $id;
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
        $this->reserva_id = $reserva_id;
        $this->adjuntos = $adjuntos;
    }
}
