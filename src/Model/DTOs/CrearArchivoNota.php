<?php
namespace App\Model\DTOs;

class CrearArchivoNota {
    private int $id;
    private string $nombre_original;
    private string $nombre_sistema;
    private string $ruta;
    private string $tipo_archivo;
    private int $peso;
    private string $fecha_subida;
    private int $nota_id;

    public function __construct(int $id, string $nombre_original, string $nombre_sistema, string $ruta, string $tipo_archivo, int $peso, string $fecha_subida, int $nota_id) {
        $this->id = $id;
        $this->nombre_original = $nombre_original;
        $this->nombre_sistema = $nombre_sistema;
        $this->ruta = $ruta;
        $this->tipo_archivo = $tipo_archivo;
        $this->peso = $peso;
        $this->fecha_subida = $fecha_subida;
        $this->nota_id = $nota_id;
    }
    
    public function getId(): int {
        return $this->id;
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

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['nombre_original'],
            $data['nombre_sistema'],
            $data['ruta'],
            $data['tipo_archivo'],
            $data['peso'],
            $data['fecha_subida'],
            $data['nota_id']
        );
    }
}