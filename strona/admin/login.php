<?php
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/layout.php';

if (zalogowany()) przekieruj('index.php');

$pierwsze = !ma_konta();
$blad = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sprawdz_csrf();
    $login = trim($_POST['login'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if ($pierwsze) {
        // Pierwsze uruchomienie: zakładamy konto administratora.
        if (!preg_match('/^[\p{L}0-9._-]{3,30}$/u', $login)) $blad = 'Login: 3–30 znaków, litery, cyfry, kropka, myślnik.';
        elseif (mb_strlen($haslo) < 8) $blad = 'Hasło musi mieć co najmniej 8 znaków.';
        elseif ($haslo !== ($_POST['haslo2'] ?? '')) $blad = 'Hasła nie są takie same.';
        else {
            db()->prepare('INSERT INTO uzytkownicy (login, haslo) VALUES (?, ?)')
                ->execute([$login, password_hash($haslo, PASSWORD_DEFAULT)]);
            zaloguj($login, $haslo);
            flash('Konto utworzone. Witaj w panelu!');
            przekieruj('index.php');
        }
    } elseif (zaloguj($login, $haslo)) {
        przekieruj('index.php');
    } else {
        sleep(1); // spowalnia zgadywanie hasła
        $blad = 'Nieprawidłowy login lub hasło.';
    }
}

panel_start($pierwsze ? 'Pierwsze uruchomienie' : 'Logowanie');
?>
<form method="post" class="card login">
  <img src="../assets/img/unia-herb.svg" alt="" width="72" height="72">
  <h1><?= $pierwsze ? 'Załóż konto administratora' : 'Panel klubu' ?></h1>
  <?php if ($pierwsze): ?><p class="muted">To pierwsze uruchomienie panelu. Ustal login i hasło – będą potrzebne przy każdym logowaniu.</p><?php endif; ?>
  <?php if ($blad): ?><div class="flash flash--err"><?= e($blad) ?></div><?php endif; ?>
  <?= csrf_pole() ?>
  <label>Login<input name="login" required autocomplete="username" value="<?= e($_POST['login'] ?? '') ?>" autofocus></label>
  <label>Hasło<input name="haslo" type="password" required autocomplete="<?= $pierwsze ? 'new-password' : 'current-password' ?>"<?= $pierwsze ? ' minlength="8"' : '' ?>></label>
  <?php if ($pierwsze): ?><label>Powtórz hasło<input name="haslo2" type="password" required autocomplete="new-password" minlength="8"></label><?php endif; ?>
  <button class="btn btn--primary"><?= $pierwsze ? 'Utwórz konto' : 'Zaloguj się' ?></button>
  <a class="muted small" href="../index.php">← Wróć na stronę</a>
</form>
<?php panel_koniec();
