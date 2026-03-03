<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

// Ejecutamos docker ps -a en formato JSON
$command = 'docker ps -a --format "{{json .}}"';
$output = shell_exec($command);

if ($output === null) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo ejecutar docker ps. Verifica permisos."]);
    exit;
}

$output = trim($output);
if (empty($output)) {
    echo json_encode([]);
    exit;
}

$lines = explode("
", $output);
$containers = [];

foreach ($lines as $line) {
    if (empty($line)) continue;
    $decoded = json_decode($line);
    if ($decoded) {
        $containers[] = $decoded;
    }
}

echo json_encode($containers);
