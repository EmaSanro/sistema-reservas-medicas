<?php
// Vista de Swagger UI. La ruta GET /api/docs le inyecta $openapiUrl;
// el valor por defecto cubre el caso de acceso directo al archivo.
$openapiUrl ??= '/api/openapi';
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: <?= json_encode($openapiUrl, JSON_UNESCAPED_SLASHES) ?>,
            dom_id: '#swagger-ui',
            presets: [SwaggerUIBundle.presets.apis],
            layout: "BaseLayout"
        });
    </script>
</body>
</html>