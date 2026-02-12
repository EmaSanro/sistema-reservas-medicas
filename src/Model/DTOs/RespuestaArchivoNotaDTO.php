<?php
namespace App\Model\DTOs;
class RespuestaArchivoNotaDTO {
    public readonly int $id;
    public readonly string $nombre_original;
    public readonly string $tipo_archivo;
    public readonly int $peso;
    public readonly string $fecha_subida;
    public readonly int $nota_id;

    public function __construct(int $id, string $nombre_original, int $peso, string $fecha_subida, int $nota_id) {
        $this->id = $id;
        $this->nombre_original = $nombre_original;
        $this->peso = $peso;
        $this->fecha_subida = $fecha_subida;
        $this->nota_id = $nota_id;
    }
}
