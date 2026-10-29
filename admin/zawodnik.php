<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

$id = (int)($_GET['id'] ?? 0);
$z = null;
if ($id) {
    $st = db()->prepare('SELECT * FROM zawodnicy WHERE id = ?');
    $st->execute([$id]);
    $z = $st->fetch() ?: przekieruj('kadra.php');
}
$z ??= ['imie' => '', 'nazwisko' => '', 'numer' => null, 'rocznik' => null, 'kapitan' => 0, 'zdjecie' => null,
        'pozycja' => in_array($_GET['pozycja'] ?? '', POZYCJE, true) ? $_GET['pozycja'] : 'Pomocnik'];
$bledy = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sprawdz_csrf();
    $z['imie']     = trim($_POST['imie'] ?? '');
    $z['nazwisko'] = trim($_POST['nazwisko'] ?? '');
    $z['pozycja']  = in_array($_POST['pozycja'] ?? '', POZYCJE, true) ? $_POST['pozycja'] : 'Pomocnik';
    $z['numer']    = ($_POST['numer'] ?? '') === '' ? null : (int)$_POST['numer'];
    $z['rocznik']  = ($_POST['rocznik'] ?? '') === '' ? null : (int)$_POST['rocznik'];
    $z['kapitan']  = isset($_POST['kapitan']) ? 1 : 0;

    if ($z['imie'] === '' || $z['nazwisko'] === '') $bledy[] = 'Wpisz imię i nazwisko.';
    if ($z['numer'] !== null && ($z['numer'] < 1 || $z['numer'] > 99)) $bledy[] = 'Numer: od 1 do 99.';
    if ($z['rocznik'] !== null && ($z['rocznik'] < 1960 || $z['rocznik'] > (int)date('Y') - 12)) $bledy[] = 'Sprawdź rok urodzenia.';
    if ($z['numer'] !== null) {
        $st = db()->prepare('SELECT imie, nazwisko FROM zawodnicy WHERE numer = ? AND id != ?');
        $st->execute([$z['numer'], $id]);
        if ($inny = $st->fetch()) $bledy[] = "Numer {$z['numer']} ma już {$inny['imie']} {$inny['nazwisko']}.";
    }

    $stareZdjecie = $z['zdjecie'];
    if (!$bledy && !empty($_FILES['zdjecie']['name'])) {
        try { $z['zdjecie'] = zapisz_zdjecie($_FILES['zdjecie'], 'kadra', 600, 125); } // 4:5
        catch (RuntimeException $ex) { $bledy[] = $ex->getMessage(); }
    } elseif (isset($_POST['usun_zdjecie'])) {
        $z['zdjecie'] = null;
    }

    if (!$bledy) {
        if ($z['kapitan']) db()->exec('UPDATE zawodnicy SET kapitan = 0'); // tylko jeden kapitan
        $p = [$z['imie'], $z['nazwisko'], $z['numer'], $z['pozycja'], $z['rocznik'], $z['zdjecie'], $z['kapitan']];
        if ($id) {
            db()->prepare('UPDATE zawodnicy SET imie=?, nazwisko=?, numer=?, pozycja=?, rocznik=?, zdjecie=?, kapitan=? WHERE id=?')
                ->execute([...$p, $id]);
        } else {
            db()->prepare('INSERT INTO zawodnicy (imie, nazwisko, numer, pozycja, rocznik, zdjecie, kapitan) VALUES (?,?,?,?,?,?,?)')
                ->execute($p);
        }
        if ($stareZdjecie !== $z['zdjecie']) usun_zdjecie($stareZdjecie);
        flash("Zapisano: {$z['imie']} {$z['nazwisko']}");
        przekieruj(isset($_POST['kolejny']) ? 'zawodnik.php?pozycja=' . urlencode($z['pozycja']) : 'kadra.php');
    }
}

panel_start($id ? 'Edycja zawodnika' : 'Nowy zawodnik', 'kadra');
?>
<div class="page__head"><h1><?= $id ? 'Edycja zawodnika' : 'Nowy zawodnik' ?></h1></div>
<?php if ($bledy): ?><div class="flash flash--err"><?= implode('<br>', array_map('e', $bledy)) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-grid form-grid--player">
  <?= csrf_pole() ?>
  <div class="card stack">
    <div class="row2">
      <label>Imię<input name="imie" required maxlength="40" value="<?= e($z['imie']) ?>" autofocus></label>
      <label>Nazwisko<input name="nazwisko" required maxlength="60" value="<?= e($z['nazwisko']) ?>"></label>
    </div>
    <div class="row3">
      <label>Pozycja<select name="pozycja">
        <?php foreach (POZYCJE as $poz): ?><option<?= $poz === $z['pozycja'] ? ' selected' : '' ?>><?= $poz ?></option><?php endforeach; ?>
      </select></label>
      <label>Numer<input name="numer" type="number" min="1" max="99" value="<?= e($z['numer']) ?>"></label>
      <label>Rok urodzenia<input name="rocznik" type="number" min="1960" max="<?= date('Y') - 12 ?>" value="<?= e($z['rocznik']) ?>" placeholder="opcjonalnie"></label>
    </div>
    <label class="check"><input type="checkbox" name="kapitan" <?= $z['kapitan'] ? 'checked' : '' ?>> Kapitan drużyny</label>
    <div class="buttons">
      <button class="btn btn--primary">Zapisz</button>
      <?php if (!$id): ?><button class="btn" name="kolejny" value="1">Zapisz i dodaj kolejnego</button><?php endif; ?>
      <a class="muted small" href="kadra.php">Anuluj</a>
    </div>
  </div>
  <div class="card stack">
    <span class="label">Zdjęcie <small>(przytniemy do formatu 4:5)</small></span>
    <img id="podglad" class="preview preview--player" src="<?= $z['zdjecie'] ? '../' . e($z['zdjecie']) : '' ?>" alt=""<?= $z['zdjecie'] ? '' : ' hidden' ?>>
    <input type="file" name="zdjecie" accept="image/jpeg,image/png,image/webp" data-preview="podglad">
    <?php if ($z['zdjecie']): ?><label class="check"><input type="checkbox" name="usun_zdjecie"> Usuń zdjęcie</label><?php endif; ?>
  </div>
</form>
<?php panel_koniec();
