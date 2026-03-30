<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(["error" => "ID no proporcionado."]);
    exit;
}

// Obtenemos la ruta del archivo compose desde los labels del contenedor
$inspect = shell_exec(sprintf('docker inspect %s --format "{{index .Config.Labels \"com.docker.compose.project.config_files\"}}"', escapeshellarg($id)));
$path = trim($inspect);

if (!$path) {
    echo json_encode(["error" => "Este contenedor no parece haber sido levantado con Docker Compose."]);
    exit;
}

if (!file_exists($path)) {
    echo json_encode(["error" => "Archivo no encontrado en la ruta: $path. Verifica los permisos de volumen."]);
    exit;
}

$content = file_get_contents($path);
echo json_encode([
    "path" => $path,
    "content" => $content
]);
