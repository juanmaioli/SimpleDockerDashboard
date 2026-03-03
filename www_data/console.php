<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$command = $input['command'] ?? null;
$workdir = $input['workdir'] ?? '/'; // Directorio actual (estado)

if (!$id || !$command) {
    echo json_encode(["error" => "Faltan parámetros"]);
    exit;
}

// Limpieza básica del comando
$command = trim($command);

// Detectamos si es un intento de cambio de directorio
if (preg_match('/^cd\s+(.+)$/', $command, $matches)) {
    $targetDir = $matches[1];
    // Intentamos cambiar de directorio y obtener la ruta absoluta resultante
    // Usamos 'sh -c' para que el shell resuelva la ruta (ej: cd ..)
    $execCmd = sprintf(
        'docker exec -w %s %s sh -c "cd %s && pwd"', 
        escapeshellarg($workdir), 
        escapeshellarg($id), 
        escapeshellarg($targetDir)
    );
    
    $output = shell_exec($execCmd . ' 2>&1');
    $lines = explode("
", trim($output));
    // Si la última línea parece una ruta, asumimos éxito
    $lastLine = end($lines);
    
    if (strpos($lastLine, '/') === 0) {
        echo json_encode(["output" => "", "newWorkdir" => $lastLine]);
    } else {
        echo json_encode(["output" => $output]); // Mostrar error (ej: no such file)
    }
    exit;
}

// Ejecución normal de comandos
$execCmd = sprintf(
    'docker exec -w %s %s sh -c %s 2>&1',
    escapeshellarg($workdir),
    escapeshellarg($id),
    escapeshellarg($command)
);

$output = shell_exec($execCmd);
echo json_encode(["output" => $output]);
