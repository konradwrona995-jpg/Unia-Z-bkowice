<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

$id = (int)($_GET['id'] ?? 0);
$n = $id ? news_jeden($id) : null;
if ($id && !$n) przekieruj('aktualnosci.php');
$n ??= ['tytul' => '', 'data' => date('Y-m-d'), 'kat' => KATEGORIE[0], 'lead' => '', 'tresc' => '', 'zdjecie' => null, 'opublikowany' => 1];
$bledy = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sprawdz_csrf();
    $n['tytul']        = trim($_POST['tytul'] ?? '');
    $n['data']         = $_POST['data'] ?? '';
    $n['kat']          = in_array($_POST['kat'] ?? '', KATEGORIE, true) ? $_POST['kat'] : KATEGORIE[0];
    $n['lead']         = trim($_POST['lead'] ?? '');
    $n['tresc']        = trim($_POST['tresc'] ?? '');
    $n['opublikowany'] = isset($_POST['opublikowany']) ? 1 : 0;

    if ($n['tytul'] === '') $bledy[] = 'Wpisz tytuł.';
    if (mb_strlen($n['tytul']) > 120) $bledy[] = 'Tytuł może mieć maksymalnie 120 znaków.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $n['data'])) $bledy[] = 'Podaj poprawną datę.';
    if ($n['lead'] === '') $bledy[] = 'Wpisz krótki wstęp – pokazuje się na stronie głównej.';

    $stareZdjecie = $n['zdjecie'];
    if (!$bledy && !empty($_FILES['zdjecie']['name'])) {
        try { $n['zdjecie'] = zapisz_zdjecie($_FILES['zdjecie'], 'aktualnosci', 1600); }
        catch (RuntimeException $ex) { $bledy[] = $ex->getMessage(); }
    } elseif (isset($_POST['usun_zdjecie'])) {
        $n['zdjecie'] = null;
    }

    if (!$bledy) {
        $p = [$n['tytul'], $n['data'], $n['kat'], $n['lead'], $n['tresc'], $n['zdjecie'], $n['opublikowany']];
        if ($id) {
            db()->prepare('UPDATE aktualnosci SET tytul=?, data=?, kat=?, lead=?, tresc=?, zdjecie=?, opublikowany=? WHERE id=?')
                ->execute([...$p, $id]);
        } else {
            db()->prepare('INSERT INTO aktualnosci (tytul, data, kat, lead, tresc, zdjecie, opublikowany) VALUES (?,?,?,?,?,?,?)')
                ->execute($p);
            $id = (int)db()->lastInsertId();
        }
        if ($stareZdjecie !== $n['zdjecie']) usun_zdjecie($stareZdjecie);
        flash($n['opublikowany'] ? 'Zapisano i opublikowano.' : 'Zapisano jako szkic (niewidoczny na stronie).');
        przekieruj('aktualnosci.php');
    }
}

panel_start($id ? 'Edycja aktualności' : 'Nowa aktualność', 'news');
?>
<div class="page__head">
  <h1><?= $id ? 'Edycja aktualności' : 'Nowa aktualność' ?></h1>
  <?php if ($id && $n['opublikowany']): ?><a href="../aktualnosc.php?id=<?= $id ?>" target="_blank">Zobacz na stronie ↗</a><?php endif; ?>
</div>
<?php if ($bledy): ?><div class="flash flash--err"><?= implode('<br>', array_map('e', $bledy)) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-grid">
  <?= csrf_pole() ?>
  <div class="card stack">
    <label>Tytuł<input name="tytul" required maxlength="120" value="<?= e($n['tytul']) ?>" placeholder="np. Pewne 3:0 w Bielszowicach"></label>
    <label>Krótki wstęp <small>1–2 zdania, widoczne na stronie głównej</small>
      <textarea name="lead" rows="3" required maxlength="300"><?= e($n['lead']) ?></textarea></label>
    <label>Treść <small>pusta linia = nowy akapit</small>
      <textarea name="tresc" rows="14" placeholder="Pełna relacja, skład, strzelcy, wypowiedź trenera…"><?= e($n['tresc']) ?></textarea></label>
  </div>
  <aside class="stack">
    <div class="card stack">
      <label class="check"><input type="checkbox" name="opublikowany" <?= $n['opublikowany'] ? 'checked' : '' ?>> Opublikowany</label>
      <label>Data<input type="date" name="data" required value="<?= e($n['data']) ?>"></label>
      <label>Kategoria<select name="kat">
        <?php foreach (KATEGORIE as $k): ?><option<?= $k === $n['kat'] ? ' selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?>
      </select></label>
      <button class="btn btn--primary btn--block">Zapisz</button>
      <a class="muted small center" href="aktualnosci.php">Anuluj</a>
    </div>
    <div class="card stack">
      <span class="label">Zdjęcie główne</span>
      <img id="podglad" class="preview" src="<?= $n['zdjecie'] ? '../' . e($n['zdjecie']) : '' ?>" alt=""<?= $n['zdjecie'] ? '' : ' hidden' ?>>
      <input type="file" name="zdjecie" accept="image/jpeg,image/png,image/webp" data-preview="podglad">
      <?php if ($n['zdjecie']): ?><label class="check"><input type="checkbox" name="usun_zdjecie"> Usuń zdjęcie</label><?php endif; ?>
      <small class="muted">JPG, PNG lub WEBP. Duże zdjęcia z telefonu zmniejszymy automatycznie.</small>
    </div>
  </aside>
</form>
<?php panel_koniec();
