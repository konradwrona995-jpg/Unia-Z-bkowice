<?php
// Baza SQLite (data/unia.sqlite) – aktualności, kadra, konta administratorów.
// Tworzy się sama przy pierwszym użyciu; nie wymaga konfiguracji MySQL.
require_once __DIR__ . '/config.php';

const DB_FILE     = __DIR__ . '/../data/unia.sqlite';
const UPLOADS_DIR = __DIR__ . '/../uploads';

const POZYCJE = ['Bramkarze' => 'Bramkarz', 'Obrońcy' => 'Obrońca', 'Pomocnicy' => 'Pomocnik', 'Napastnicy' => 'Napastnik'];
const KATEGORIE = ['Relacja', 'Zapowiedź', 'Klub', 'Akademia', 'Puchar Polski', 'Kibice'];

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $nowa = !is_file(DB_FILE);
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($nowa) {
        $pdo->exec("
            CREATE TABLE uzytkownicy (
                id INTEGER PRIMARY KEY,
                login TEXT NOT NULL UNIQUE,
                haslo TEXT NOT NULL,
                utworzono TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE aktualnosci (
                id INTEGER PRIMARY KEY,
                tytul TEXT NOT NULL,
                data TEXT NOT NULL,
                kat TEXT NOT NULL,
                lead TEXT NOT NULL DEFAULT '',
                tresc TEXT NOT NULL DEFAULT '',
                zdjecie TEXT,
                opublikowany INTEGER NOT NULL DEFAULT 1,
                utworzono TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE zawodnicy (
                id INTEGER PRIMARY KEY,
                imie TEXT NOT NULL,
                nazwisko TEXT NOT NULL,
                numer INTEGER,
                pozycja TEXT NOT NULL,
                rocznik INTEGER,
                zdjecie TEXT,
                kapitan INTEGER NOT NULL DEFAULT 0
            );
        ");
        // Startowe wpisy z prototypu, żeby strona nie była pusta.
        $ins = $pdo->prepare('INSERT INTO aktualnosci (tytul, data, kat, lead) VALUES (?, ?, ?, ?)');
        foreach (require __DIR__ . '/../data/aktualnosci.php' as $n) {
            $ins->execute([$n['tytul'], $n['data'], $n['kat'], $n['lead']]);
        }
    }
    return $pdo;
}

// ---- Odczyt dla strony publicznej ----

function news_lista(int $limit = 0, bool $tylkoOpublikowane = true): array
{
    $sql = 'SELECT * FROM aktualnosci' . ($tylkoOpublikowane ? ' WHERE opublikowany = 1' : '')
         . ' ORDER BY data DESC, id DESC' . ($limit ? ' LIMIT ' . (int)$limit : '');
    return db()->query($sql)->fetchAll();
}

function news_jeden(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM aktualnosci WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// Zawodnicy pogrupowani wg POZYCJE, posortowani po numerze.
function kadra(): array
{
    $grupy = array_fill_keys(array_keys(POZYCJE), []);
    $wszyscy = db()->query('SELECT * FROM zawodnicy ORDER BY numer IS NULL, numer, nazwisko')->fetchAll();
    foreach ($wszyscy as $z) {
        $grupa = array_search($z['pozycja'], POZYCJE, true);
        if ($grupa !== false) $grupy[$grupa][] = $z;
    }
    return $grupy;
}

// Puste linie dzielą akapity; reszta to zwykły tekst (bez HTML od użytkownika).
function akapity(string $tekst): string
{
    $out = '';
    foreach (preg_split("/\R{2,}/", trim($tekst)) as $p) {
        if ($p !== '') $out .= '<p>' . nl2br(e($p)) . '</p>';
    }
    return $out;
}

// ---- Zdjęcia: walidacja przez GD, zmiana rozmiaru, zapis jako JPEG ----

function zapisz_zdjecie(array $plik, string $folder, int $maxSzer, ?int $proporcja = null): string
{
    if ($plik['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Nie udało się wgrać pliku.');
    if ($plik['size'] > 15 * 1024 * 1024) throw new RuntimeException('Zdjęcie jest za duże (max 15 MB).');

    $typ = (new finfo(FILEINFO_MIME_TYPE))->file($plik['tmp_name']);
    $src = match ($typ) {
        'image/jpeg' => @imagecreatefromjpeg($plik['tmp_name']),
        'image/png'  => @imagecreatefrompng($plik['tmp_name']),
        'image/webp' => @imagecreatefromwebp($plik['tmp_name']),
        default      => false,
    };
    if (!$src) throw new RuntimeException('Dozwolone formaty: JPG, PNG, WEBP.');

    if ($typ === 'image/jpeg' && function_exists('exif_read_data')) {
        $obrot = [3 => 180, 6 => -90, 8 => 90][@exif_read_data($plik['tmp_name'])['Orientation'] ?? 0] ?? 0;
        if ($obrot) $src = imagerotate($src, $obrot, 0);
    }

    $w = imagesx($src); $h = imagesy($src);
    $sx = 0; $sy = 0; $sw = $w; $sh = $h;
    if ($proporcja) { // przycięcie do proporcji (np. 4:5 dla zdjęć zawodników), od góry kadru
        $docelowaH = (int)round($w * $proporcja / 100);
        if ($docelowaH <= $h) { $sh = $docelowaH; }
        else { $sw = (int)round($h * 100 / $proporcja); $sx = (int)(($w - $sw) / 2); }
    }
    $nw = min($maxSzer, $sw);
    $nh = (int)round($sh * $nw / $sw);
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $nw, $nh, $sw, $sh);

    $dir = UPLOADS_DIR . '/' . $folder;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $nazwa = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.jpg';
    imagejpeg($dst, "$dir/$nazwa", 85);
    imagedestroy($src); imagedestroy($dst);
    return "uploads/$folder/$nazwa";
}

function usun_zdjecie(?string $sciezka): void
{
    if ($sciezka && str_starts_with($sciezka, 'uploads/') && !str_contains($sciezka, '..')) {
        @unlink(__DIR__ . '/../' . $sciezka);
    }
}
