<?php
$tytul   = 'KS Unia Ząbkowice – klub piłkarski z Dąbrowy Górniczej';
$aktywna = 'index.php';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/widoki.php';

$liga       = liga_dane();
$nasze      = liga_nasze_mecze($liga);
$rozegrane  = liga_rozegrane($nasze);
$nadchodzace = liga_nadchodzace($nasze);
$nastepny   = $nadchodzace[0] ?? null;
$ostatni    = end($rozegrane) ?: null;
$forma      = array_reverse(array_slice($rozegrane, -5));
$my         = liga_pozycja($liga);
require_once __DIR__ . '/inc/db.php';
$news       = news_lista(5);
$uSiebie    = $nastepny && $nastepny['gosp'] === NASZ_KLUB;
?>

<section class="hero">
  <div class="hero__stripes" aria-hidden="true"></div>
  <img class="hero__crest" src="assets/img/unia-herb.svg" alt="" aria-hidden="true">
  <div class="wrap hero__in">
    <div class="hero__intro">
      <p class="eyebrow">Dąbrowa Górnicza · Ząbkowice · od <?= $KLUB['rok'] ?></p>
      <h1>Biało-niebiescy<br><span>z Alei Zwycięstwa</span></h1>
      <p class="hero__lead"><?= date('Y') - $KLUB['rok'] ?> lata piłki w Ząbkowicach. Gramy w klasie okręgowej, szkolimy dzieciaki w akademii i zawsze czekamy na Was na trybunach.</p>
      <div class="hero__cta">
        <a class="btn btn--light" href="rozgrywki.php">Tabela i terminarz</a>
        <a class="btn btn--ghost" href="#akademia">Zapisz dziecko</a>
      </div>
    </div>

    <?php if ($nastepny): ?>
    <article class="nextmatch" data-start="<?= e($nastepny['data']) ?>"
             data-title="<?= e($nastepny['gosp'] . ' – ' . $nastepny['gosc']) ?>"
             data-place="<?= e($uSiebie ? $KLUB['stadion']['nazwa'] . ', ' . $KLUB['adres'] : 'na wyjeździe') ?>">
      <header>
        <span class="tag"><?= $uSiebie ? 'Mecz u siebie' : 'Wyjazd' ?></span>
        <span>Najbliższy mecz · <?= $nastepny['kolejka'] ?>. kolejka</span>
      </header>
      <div class="nextmatch__teams">
        <div><?= herb($nastepny['gosp_slug'], $nastepny['gosp'], 'herb herb--lg') ?><b><?= e($nastepny['gosp']) ?></b></div>
        <span class="vs">vs</span>
        <div><?= herb($nastepny['gosc_slug'], $nastepny['gosc'], 'herb herb--lg') ?><b><?= e($nastepny['gosc']) ?></b></div>
      </div>
      <p class="nextmatch__when"><?= e(data_pl($nastepny['data'])) ?><br>
        <small><?= $uSiebie ? e($KLUB['stadion']['nazwa'] . ', Al. Zwycięstwa 29') : 'Stadion gospodarzy' ?></small></p>
      <?php if ($nastepny['data']): ?>
      <div class="countdown" aria-live="polite">
        <div><b data-cd="d">–</b><small>dni</small></div>
        <div><b data-cd="h">–</b><small>godz</small></div>
        <div><b data-cd="m">–</b><small>min</small></div>
        <div><b data-cd="s">–</b><small>sek</small></div>
      </div>
      <div class="nextmatch__actions">
        <button class="btn btn--primary" data-ics>Dodaj do kalendarza</button>
        <?php if ($uSiebie): ?><a class="btn btn--ghost-dark" href="#stadion">Jak dojechać</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </article>
    <?php endif; ?>
  </div>
</section>

