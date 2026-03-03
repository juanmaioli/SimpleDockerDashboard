<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

// Ejecutamos docker images en formato JSON
$command = 'docker images --format "{{json .}}"';
$output = shell_exec($command);

if ($output === null) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo ejecutar docker images. Verifica permisos."]);
    exit;
}

$output = trim($output);
if (empty($output)) {
    echo json_encode([]);
    exit;
}

$lines = explode("
", $output);
$images = [];

foreach ($lines as $line) {
    if (empty($line)) continue;
    $decoded = json_decode($line);
    if ($decoded) {
        $images[] = $decoded;
    }
}

echo json_encode($images);
