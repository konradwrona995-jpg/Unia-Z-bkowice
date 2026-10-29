<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

$ileNews   = (int)db()->query('SELECT COUNT(*) FROM aktualnosci WHERE opublikowany = 1')->fetchColumn();
$szkice    = (int)db()->query('SELECT COUNT(*) FROM aktualnosci WHERE opublikowany = 0')->fetchColumn();
$ileGraczy = (int)db()->query('SELECT COUNT(*) FROM zawodnicy')->fetchColumn();
$ostatnie  = news_lista(5, false);

panel_start('Start');
?>
<h1>Cześć, <?= e(zalogowany()['login']) ?>!</h1>
<p class="muted">Tu dodasz aktualności i zawodników. Tabela i terminarz aktualizują się na stronie same.</p>

<div class="tiles">
  <a class="tile" href="aktualnosc.php"><b>+ Nowa aktualność</b><span>Relacja, zapowiedź, ogłoszenie</span></a>
  <a class="tile" href="zawodnik.php"><b>+ Dodaj zawodnika</b><span>Imię, numer, pozycja, zdjęcie</span></a>
  <div class="tile tile--stat"><b><?= $ileNews ?></b><span>opublikowanych wpisów<?= $szkice ? " · $szkice szkic(ów)" : '' ?></span></div>
  <div class="tile tile--stat"><b><?= $ileGraczy ?></b><span>zawodników w kadrze</span></div>
</div>

<section class="card">
  <div class="card__head"><h2>Ostatnie wpisy</h2><a href="aktualnosci.php">Wszystkie →</a></div>
  <table class="list">
    <?php foreach ($ostatnie as $n): ?>
    <tr>
      <td class="nowrap muted"><?= e(date('d.m.Y', strtotime($n['data']))) ?></td>
      <td><a href="aktualnosc.php?id=<?= $n['id'] ?>"><?= e($n['tytul']) ?></a><?= $n['opublikowany'] ? '' : ' <span class="pill pill--draft">szkic</span>' ?></td>
      <td><span class="pill"><?= e($n['kat']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </table>
</section>
<?php panel_koniec();
