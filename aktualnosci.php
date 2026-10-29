<?php
$tytul   = 'Aktualności – KS Unia Ząbkowice';
$aktywna = 'aktualnosci.php';
require __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

$wszystkie = news_lista();
?>

<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Z życia klubu</p>
    <h1>Aktualności</h1>
    <p>Relacje z meczów, zapowiedzi i ogłoszenia klubu.</p>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="newslist">
      <?php foreach ($wszystkie as $n): ?>
      <a href="aktualnosc.php?id=<?= $n['id'] ?>" class="news__item">
        <?php if ($n['zdjecie']): ?>
        <div class="news__img news__img--photo"><img src="<?= e($n['zdjecie']) ?>" alt="" loading="lazy"></div>
        <?php else: ?>
        <div class="news__img news__img--<?= liga_slug($n['kat']) ?>" aria-hidden="true"><img src="assets/img/unia-herb.svg" alt=""></div>
        <?php endif; ?>
        <div class="news__body">
          <p class="news__meta"><span class="tag tag--soft"><?= e($n['kat']) ?></span> <?= e(data_pl($n['data'], false)) ?></p>
          <h3><?= e($n['tytul']) ?></h3>
          <p><?= e($n['lead']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$wszystkie): ?><p class="muted">Wkrótce pojawią się tu pierwsze wpisy.</p><?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
