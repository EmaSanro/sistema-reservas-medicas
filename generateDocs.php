<?php
require_once __DIR__ . '/vendor/autoload.php';

$openapi = (new \OpenApi\Generator())
    ->generate([
        __DIR__ . '/src/Controller',
        __DIR__ . '/src/Model/DTOs'
    ]);

file_put_contents(__DIR__ . '/public/openapi.json', $openapi->toJson());

echo "Documentacion generada en public/openapi.json\n";