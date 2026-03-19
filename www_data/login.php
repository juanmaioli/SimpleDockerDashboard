<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Intentar obtener desde entorno, si no, usar valores conocidos del .env o default
    $envUser = getenv('DASHBOARD_USER');
    $envPass = getenv('DASHBOARD_PASS');
    
    $validUser = $envUser ?: 'juan';
    $validPass = $envPass ?: 'Lasflores506';

    if ($user === $validUser && $pass === $validPass) {
        $_SESSION['authenticated'] = true;
        session_write_close(); // Aseguramos que se guarde antes de redirigir
        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Simple Docker Dashboard</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background-color: #1e293b; border: 1px solid #334155; width: 100%; max-width: 400px; border-radius: 1rem; }
        .btn-info { background-color: #0ea5e9; border: none; font-weight: bold; color: white; }
    </style>
</head>
<body>
    <div class="card shadow-lg p-4">
        <div class="text-center mb-4">
            <h2 class="text-info mt-2">Simple Docker Dashboard</h2>
            <p class="text-secondary">Inicia sesión</p>
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
