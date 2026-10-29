<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usun'])) {
    sprawdz_csrf();
    if ($n = news_jeden((int)$_POST['usun'])) {
        usun_zdjecie($n['zdjecie']);
        db()->prepare('DELETE FROM aktualnosci WHERE id = ?')->execute([$n['id']]);
        flash('Usunięto: ' . $n['tytul']);
    }
    przekieruj('aktualnosci.php');
}

$lista = news_lista(0, false);
panel_start('Aktualności', 'news');
?>
<div class="page__head">
  <h1>Aktualności</h1>
  <a class="btn btn--primary" href="aktualnosc.php">+ Nowa aktualność</a>
</div>

<div class="card card--flush">
  <table class="list">
    <thead><tr><th></th><th>Tytuł</th><th>Kategoria</th><th>Data</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($lista as $n): ?>
      <tr>
        <td class="thumb"><?php if ($n['zdjecie']): ?><img src="../<?= e($n['zdjecie']) ?>" alt=""><?php else: ?><span class="thumb__ph"></span><?php endif; ?></td>
        <td><a href="aktualnosc.php?id=<?= $n['id'] ?>"><b><?= e($n['tytul']) ?></b></a>
          <?= $n['opublikowany'] ? '' : ' <span class="pill pill--draft">szkic</span>' ?></td>
        <td><span class="pill"><?= e($n['kat']) ?></span></td>
        <td class="nowrap muted"><?= e(date('d.m.Y', strtotime($n['data']))) ?></td>
        <td class="actions">
          <a href="aktualnosc.php?id=<?= $n['id'] ?>">Edytuj</a>
          <form method="post" data-confirm="Usunąć „<?= e($n['tytul']) ?>”? Tego nie da się cofnąć.">
            <?= csrf_pole() ?><button name="usun" value="<?= $n['id'] ?>" class="link-danger">Usuń</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="5" class="empty">Brak wpisów. Dodaj pierwszą aktualność!</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php panel_koniec();
