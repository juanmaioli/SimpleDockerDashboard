<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

// Ejecutamos docker stats en formato JSON (una sola vez)
// Usamos comillas simples para el comando para evitar conflictos con las comillas dobles del formato JSON
$command = 'docker stats --no-stream --format "{{json .}}"';
$output = shell_exec($command);

if ($output === null) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo ejecutar el comando Docker. Verifica los permisos del usuario www-data."]);
    exit;
}

$output = trim($output);
if (empty($output)) {
    echo json_encode([]);
    exit;
}

// Convertimos la salida multilínea en un array de objetos
$lines = explode("\n", $output);
$stats = [];

foreach ($lines as $line) {
    if (empty($line)) continue;
    $decoded = json_decode($line);
    if ($decoded) {
        $stats[] = $decoded;
    }
}

echo json_encode($stats);
