<?php
require_once __DIR__ . '/vendor/autoload.php';

$openapi = (new \OpenApi\Generator())
    ->generate([
        __DIR__ . '/src/Shared/',
        __DIR__ . '/src/Auth/Controller',
        __DIR__ . '/src/Consultorio/Controller',
        __DIR__ . '/src/Nota/Controller',
        __DIR__ . '/src/Pacientes/Controller',
        __DIR__ . '/src/Profesionales/Controller',
        __DIR__ . '/src/Reservas/Controller',
        __DIR__ . '/src/Auth/DTOs',
        __DIR__ . '/src/Consultorio/DTOs',
        __DIR__ . '/src/Nota/DTOs',
        __DIR__ . '/src/Pacientes/DTOs',
        __DIR__ . '/src/Profesionales/DTOs',
        __DIR__ . '/src/Reservas/DTOs',
    ]);

file_put_contents(__DIR__ . '/public/openapi.json', $openapi->toJson());

echo "Documentacion generada en public/openapi.json\n";