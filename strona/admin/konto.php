<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';
wymagaj_logowania();

$me = zalogowany();
$bledy = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sprawdz_csrf();
    $akcja = $_POST['akcja'] ?? '';

    if ($akcja === 'haslo') {
        $st = db()->prepare('SELECT haslo FROM uzytkownicy WHERE id = ?');
        $st->execute([$me['id']]);
        if (!password_verify($_POST['stare'] ?? '', $st->fetchColumn())) $bledy[] = 'Obecne hasło jest nieprawidłowe.';
        elseif (mb_strlen($_POST['nowe'] ?? '') < 8) $bledy[] = 'Nowe hasło musi mieć co najmniej 8 znaków.';
        elseif ($_POST['nowe'] !== ($_POST['nowe2'] ?? '')) $bledy[] = 'Nowe hasła nie są takie same.';
        else {
            db()->prepare('UPDATE uzytkownicy SET haslo = ? WHERE id = ?')->execute([password_hash($_POST['nowe'], PASSWORD_DEFAULT), $me['id']]);
            flash('Hasło zmienione.');
            przekieruj('konto.php');
        }
    }

    if ($akcja === 'dodaj') {
        $login = trim($_POST['login'] ?? '');
        if (!preg_match('/^[\p{L}0-9._-]{3,30}$/u', $login)) $bledy[] = 'Login: 3–30 znaków, litery, cyfry, kropka, myślnik.';
        elseif (mb_strlen($_POST['haslo'] ?? '') < 8) $bledy[] = 'Hasło musi mieć co najmniej 8 znaków.';
        else {
            try {
                db()->prepare('INSERT INTO uzytkownicy (login, haslo) VALUES (?, ?)')->execute([$login, password_hash($_POST['haslo'], PASSWORD_DEFAULT)]);
                flash("Dodano konto: $login");
                przekieruj('konto.php');
            } catch (PDOException $ex) {
                $bledy[] = 'Taki login już istnieje.';
            }
        }
    }

    if ($akcja === 'usun' && (int)$_POST['id'] !== $me['id']) {
        db()->prepare('DELETE FROM uzytkownicy WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('Konto usunięte.');
        przekieruj('konto.php');
    }
}

$konta = db()->query('SELECT id, login, utworzono FROM uzytkownicy ORDER BY login')->fetchAll();
panel_start('Konto', 'konto');
?>
<div class="page__head"><h1>Konto i dostęp</h1></div>
<?php if ($bledy): ?><div class="flash flash--err"><?= implode('<br>', array_map('e', $bledy)) ?></div><?php endif; ?>

<div class="form-grid form-grid--even">
  <form method="post" class="card stack">
    <h2>Zmień swoje hasło</h2>
    <?= csrf_pole() ?><input type="hidden" name="akcja" value="haslo">
    <input type="text" name="username" value="<?= e($me['login']) ?>" autocomplete="username" hidden>
    <label>Obecne hasło<input type="password" name="stare" required autocomplete="current-password"></label>
    <label>Nowe hasło<input type="password" name="nowe" required minlength="8" autocomplete="new-password"></label>
    <label>Powtórz nowe hasło<input type="password" name="nowe2" required minlength="8" autocomplete="new-password"></label>
    <button class="btn btn--primary">Zmień hasło</button>
  </form>

  <div class="card stack">
    <h2>Osoby z dostępem</h2>
    <table class="list">
      <?php foreach ($konta as $k): ?>
      <tr>
        <td><b><?= e($k['login']) ?></b><?= (int)$k['id'] === $me['id'] ? ' <span class="pill">to Ty</span>' : '' ?></td>
        <td class="actions"><?php if ((int)$k['id'] !== $me['id']): ?>
          <form method="post" data-confirm="Odebrać dostęp użytkownikowi <?= e($k['login']) ?>?">
            <?= csrf_pole() ?><input type="hidden" name="akcja" value="usun"><button name="id" value="<?= $k['id'] ?>" class="link-danger">Usuń</button>
          </form><?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <form method="post" class="stack">
      <h3>Dodaj osobę (np. trenera akademii)</h3>
      <?= csrf_pole() ?><input type="hidden" name="akcja" value="dodaj">
      <label>Login<input name="login" required autocomplete="off"></label>
      <label>Hasło startowe<input type="password" name="haslo" required minlength="8" autocomplete="new-password"></label>
      <button class="btn">Dodaj konto</button>
    </form>
  </div>
</div>
<?php panel_koniec();
