<?php
require_once __DIR__ . '/inc/db.php';

$n = news_jeden((int)($_GET['id'] ?? 0));
if (!$n || !$n['opublikowany']) {
    http_response_code(404);
    $n = null;
}

$tytul   = ($n ? $n['tytul'] : 'Nie znaleziono') . ' – KS Unia Ząbkowice';
$aktywna = 'aktualnosci.php';
require __DIR__ . '/inc/header.php';

$inne = $n ? array_values(array_filter(news_lista(4), fn($x) => $x['id'] !== $n['id'])) : [];
?>

<?php if (!$n): ?>
<section class="pagehead"><div class="wrap"><h1>Nie znaleziono</h1><p>Ten wpis nie istnieje albo został usunięty.</p></div></section>
<section class="section"><div class="wrap"><a class="btn btn--primary" href="aktualnosci.php">Wszystkie aktualności</a></div></section>
<?php else: ?>

<article>
  <header class="pagehead pagehead--article">
    <div class="wrap wrap--narrow">
      <p class="news__meta news__meta--light"><span class="tag"><?= e($n['kat']) ?></span> <?= e(data_pl($n['data'])) ?></p>
      <h1><?= e($n['tytul']) ?></h1>
    </div>
  </header>
  <div class="wrap wrap--narrow article">
    <?php if ($n['zdjecie']): ?><img class="article__img" src="<?= e($n['zdjecie']) ?>" alt=""><?php endif; ?>
    <p class="article__lead"><?= e($n['lead']) ?></p>
    <?= akapity($n['tresc']) ?>
    <p class="article__back"><a href="aktualnosci.php">← Wszystkie aktualności</a></p>
  </div>
</article>

<?php if ($inne): ?>
<section class="section section--alt">
  <div class="wrap">
    <div class="section__head"><h2>Czytaj też</h2></div>
    <div class="newslist">
      <?php foreach (array_slice($inne, 0, 3) as $x): ?>
      <a href="aktualnosc.php?id=<?= $x['id'] ?>" class="news__item">
        <div class="news__body">
          <p class="news__meta"><span class="tag tag--soft"><?= e($x['kat']) ?></span> <?= e(data_pl($x['data'], false)) ?></p>
          <h3><?= e($x['tytul']) ?></h3>
          <p><?= e($x['lead']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/inc/footer.php'; ?>