<section class="stats">
  <div class="wrap stats__grid">
    <div class="stat">
      <small>Miejsce w tabeli</small>
      <b><?= $my ? $my['poz'] . '.' : '–' ?></b>
      <span><?= $my ? $my['pkt'] . ' pkt · ' . $my['m'] . ' meczów' : '' ?></span>
    </div>
    <div class="stat">
      <small>Ostatni mecz</small>
      <?php if ($ostatni): ?>
      <?php $dom = $ostatni['gosp'] === NASZ_KLUB; [$a, $b] = $dom ? $ostatni['wynik'] : array_reverse($ostatni['wynik']); ?>
      <b class="res-<?= strtolower(liga_rezultat($ostatni)) ?>"><?= $a ?>:<?= $b ?></b>
      <span><?= e(($dom ? 'u siebie z ' . $ostatni['gosc'] : 'na wyjeździe z ' . $ostatni['gosp'])) ?></span>
      <?php endif; ?>
    </div>
    <div class="stat">
      <small>Forma (ostatnie 5)</small>
      <div class="form">
        <?php foreach ($forma as $m): $r = liga_rezultat($m); ?>
          <span class="badge badge--<?= strtolower($r) ?>" title="<?= e($m['gosp'] . ' ' . implode(':', $m['wynik']) . ' ' . $m['gosc']) ?>"><?= $r ?></span>
        <?php endforeach; ?>
      </div>
      <span>najnowszy po lewej</span>
    </div>
    <div class="stat">
      <small>Bramki w sezonie</small>
      <b><?= $my ? $my['bz'] : '–' ?></b>
      <span><?= $my ? 'średnio ' . number_format($my['bz'] / max(1, $my['m']), 1, ',', '') . ' na mecz' : '' ?></span>
    </div>
  </div>
</section>

