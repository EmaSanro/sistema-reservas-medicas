<?php
namespace App\Nota\Exceptions;

use App\Shared\Exceptions\BusinessValidationException;

class TipoArchivoInvalidoException extends BusinessValidationException {
    public function __construct(string $tipoArchivo)
    {
        parent::__construct("Tipo de archivo '$tipoArchivo' no permitido");
    }
}