<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_csrf_token(is_string($token) ? $token : null)) {
        $error = 'Solicitud no válida. Recarga la página e inténtalo de nuevo.';
    } elseif ($username === '' || $password === '') {
        $error = 'Ingresa tu usuario y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, (string) $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = (string) $user['username'];
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión - Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
  <form method="POST" class="bg-white w-full max-w-sm p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
    <h1 class="text-xl font-bold text-slate-900">Acceso al panel</h1>

    <?php if ($error !== ''): ?>
      <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg border border-red-200">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
      <input type="text" name="username" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
      <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
    </div>

    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded-lg text-sm transition">
      Entrar
    </button>
  </form>
</body>
</html>
