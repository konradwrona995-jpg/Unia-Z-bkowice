<?php
/** @var string $tytul  @var string $aktywna */
require_once __DIR__ . '/liga.php';
$menu = [
    'index.php'          => 'Start',
    'aktualnosci.php'    => 'Aktualności',
    'rozgrywki.php'      => 'Rozgrywki',
    'klub.php'           => 'Klub',
    'index.php#akademia' => 'Akademia',
    'index.php#kontakt'  => 'Kontakt',
];
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($tytul ?? $KLUB['nazwa']) ?></title>
<meta name="description" content="Oficjalna strona KS Unia Ząbkowice – klubu piłkarskiego z Dąbrowy Górniczej. Wyniki, tabela, terminarz, historia od 1922 roku.">
<meta name="theme-color" content="#0b1f45">
<link rel="icon" href="assets/img/unia-herb.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<header class="topbar">
  <div class="wrap topbar__in">
    <a class="brand" href="index.php">
      <img src="assets/img/unia-herb.svg" alt="" width="44" height="44">
      <span><b>Unia</b> Ząbkowice<small>od <?= $KLUB['rok'] ?></small></span>
    </a>
    <button class="burger" aria-label="Menu" aria-expanded="false" aria-controls="nav"><span></span></button>
    <nav id="nav" class="nav">
      <?php foreach ($menu as $href => $label): ?>
        <a href="<?= $href ?>"<?= ($aktywna ?? '') === $href ? ' class="on"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
      <span class="nav__social">
        <a href="<?= e($KLUB['social']['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M14 8h3V4h-3c-2.8 0-4.5 1.8-4.5 4.6V11H7v4h2.5v9h4v-9h3l.5-4h-3.5V8.8c0-.5.3-.8.5-.8z"/></svg></a>
        <a href="<?= e($KLUB['social']['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4zM12 2c-2.7 0-3 0-4.1.1C4.3 2.3 2.3 4.3 2.1 7.9 2 9 2 9.3 2 12s0 3 .1 4.1c.2 3.6 2.2 5.6 5.8 5.8 1.1.1 1.4.1 4.1.1s3 0 4.1-.1c3.6-.2 5.6-2.2 5.8-5.8.1-1.1.1-1.4.1-4.1s0-3-.1-4.1c-.2-3.6-2.2-5.6-5.8-5.8C15 2 14.7 2 12 2zm0 1.8c2.7 0 3 0 4 .1 2.6.1 3.9 1.4 4 4 .1 1 .1 1.3.1 4s0 3-.1 4c-.1 2.6-1.4 3.9-4 4-1 .1-1.3.1-4 .1s-3 0-4-.1c-2.6-.1-3.9-1.4-4-4-.1-1-.1-1.3-.1-4s0-3 .1-4c.1-2.6 1.4-3.9 4-4 1-.1 1.3-.1 4-.1z"/></svg></a>
      </span>
    </nav>
  </div>
</header>
<main>
