<?php
namespace App\Nota\Validators;
use App\Nota\Exceptions\SubidaArchivoException;
use App\Nota\Exceptions\TamanioArchivoException;
use App\Nota\Exceptions\TipoArchivoInvalidoException;
use App\Nota\Model\TiposArchivosPermitidos;

class ArchivoNotaValidator {
    
    public static function validarArchivo(array $archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new SubidaArchivoException("Error al subir archivo");
        }
        
        // Validar tipo
        if (!in_array($archivo['type'], TiposArchivosPermitidos::obtenerTodos())) {
            throw new TipoArchivoInvalidoException("Solo se permiten archivos PDF, JPG, JPEG y PNG");
        }
        
        // Validar tamaño
        $tamanioMaximo = 5 * 1024 * 1024; // 5MB
        if ($archivo['size'] > $tamanioMaximo) {
            throw new TamanioArchivoException("El archivo no puede superar 5MB");
        }
        
        // Validación adicional de extensión (seguridad)
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        if(!in_array($extension, $extensionesPermitidas)) {
            throw new TipoArchivoInvalidoException("Extensión de archivo no permitida");
        }
    }
}