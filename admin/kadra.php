<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usun'])) {
    sprawdz_csrf();
    $st = db()->prepare('SELECT * FROM zawodnicy WHERE id = ?');
    $st->execute([(int)$_POST['usun']]);
    if ($z = $st->fetch()) {
        usun_zdjecie($z['zdjecie']);
        db()->prepare('DELETE FROM zawodnicy WHERE id = ?')->execute([$z['id']]);
        flash("Usunięto z kadry: {$z['imie']} {$z['nazwisko']}");
    }
    przekieruj('kadra.php');
}

$kadra = kadra();
panel_start('Kadra', 'kadra');
?>
<div class="page__head">
  <h1>Kadra pierwszej drużyny</h1>
  <a class="btn btn--primary" href="zawodnik.php">+ Dodaj zawodnika</a>
</div>

<?php foreach ($kadra as $grupa => $gracze): ?>
<section class="card card--flush">
  <div class="card__head card__head--pad"><h2><?= $grupa ?> <span class="muted">(<?= count($gracze) ?>)</span></h2>
    <a href="zawodnik.php?pozycja=<?= urlencode(POZYCJE[$grupa]) ?>">+ Dodaj</a></div>
  <?php if ($gracze): ?>
  <table class="list">
    <tbody>
    <?php foreach ($gracze as $z): ?>
      <tr>
        <td class="thumb thumb--player"><?php if ($z['zdjecie']): ?><img src="../<?= e($z['zdjecie']) ?>" alt=""><?php else: ?><span class="thumb__ph"></span><?php endif; ?></td>
        <td class="nr"><?= $z['numer'] !== null ? (int)$z['numer'] : '–' ?></td>
        <td><a href="zawodnik.php?id=<?= $z['id'] ?>"><b><?= e($z['imie'] . ' ' . $z['nazwisko']) ?></b></a>
          <?= $z['kapitan'] ? ' <span class="pill">kapitan</span>' : '' ?></td>
        <td class="muted"><?= $z['rocznik'] ? 'rocznik ' . (int)$z['rocznik'] : '' ?></td>
        <td class="actions">
          <a href="zawodnik.php?id=<?= $z['id'] ?>">Edytuj</a>
          <form method="post" data-confirm="Usunąć <?= e($z['imie'] . ' ' . $z['nazwisko']) ?> z kadry?">
            <?= csrf_pole() ?><button name="usun" value="<?= $z['id'] ?>" class="link-danger">Usuń</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p class="empty">Nikogo jeszcze nie ma na tej pozycji.</p>
  <?php endif; ?>
</section>
<?php endforeach; ?>
<?php panel_koniec();
