<?php
namespace App\Model;

use App\Model\DTOs\RespuestaArchivoNotaDTO;
use App\Shared\Entity;

class ArchivoNota extends Entity
{
    private string $nombre_original;
    private string $nombre_sistema;
    private string $ruta;
    private string $tipo_archivo;
    private int $peso;
    private string $fecha_subida;
    private int $nota_id;

    private function __construct()
    {

    }

    public static function create(string $nombre_original, string $nombre_sistema, string $ruta, string $tipo_archivo, int $peso, string $fecha_subida, int $nota_id): self
    {
        $archivo = new self();
        $archivo->setNombreOriginal($nombre_original);
        $archivo->setNombreSistema($nombre_sistema);
        $archivo->setRuta($ruta);
        $archivo->setTipoArchivo($tipo_archivo);
        $archivo->setPeso($peso);
        $archivo->setFechaSubida($fecha_subida);
        $archivo->setNotaId($nota_id);

        return $archivo;
    }

    public function setNombreOriginal(string $nombre_original): void
    {
        $this->maxLength($nombre_original, 255, 'nombre_original');
        $this->nombre_original = $nombre_original;
    }

    public function setNombreSistema(string $nombre_sistema): void
    {
        $this->maxLength($nombre_sistema, 255, 'nombre_sistema');
        $this->nombre_sistema = $nombre_sistema;
    }

    public function setRuta(string $ruta): void
    {
        $this->maxLength($ruta, 500, 'ruta');
        $this->ruta = $ruta;
    }

    public function setTipoArchivo(string $tipo_archivo): void
    {
        $this->maxLength($tipo_archivo, 50, 'tipo_archivo');
        $this->tipo_archivo = $tipo_archivo;
    }

    public function setPeso(int $peso): void
    {
        $this->peso = $peso;
    }

    public function setFechaSubida(string $fecha_subida): void
    {
        $this->fecha_subida = $fecha_subida;
    }

    public function setNotaId(int $nota_id): void
    {
        $this->nota_id = $nota_id;
    }

    public function getNombreOriginal(): string
    {
        return $this->nombre_original;
    }

    public function getNombreSistema(): string
    {
        return $this->nombre_sistema;
    }

    public function getRuta(): string
    {
        return $this->ruta;
    }

    public function getTipoArchivo(): string
    {
        return $this->tipo_archivo;
    }

    public function getPeso(): int
    {
        return $this->peso;
    }

    public function getFechaSubida(): string
    {
        return $this->fecha_subida;
    }

    public function getNotaId(): int
    {
        return $this->nota_id;
    }

    public static function fromDatabase(array $data): self
    {
        $archivo = new self();
        $archivo->id = (int) $data["id"];
        $archivo->nombre_original = $data["nombre_original"];
        $archivo->nombre_sistema = $data["nombre_sistema"];
        $archivo->ruta = $data["ruta"];
        $archivo->tipo_archivo = $data["tipo_archivo"];
        $archivo->peso = (int) $data["peso"];
        $archivo->fecha_subida = $data["fecha_subida"];
        $archivo->nota_id = (int) $data["nota_id"];

        return $archivo;
    }
}
