<?php
// Sesja, logowanie i ochrona formularzy (CSRF) dla panelu.
require_once __DIR__ . '/../../inc/db.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_name('unia_admin');
session_start();

function ma_konta(): bool
{
    return (int)db()->query('SELECT COUNT(*) FROM uzytkownicy')->fetchColumn() > 0;
}

function zalogowany(): ?array
{
    return $_SESSION['user'] ?? null;
}

function wymagaj_logowania(): void
{
    if (!zalogowany()) {
        header('Location: login.php');
        exit;
    }
}

function zaloguj(string $login, string $haslo): bool
{
    $st = db()->prepare('SELECT * FROM uzytkownicy WHERE login = ?');
    $st->execute([$login]);
    $u = $st->fetch();
    if (!$u || !password_verify($haslo, $u['haslo'])) return false;
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$u['id'], 'login' => $u['login']];
    return true;
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_pole(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function sprawdz_csrf(): void
{
    if (!hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Sesja wygasła – odśwież stronę i spróbuj ponownie.');
    }
    // Tekst w innym kodowaniu niż UTF-8 zniknąłby przy wyświetlaniu – odrzucamy od razu.
    array_walk_recursive($_POST, function ($v) {
        if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) {
            http_response_code(400);
            exit('Niepoprawne znaki w formularzu – wpisz tekst ponownie.');
        }
    });
}

// Komunikat pokazywany jednorazowo po przekierowaniu.
function flash(?string $tekst = null, string $typ = 'ok'): ?array
{
    if ($tekst !== null) { $_SESSION['flash'] = [$typ, $tekst]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function przekieruj(string $url): void // PHP 8.0 w XAMPP – bez typu "never"
{
    header("Location: $url");
    exit;
}
