<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Obtenemos credenciales desde variables de entorno
    $validUser = getenv('DASHBOARD_USER') ?: 'admin';
    $validPass = getenv('DASHBOARD_PASS') ?: 'admin123';

    if ($user === $validUser && $pass === $validPass) {
        $_SESSION['authenticated'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales inválidas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Simple Docker Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230dcaf0'><path d='M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 6.404l6.154-2.462zm3.25 3.63-6.5 2.6v7.922l6.5-2.6V6.17zm-7.5 10.522V8.77L.5 6.17v7.922zM7.5.582a1 1 0 0 1 .736 0l7.67 3.068A.5.5 0 0 1 16 4.115v8.93a1 1 0 0 1-.607.922l-7 2.8a1 1 0 0 1-.786 0l-7-2.8A1 1 0 0 1 0 13.045V4.115a.5.5 0 0 1 .308-.465z'/></svg>">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background-color: #1e293b; border: 1px solid #334155; width: 100%; max-width: 400px; border-radius: 1rem; }
        .btn-info { background-color: #0ea5e9; border: none; font-weight: bold; color: white; }
        .btn-info:hover { background-color: #0284c7; color: white; }
    </style>
</head>
<body>
    <div class="card shadow-lg p-4">
        <div class="text-center mb-4">
            <h2 class="text-info mt-2">Simple Docker Dashboard</h2>
            <p class="text-secondary">Inicia sesión para continuar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="username" class="form-control bg-dark border-secondary" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control bg-dark border-secondary" required>
            </div>
            <button type="submit" class="btn btn-info w-100 py-2">Ingresar</button>
        </form>
    </div>
</body>
</html>
