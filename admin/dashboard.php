<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin_login();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$flashSuccess = '';
$flashError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $action = (string) ($_POST['action'] ?? '');

    if (!verify_csrf_token(is_string($token) ? $token : null)) {
        $flashError = 'Token de seguridad inválido. Recarga la página e intenta nuevamente.';
    } elseif ($action === 'update_settings') {
        $businessName = trim((string) ($_POST['business_name'] ?? ''));
        $whatsapp     = trim((string) ($_POST['whatsapp'] ?? ''));
        $heroTitle    = trim((string) ($_POST['hero_title'] ?? ''));
        $heroSubtitle = trim((string) ($_POST['hero_subtitle'] ?? ''));

        if ($businessName === '' || $whatsapp === '' || $heroTitle === '' || $heroSubtitle === '') {
            $flashError = 'Todos los campos generales son obligatorios.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE site_settings
                 SET business_name = :business_name,
                     whatsapp = :whatsapp,
                     hero_title = :hero_title,
                     hero_subtitle = :hero_subtitle
                 WHERE id = 1'
            );
            $stmt->execute([
                'business_name' => $businessName,
                'whatsapp'      => $whatsapp,
                'hero_title'    => $heroTitle,
                'hero_subtitle' => $heroSubtitle,
            ]);
            $flashSuccess = 'Datos del sitio actualizados correctamente.';
        }
    } elseif ($action === 'add_offering') {
        $title       = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price       = trim((string) ($_POST['price'] ?? ''));
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);

        if ($title === '' || $description === '' || $price === '') {
            $flashError = 'Completa título, descripción y precio para agregar el servicio.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO offerings (title, description, price, is_active, sort_order)
                 VALUES (:title, :description, :price, 1, :sort_order)'
            );
            $stmt->execute([
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'sort_order'  => $sortOrder,
            ]);
            $flashSuccess = 'Servicio agregado correctamente.';
        }
    } elseif ($action === 'update_offering') {
        $id          = (int) ($_POST['id'] ?? 0);
        $title       = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price       = trim((string) ($_POST['price'] ?? ''));
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || $title === '' || $description === '' || $price === '') {
            $flashError = 'Datos incompletos para actualizar el servicio.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE offerings
                 SET title = :title,
                     description = :description,
                     price = :price,
                     sort_order = :sort_order,
                     is_active = :is_active
                 WHERE id = :id'
            );
            $stmt->execute([
                'id'          => $id,
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'sort_order'  => $sortOrder,
                'is_active'   => $isActive,
            ]);
            $flashSuccess = 'Servicio actualizado.';
        }
    } elseif ($action === 'delete_offering') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM offerings WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $flashSuccess = 'Servicio eliminado.';
        }
    }
}

$settings = $pdo->query('SELECT * FROM site_settings WHERE id = 1 LIMIT 1')->fetch() ?: [
    'business_name' => '',
    'whatsapp'      => '',
    'hero_title'    => '',
    'hero_subtitle' => '',
];

$offerings = $pdo->query('SELECT * FROM offerings ORDER BY sort_order ASC, id ASC')->fetchAll();
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrador - <?= e((string) $settings['business_name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
  <header class="bg-slate-900 text-white">
    <div class="max-w-5xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="font-bold text-lg">Panel de Administración</h1>
        <p class="text-xs text-slate-300">Sesión: <?= e((string) ($_SESSION['admin_username'] ?? 'admin')) ?></p>
      </div>
      <div class="flex items-center gap-3">
        <a href="../public/index.php" target="_blank" rel="noopener noreferrer"
           class="bg-slate-700 hover:bg-slate-600 text-xs font-medium px-3 py-2 rounded-lg transition">
          Ver sitio público ↗
        </a>
        <a href="logout.php"
           class="bg-red-600 hover:bg-red-700 text-xs font-medium px-3 py-2 rounded-lg transition">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-5xl mx-auto px-4 py-8 space-y-8">
    <?php if ($flashSuccess !== ''): ?>
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <?php if ($flashError !== ''): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <?= e($flashError) ?>
      </div>
    <?php endif; ?>

    <!-- Sección 1: Datos generales -->
    <section class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900 mb-4">1. Información general del negocio</h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="update_settings">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del negocio</label>
            <input type="text" name="business_name" required
                   value="<?= e((string) $settings['business_name']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp (ej. 56912345678)</label>
            <input type="text" name="whatsapp" required
                   value="<?= e((string) $settings['whatsapp']) ?>"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Título principal (Portada)</label>
          <input type="text" name="hero_title" required
                 value="<?= e((string) $settings['hero_title']) ?>"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Subtítulo / Descripción corta</label>
          <textarea name="hero_subtitle" rows="2" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"><?= e((string) $settings['hero_subtitle']) ?></textarea>
        </div>

        <button type="submit"
                class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
          Guardar cambios generales
        </button>
      </form>
    </section>

    <!-- Sección 2: Agregar nuevo servicio -->
    <section class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900 mb-4">2. Agregar nuevo servicio o producto</h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_offering">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del servicio</label>
            <input type="text" name="title" required placeholder="Ej. Cambio de pantalla"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
            <input type="text" name="price" required placeholder="Ej. $25.000 o Desde $15.000"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
            <input type="text" name="description" required placeholder="Breve detalle de lo que incluye"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Orden</label>
            <input type="number" name="sort_order" value="0"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
        </div>

        <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
          + Agregar servicio
        </button>
      </form>
    </section>

    <!-- Sección 3: Editar servicios existentes -->
    <section class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900 mb-4">3. Servicios actuales (editar o eliminar)</h2>

      <?php if (count($offerings) === 0): ?>
        <p class="text-sm text-slate-500">Aún no hay servicios registrados.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($offerings as $item): ?>
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
              <form method="POST" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="update_offering">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                  <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Título</label>
                    <input type="text" name="title" required value="<?= e((string) $item['title']) ?>"
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Precio</label>
                    <input type="text" name="price" required value="<?= e((string) $item['price']) ?>"
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Orden</label>
                    <input type="number" name="sort_order" value="<?= (int) $item['sort_order'] ?>"
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                  <input type="text" name="description" required value="<?= e((string) $item['description']) ?>"
                         class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                  <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" <?= ((int) $item['is_active'] === 1) ? 'checked' : '' ?>>
                    Visible en la página pública
                  </label>

                  <div class="flex items-center gap-2">
                    <button type="submit"
                            class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                      Guardar
                    </button>
                  </div>
                </div>
              </form>

              <form method="POST" class="mt-2 text-right" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="delete_offering">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">
                  Eliminar servicio
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
