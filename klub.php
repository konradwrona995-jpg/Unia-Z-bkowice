<?php
$tytul   = 'Klub i historia – KS Unia Ząbkowice';
$aktywna = 'klub.php';
require __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';
?>

<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Od <?= $KLUB['rok'] ?> roku</p>
    <h1>Klub</h1>
    <p><?= e($KLUB['pelna_nazwa']) ?></p>
  </div>
</section>

<section class="section">
  <div class="wrap split">
    <div class="prose">
      <h2>Historia</h2>
      <p>Klub powstał w <b>1922 roku</b> pod nazwą <b>Orzeł Ząbkowice</b>. Założyli go miłośnicy sportu związani z Zakładem Tworzyw Sztucznych „Erg” i z koleją. Od początku klub działa w tym samym miejscu, przy dzisiejszej Alei Zwycięstwa (dawniej Alei Wyzwolenia).</p>
      <p>Przez lata Unia była klubem wielosekcyjnym: obok piłki nożnej działały sekcje tenisa stołowego, koszykówki i siatkówki. Siatkarze w latach sześćdziesiątych wywalczyli lokatę dającą awans do I ligi. Duże zasługi mieli tu <i>Kazimierz Falfus, Waldemar Wspaniały, Kazimierz Murzyn i Małgorzata Sromek</i>.</p>
      <p>W <b>1974 roku</b> oddano do użytku stadion z dwiema pełnowymiarowymi płytami i budynkiem zaplecza. Klub gra na nim do dziś. Obecnie działa wyłącznie sekcja piłkarska.</p>
      <p>Ostatnie lata to historia odbudowy. Po spadku do klasy B drużyna wywalczyła <b>dwa awanse z rzędu</b> i w sezonie 2025/26 wróciła do klasy okręgowej.</p>
      <p class="muted small">Źródła: Wikipedia, 90minut.pl. Klub może tę historię uzupełnić o zdjęcia, nazwiska legend i wspomnienia kibiców.</p>
    </div>
    <aside class="card clubcard">
      <img src="assets/img/unia-herb.svg" alt="Herb KS Unia Ząbkowice" width="160" height="160">
      <dl class="facts">
        <div><dt>Założony</dt><dd><?= $KLUB['rok'] ?> (jako Orzeł Ząbkowice)</dd></div>
        <div><dt>Barwy</dt><dd><span class="swatch swatch--w"></span><span class="swatch swatch--b"></span> <?= e($KLUB['barwy']) ?></dd></div>
        <div><dt>Stadion</dt><dd><?= e($KLUB['stadion']['nazwa']) ?>, <?= e($KLUB['stadion']['pojemnosc']) ?></dd></div>
        <div><dt>Prezes</dt><dd><?= e($KLUB['prezes']) ?></dd></div>
        <div><dt>Trener</dt><dd><?= e($KLUB['trener']) ?></dd></div>
        <div><dt>Liga</dt><dd><?= e($KLUB['liga']) ?></dd></div>
      </dl>
    </aside>
  </div>
</section>

<section class="section section--alt">
  <div class="wrap">
    <div class="section__head"><h2>Kadra</h2></div>
    <?php $kadra = array_filter(kadra()); ?>
    <?php if ($kadra): ?>
    <div class="squad">
      <?php foreach ($kadra as $grupa => $gracze): ?>
        <div class="squad__group">
          <h3><?= $grupa ?></h3>
          <div class="squad__list">
            <?php foreach ($gracze as $z): ?>
              <div class="player<?= $z['zdjecie'] ? '' : ' player--nophoto' ?>">
                <?php if ($z['numer'] !== null): ?><span class="player__nr"><?= (int)$z['numer'] ?></span><?php endif; ?>
                <?php if ($z['kapitan']): ?><span class="player__cap" title="Kapitan">C</span><?php endif; ?>
                <?php if ($z['zdjecie']): ?><img class="player__img" src="<?= e($z['zdjecie']) ?>" alt="" loading="lazy">
                <?php else: ?><span class="player__img"><img src="assets/img/unia-herb.svg" alt=""></span><?php endif; ?>
                <span class="player__name"><small><?= e($z['imie']) ?></small><?= e($z['nazwisko']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="muted">Prezentacja kadry na sezon <?= e($KLUB['sezon']) ?> już wkrótce.</p>
    <?php endif; ?>
    <div class="staff">
      <div><small>Trener</small><b><?= e($KLUB['trener']) ?></b></div>
      <div><small>Prezes</small><b><?= e($KLUB['prezes']) ?></b></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