<section class="section" id="aktualnosci">
  <div class="wrap">
    <div class="section__head">
      <h2>Aktualności</h2>
      <a class="more" href="aktualnosci.php">Wszystkie aktualności →</a>
    </div>
    <div class="news">
      <?php foreach ($news as $i => $n): ?>
      <a href="aktualnosc.php?id=<?= $n['id'] ?>" class="news__item<?= $i === 0 ? ' news__item--big' : '' ?>">
        <?php if ($n['zdjecie']): ?>
        <div class="news__img news__img--photo"><img src="<?= e($n['zdjecie']) ?>" alt="" loading="lazy"></div>
        <?php else: ?>
        <div class="news__img news__img--<?= liga_slug($n['kat']) ?>" aria-hidden="true">
          <img src="assets/img/unia-herb.svg" alt="">
        </div>
        <?php endif; ?>
        <div class="news__body">
          <p class="news__meta"><span class="tag tag--soft"><?= e($n['kat']) ?></span> <?= e(data_pl($n['data'], false)) ?></p>
          <h3><?= e($n['tytul']) ?></h3>
          <p><?= e($n['lead']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--alt" id="rozgrywki">
  <div class="wrap split">
    <div>
      <div class="section__head">
        <h2>Tabela</h2>
        <a class="more" href="rozgrywki.php">Pełna tabela →</a>
      </div>
      <p class="muted small"><?= e($KLUB['liga']) ?> · sezon <?= e($KLUB['sezon']) ?></p>
      <?php widok_tabela($liga['tabela'], true); ?>
      <p class="muted small updated">Aktualizacja: <?= date('d.m.Y, H:i', $liga['pobrano']) ?> · źródło: 90minut.pl</p>
    </div>
    <div>
      <div class="section__head">
        <h2>Terminarz</h2>
        <a class="more" href="rozgrywki.php#terminarz">Wszystkie mecze →</a>
      </div>
      <h3 class="subhead">Ostatnie wyniki</h3>
      <ul class="matches">
        <?php foreach (array_reverse(array_slice($rozegrane, -3)) as $m) widok_mecz($m); ?>
      </ul>
      <h3 class="subhead">Kolejne mecze</h3>
      <ul class="matches">
        <?php foreach (array_slice($nadchodzace, 0, 3) as $m) widok_mecz($m); ?>
      </ul>
    </div>
  </div>
</section>

<section class="section history-teaser">
  <div class="wrap split split--center">
    <div>
      <p class="eyebrow eyebrow--dark">Od <?= $KLUB['rok'] ?> roku</p>
      <h2>Ponad sto lat piłki w Ząbkowicach</h2>
      <p>Wszystko zaczęło się w 1922 roku od Orła Ząbkowice, który założyli pracownicy zakładów „Erg” i kolejarze. W 1974 roku klub dostał stadion z dwoma pełnowymiarowymi boiskami. Później przyszły lata w IV lidze i trudne sezony w klasie B, a potem dwa awanse z rzędu.</p>
      <a class="btn btn--primary" href="klub.php">Poznaj historię klubu</a>
    </div>
    <ol class="timeline timeline--mini">
      <li><b>1922</b><span>Powstaje Orzeł Ząbkowice</span></li>
      <li><b>1974</b><span>Otwarcie stadionu przy Alei Zwycięstwa</span></li>
      <li><b>2012</b><span>Awans do IV ligi</span></li>
      <li><b>2024</b><span>Awans z klasy B do A-klasy</span></li>
      <li><b>2025</b><span>Powrót do klasy okręgowej</span></li>
    </ol>
  </div>
</section>

<section class="section academy" id="akademia">
  <div class="wrap split split--center">
    <div>
      <p class="eyebrow">Akademia KS Unia Ząbkowice</p>
      <h2>Twoje dziecko chce grać w piłkę? Zapraszamy!</h2>
      <p>Prowadzimy treningi dla dzieci i młodzieży z Ząbkowic i okolic. Szukamy przede wszystkim radości z gry, a nie od razu talentów. Przyjdź na trening próbny i zobacz, jak u nas jest.</p>
      <ul class="checks">
        <li>Treningi w grupach wiekowych</li>
        <li>Blisko domu – stadion przy Al. Zwycięstwa</li>
        <li>Turnieje i mecze ligowe</li>
      </ul>
    </div>
    <form class="card form-card" onsubmit="event.preventDefault(); this.querySelector('.form-ok').hidden=false; this.reset();">
      <h3>Zapisz się na trening próbny</h3>
      <label>Imię i nazwisko rodzica<input required name="rodzic" autocomplete="name"></label>
      <label>Rok urodzenia dziecka<input required name="rocznik" inputmode="numeric" pattern="20[0-9]{2}" placeholder="np. 2016"></label>
      <label>Telefon<input required name="tel" type="tel" autocomplete="tel"></label>
      <button class="btn btn--primary btn--block">Wyślij zgłoszenie</button>
      <p class="form-ok" hidden>Dziękujemy! Trener oddzwoni w ciągu kilku dni. <small>(W prototypie formularz niczego jeszcze nie wysyła.)</small></p>
    </form>
  </div>
</section>

<section class="section" id="stadion">
  <div class="wrap split">
    <div>
      <div class="section__head"><h2>Stadion</h2></div>
      <dl class="facts">
        <div><dt>Adres</dt><dd><?= e($KLUB['adres']) ?></dd></div>
        <div><dt>Pojemność</dt><dd><?= e($KLUB['stadion']['pojemnosc']) ?></dd></div>
        <div><dt>Boisko</dt><dd><?= e($KLUB['stadion']['boisko']) ?></dd></div>
        <div><dt>Otwarty</dt><dd><?= $KLUB['stadion']['otwarcie'] ?> r. – dwie pełnowymiarowe płyty</dd></div>
        <div><dt>Dojazd</dt><dd>Najbliższa stacja kolejowa: Dąbrowa Górnicza Ząbkowice</dd></div>
      </dl>
      <a class="btn btn--primary" target="_blank" rel="noopener"
         href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($KLUB['adres']) ?>">Wyznacz trasę</a>
    </div>
    <div class="map">
      <?php $s = $KLUB['stadion']; $d = 0.006; ?>
      <iframe title="Mapa – stadion Unii Ząbkowice" loading="lazy"
        src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $s['lon']-$d ?>,<?= $s['lat']-$d/2 ?>,<?= $s['lon']+$d ?>,<?= $s['lat']+$d/2 ?>&layer=mapnik&marker=<?= $s['lat'] ?>,<?= $s['lon'] ?>"></iframe>
    </div>
  </div>
</section>

<section class="section section--alt" id="partnerzy">
  <div class="wrap">
    <div class="section__head"><h2>Partnerzy klubu</h2></div>
    <div class="partners">
      <?php foreach (['Partner główny', 'Partner', 'Partner', 'Partner', 'Partner', 'Partner medialny'] as $p): ?>
        <div class="partner"><span><?= e($p) ?></span></div>
      <?php endforeach; ?>
    </div>
    <div class="partner-cta card">
      <div>
        <h3>Prowadzisz firmę w Ząbkowicach lub okolicy?</h3>
        <p>Twoje logo może być tutaj, na bandzie i na koszulkach. Wspierasz lokalny sport, a klub dziękuje Ci przed każdym meczem.</p>
      </div>
      <a class="btn btn--primary" href="mailto:<?= e($KLUB['email']) ?>?subject=Wsparcie%20KS%20Unia%20Z%C4%85bkowice">Zostań partnerem</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
