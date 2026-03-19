<?php
require_once 'auth.php';
checkAuth();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $max_size = ini_get('post_max_size');
    echo json_encode(["error" => "El archivo es demasiado grande para el servidor (Límite PHP: $max_size). Asegúrate de haber reiniciado el contenedor después de los cambios en php.ini."]);
    exit;
}

if (!isset($_FILES['image_tar'])) {
    http_response_code(400);
    echo json_encode(["error" => "No se recibió ningún archivo (FILES vacío)."]);
    exit;
}

$file = $_FILES['image_tar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "Error en la subida: " . $file['error']]);
    exit;
}

// Ruta temporal para procesar
$tmpPath = $file['tmp_name'];

// Ejecutamos docker load usando el archivo temporal
$command = "docker load < " . escapeshellarg($tmpPath) . " 2>&1";
$output = [];
$return_var = 0;
exec($command, $output, $return_var);

$outputStr = implode("\n", $output);

if ($return_var !== 0) {
    echo json_encode(["success" => false, "error" => $outputStr ?: "Error al importar la imagen (Código: $return_var)"]);
} else {
    echo json_encode(["success" => true, "output" => trim($outputStr)]);
}
