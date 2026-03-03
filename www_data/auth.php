<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        // Si es una petición AJAX, devolvemos 401
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }
        // Si es una navegación normal, redirigimos al login
        header("Location: login.php");
        exit;
    }
}
