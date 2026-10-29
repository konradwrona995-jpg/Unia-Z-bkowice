<?php
// Wspólny nagłówek/stopka panelu. Użycie: panel_start('Tytuł', 'aktywna'); ... panel_koniec();

function panel_start(string $tytul, string $aktywna = ''): void
{
    global $KLUB;
    $u = zalogowany();
    $f = flash(); ?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($tytul) ?> – Panel Unii</title>
<link rel="icon" href="../assets/img/unia-herb.svg">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/../admin.css') ?>">
</head>
<body class="<?= $u ? '' : 'guest' ?>">
<?php if ($u): ?>
<header class="bar">
  <a class="bar__brand" href="index.php"><img src="../assets/img/unia-herb.svg" alt=""> Panel klubu</a>
  <nav>
    <a href="aktualnosci.php"<?= $aktywna === 'news' ? ' class="on"' : '' ?>>Aktualności</a>
    <a href="kadra.php"<?= $aktywna === 'kadra' ? ' class="on"' : '' ?>>Kadra</a>
    <a href="konto.php"<?= $aktywna === 'konto' ? ' class="on"' : '' ?>>Konto</a>
  </nav>
  <span class="bar__user">
    <a href="../index.php" target="_blank">Zobacz stronę ↗</a>
    <form method="post" action="logout.php"><?= csrf_pole() ?><button>Wyloguj (<?= e($u['login']) ?>)</button></form>
  </span>
</header>
<?php endif; ?>
<main class="page">
  <?php if ($f): ?><div class="flash flash--<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
<?php }

function panel_koniec(): void
{ ?>
</main>
<script>
// Podgląd wybranego zdjęcia przed zapisem
document.querySelectorAll('input[type=file][data-preview]').forEach(inp => inp.addEventListener('change', () => {
  const img = document.getElementById(inp.dataset.preview);
  if (inp.files[0]) { img.src = URL.createObjectURL(inp.files[0]); img.hidden = false; }
}));
// Potwierdzenie usuwania
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
  if (!confirm(f.dataset.confirm)) e.preventDefault();
}));
</script>
</body>
</html>
<?php }
