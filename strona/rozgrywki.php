<?php
$tytul   = 'Tabela i terminarz – KS Unia Ząbkowice';
$aktywna = 'rozgrywki.php';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/widoki.php';

$liga  = liga_dane();
$nasze = liga_nasze_mecze($liga);
$rozegrane = array_reverse(liga_rozegrane($nasze)); // najnowszy na górze
$nadchodzace = liga_nadchodzace($nasze);
$bilans = ['W' => 0, 'R' => 0, 'P' => 0];
foreach ($rozegrane as $m) $bilans[liga_rezultat($m)]++;
?>

<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Sezon <?= e($KLUB['sezon']) ?></p>
    <h1>Rozgrywki</h1>
    <p><?= e($KLUB['liga']) ?></p>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section__head">
      <h2>Tabela ligowa</h2>
      <span class="muted small">Aktualizacja: <?= date('d.m.Y, H:i', $liga['pobrano']) ?></span>
    </div>
    <?php widok_tabela($liga['tabela']); ?>
    <p class="legend"><span class="lg lg--up"></span> awans do IV ligi <span class="lg lg--down"></span> strefa spadkowa (orientacyjnie)</p>
  </div>
</section>

<section class="section section--alt" id="terminarz">
  <div class="wrap">
    <div class="fixtures">
      <div class="fixtures__col">
        <header class="fixtures__head">
          <div><small>Przed nami</small><h2>Terminarz</h2></div>
          <span class="fixtures__meta"><b><?= count($nadchodzace) ?></b> meczów do końca sezonu</span>
        </header>
        <?php widok_lista_meczow($nadchodzace, 5, 'Pokaż wszystkie do końca sezonu'); ?>
      </div>
      <span class="fixtures__divider" aria-hidden="true"><img src="assets/img/unia-herb.svg" alt=""></span>
      <div class="fixtures__col">
        <header class="fixtures__head">
          <div><small>Za nami</small><h2>Wyniki</h2></div>
          <span class="fixtures__meta record">
            <span class="badge badge--w" title="Wygrane">W</span><b><?= $bilans['W'] ?></b>
            <span class="badge badge--r" title="Remisy">R</span><b><?= $bilans['R'] ?></b>
            <span class="badge badge--p" title="Porażki">P</span><b><?= $bilans['P'] ?></b>
          </span>
        </header>
        <?php widok_lista_meczow($rozegrane, 5, 'Pokaż wszystkie wyniki'); ?>
      </div>
    </div>
    <p class="muted small fixtures-note">Mecze bez daty czekają na ustalenie terminu przez Śląski ZPN. Dane: <a href="<?= e($liga['zrodlo'] ?? LIGA_URL) ?>" target="_blank" rel="noopener">90minut.pl</a>.</p>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
