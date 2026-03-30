<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$path = $input['path'] ?? null;
$content = $input['content'] ?? null;

if (!$path || $content === null) {
    echo json_encode(["success" => false, "error" => "Datos incompletos."]);
    exit;
}

// Validación de seguridad básica: solo permitir editar dentro de la ruta raíz permitida
if (strpos($path, '/home/juan/VirtualMachines/Docker/') !== 0) {
    echo json_encode(["success" => false, "error" => "Acceso denegado a la ruta especificada."]);
    exit;
}

if (file_put_contents($path, $content) !== false) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Error al escribir en el archivo. Verifica permisos de escritura."]);
}
