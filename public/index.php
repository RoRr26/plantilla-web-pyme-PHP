<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$stmtSettings = $pdo->query('SELECT business_name, whatsapp, hero_title, hero_subtitle FROM site_settings ORDER BY id ASC LIMIT 1');
$settings = $stmtSettings->fetch() ?: [
    'business_name' => 'Mi Negocio',
    'whatsapp'      => '56912345678',
    'hero_title'    => 'Bienvenido',
    'hero_subtitle' => 'Contáctanos para cotizar.',
];

$stmtOfferings = $pdo->query('SELECT title, description, price FROM offerings WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$offerings = $stmtOfferings->fetchAll();

$cleanPhone = preg_replace('/\D+/', '', (string) $settings['whatsapp']);
$defaultMsg = rawurlencode('Hola, vi su página y me gustaría cotizar.');
$waUrl = "https://wa.me/{$cleanPhone}?text={$defaultMsg}";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e((string) $settings['business_name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
  <header class="bg-white border-b border-slate-200">
    <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
      <span class="font-bold text-lg text-slate-900"><?= e((string) $settings['business_name']) ?></span>
      <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer"
         class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        Cotizar por WhatsApp
      </a>
    </div>
  </header>

  <main class="flex-1 max-w-4xl mx-auto px-4 py-10 w-full">
    <section class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm mb-8">
      <h1 class="text-3xl font-bold text-slate-900 mb-3"><?= e((string) $settings['hero_title']) ?></h1>
      <p class="text-slate-600 text-base mb-6"><?= e((string) $settings['hero_subtitle']) ?></p>
      <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer"
         class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-3 rounded-xl transition">
        Hablar ahora por WhatsApp
      </a>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-4">Servicios y precios</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($offerings as $item): ?>
          <?php
            $itemMsg = rawurlencode("Hola, me interesa el servicio: {$item['title']} ({$item['price']}).");
            $itemWaUrl = "https://wa.me/{$cleanPhone}?text={$itemMsg}";
          ?>
          <article class="bg-white rounded-xl p-5 border border-slate-200 flex flex-col justify-between">
            <div>
              <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-semibold text-lg text-slate-900"><?= e((string) $item['title']) ?></h3>
                <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md text-sm shrink-0">
                  <?= e((string) $item['price']) ?>
                </span>
              </div>
              <p class="text-slate-600 text-sm mb-4"><?= e((string) $item['description']) ?></p>
            </div>
            <a href="<?= e($itemWaUrl) ?>" target="_blank" rel="noopener noreferrer"
               class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
              Consultar por este servicio →
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
    <?= e((string) $settings['business_name']) ?>
  </footer>
</body>
</html>
