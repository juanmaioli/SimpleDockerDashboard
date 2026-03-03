<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

// Recibimos los datos de la petición POST
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$action = $input['action'] ?? null;

// Validamos parámetros
if (!$id || !in_array($action, ['start', 'stop', 'logs', 'rm', 'rmi', 'inspect'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit;
}

// Construimos el comando según la acción
switch ($action) {
    case 'inspect':
        $command = sprintf('docker inspect %s 2>&1', escapeshellarg($id));
        break;
    case 'logs':
        $command = sprintf('docker logs --tail 100 %s 2>&1', escapeshellarg($id));
        break;
    case 'rm':
        $command = sprintf('docker rm -f %s 2>&1', escapeshellarg($id));
        break;
    case 'rmi':
        $command = sprintf('docker rmi -f %s 2>&1', escapeshellarg($id));
        break;
    default:
        $command = sprintf('docker %s %s 2>&1', escapeshellarg($action), escapeshellarg($id));
}

$output = shell_exec($command);

if ($output === null && !in_array($action, ['rm', 'rmi'])) {
    http_response_code(500);
    echo json_encode(["error" => "Error al ejecutar el comando Docker."]);
    exit;
}

echo json_encode([
    "success" => true, 
    "output" => $output ? trim($output) : "Operación completada sin salida."
]);
