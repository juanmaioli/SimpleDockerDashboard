<?php
require_once 'auth.php';
checkAuth();

$id = $_GET['id'] ?? null;
$repo = $_GET['repo'] ?? 'imagen';
$tag = $_GET['tag'] ?? 'latest';

if (!$id) {
    die("ID de imagen no proporcionado.");
}

// Limpiamos el nombre del archivo para la descarga
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $repo . '_' . $tag);
$filename = $safeName . ".tar";

// Configuramos headers para la descarga de un archivo .tar
header('Content-Type: application/x-tar');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Ejecutamos docker save y enviamos la salida directamente al buffer
// Usamos escapeshellarg por seguridad
$command = 'docker save ' . escapeshellarg($id);

// Desactivamos el límite de tiempo de ejecución para imágenes grandes
set_time_limit(0);

// Ejecutamos el comando y volcamos la salida al navegador
passthru($command);
exit;
