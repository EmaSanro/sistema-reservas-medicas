<?php
namespace App\Model;

class TiposArchivosPermitidos {
    public const PDF = "application/pdf";
    public const JPG = "image/jpg";
    public const JPEG = "image/jpeg";
    public const PNG = "image/png";

    public static function obtenerTodos(): array {
        return [
            self::PNG,
            self::JPG,
            self::JPEG,
            self::PDF
        ];
    }
}