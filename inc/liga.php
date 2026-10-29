<?php
// Tabela i terminarz pobierane z 90minut.pl, buforowane w cache/liga.json.
// Gdy źródło nie odpowiada, używamy ostatniej kopii lub data/liga-snapshot.json.
require_once __DIR__ . '/config.php';

const CACHE_FILE    = __DIR__ . '/../cache/liga.json';
const SNAPSHOT_FILE = __DIR__ . '/../data/liga-snapshot.json';

// Krótsze nazwy do wyświetlania (90minut dopisuje miasto w nawiasie).
const SKROTY = [
    'Siemianowiczanka Siemianowice Śląskie' => 'Siemianowiczanka',
];

function liga_dane(bool $wymus = false): array
{
    $cache = is_file(CACHE_FILE) ? json_decode(file_get_contents(CACHE_FILE), true) : null;
    if (!$wymus && $cache && time() - $cache['pobrano'] < LIGA_CACHE_TTL) {
        return $cache;
    }

    $html = liga_pobierz(LIGA_URL);
    $dane = $html ? liga_parsuj($html) : null;
    if ($dane && count($dane['tabela']) > 0) {
        $dane['pobrano'] = time();
        $dane['zrodlo']  = LIGA_URL;
        @mkdir(dirname(CACHE_FILE), 0775, true);
        file_put_contents(CACHE_FILE, json_encode($dane, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $dane;
    }

    if ($cache) return $cache;
    return json_decode(file_get_contents(SNAPSHOT_FILE), true);
}

function liga_pobierz(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (KS Unia Zabkowice - strona klubu)',
    ]);
    $html = curl_exec($ch);
    $ok = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);
    if (!$ok || !$html) return null;
    return iconv('ISO-8859-2', 'UTF-8//IGNORE', $html);
}

function liga_nazwa(string $n): string
{
    $n = trim(preg_replace('/\s*\(.*\)\s*$/u', '', html_entity_decode(trim($n), ENT_QUOTES, 'UTF-8')), " \xC2\xA0");
    return SKROTY[$n] ?? $n;
}

function liga_slug(string $n): string
{
    $n = mb_strtolower($n, 'UTF-8');
    $n = strtr($n, ['ą'=>'a','ć'=>'c','ę'=>'e','ł'=>'l','ń'=>'n','ó'=>'o','ś'=>'s','ź'=>'z','ż'=>'z']);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $n), '-');
}

function liga_parsuj(string $html): ?array
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $html = preg_replace('/<meta[^>]+charset[^>]*>/i', '', $html); // inaczej libxml czyta to jako ISO-8859-2
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $xp = new DOMXPath($doc);

    // Tabela: wiersze z pozycją "N." i linkiem do klubu.
    $tabela = [];
    foreach ($xp->query("//tr[td[2]/a[@class='main']]") as $tr) {
        $td = $tr->getElementsByTagName('td');
        if ($td->length < 16 || !preg_match('/^(\d+)\.$/', trim($td[0]->textContent), $m)) continue;
        $poz = (int)$m[1];
        if (isset($tabela[$poz])) break;
        [$bz, $bs] = array_map('intval', explode('-', trim($td[7]->textContent)) + [0, 0]);
        $nazwa = liga_nazwa($td[1]->textContent);
        $tabela[$poz] = [
            'poz' => $poz, 'nazwa' => $nazwa, 'slug' => liga_slug($nazwa),
            'm' => (int)$td[2]->textContent, 'pkt' => (int)$td[3]->textContent,
            'w' => (int)$td[4]->textContent, 'r' => (int)$td[5]->textContent, 'p' => (int)$td[6]->textContent,
            'bz' => $bz, 'bs' => $bs,
        ];
    }

    // Sezon z tytułu, np. "Klasa okręgowa 2026/2027" -> rok startu 2026.
    preg_match('/(20\d\d)\/20\d\d/', $xp->query('//title')->item(0)->textContent ?? '', $sm);
    $rokStartu = (int)($sm[1] ?? date('Y'));

    // Terminarz: nagłówki "Kolejka N" i wiersze meczów (4 komórki, pierwsza szer. 180).
    $mecze = [];
    $kolejka = 0;
    foreach ($xp->query('//tr') as $tr) {
        $u = $xp->query('.//u', $tr);
        if ($u->length && preg_match('/Kolejka (\d+)/u', $u->item(0)->textContent, $km)) {
            $kolejka = (int)$km[1];
            continue;
        }
        $td = $xp->query('./td', $tr);
        if (!$kolejka || $td->length !== 4 || $td[0]->getAttribute('width') !== '180') continue;
        $wynik = trim(str_replace("\xC2\xA0", ' ', $td[1]->textContent));
        $gosp = liga_nazwa($td[0]->textContent);
        $gosc = liga_nazwa($td[2]->textContent);
        $mecze[] = [
            'kolejka' => $kolejka,
            'gosp' => $gosp, 'gosp_slug' => liga_slug($gosp),
            'gosc' => $gosc, 'gosc_slug' => liga_slug($gosc),
            'wynik' => preg_match('/^(\d+)-(\d+)$/', $wynik, $wm) ? [(int)$wm[1], (int)$wm[2]] : null,
            'data' => liga_data(trim($td[3]->textContent), $rokStartu),
        ];
    }

    return ['tabela' => array_values($tabela), 'mecze' => $mecze];
}

// "31 października, 14:00" -> "2026-10-31T14:00"
function liga_data(string $s, int $rokStartu): ?string
{
    $mies = ['stycznia'=>1,'lutego'=>2,'marca'=>3,'kwietnia'=>4,'maja'=>5,'czerwca'=>6,'lipca'=>7,
             'sierpnia'=>8,'września'=>9,'października'=>10,'listopada'=>11,'grudnia'=>12];
    if (!preg_match('/(\d{1,2})\s+(\p{L}+)(?:,\s*(\d{1,2}):(\d{2}))?/u', $s, $m) || !isset($mies[$m[2]])) return null;
    $mc = $mies[$m[2]];
    $rok = $mc >= 7 ? $rokStartu : $rokStartu + 1;
    return sprintf('%04d-%02d-%02dT%s', $rok, $mc, $m[1], isset($m[3]) ? sprintf('%02d:%s', $m[3], $m[4]) : '00:00');
}

// ---- Pomocnicze widoki na dane ----

function liga_nasze_mecze(array $dane): array
{
    return array_values(array_filter($dane['mecze'], fn($m) => $m['gosp'] === NASZ_KLUB || $m['gosc'] === NASZ_KLUB));
}

function liga_rozegrane(array $mecze): array
{
    $r = array_values(array_filter($mecze, fn($m) => $m['wynik'] !== null));
    usort($r, fn($a, $b) => strcmp($a['data'] ?? '', $b['data'] ?? ''));
    return $r;
}

function liga_nadchodzace(array $mecze): array
{
    // Najpierw mecze z ustaloną datą, potem bez daty wg kolejki.
    // Brak daty w kolejce, która już minęła = mecz przełożony.
    $ostatniaRozegrana = max(array_merge([0], array_map(fn($m) => $m['kolejka'], liga_rozegrane($mecze))));
    $r = [];
    foreach ($mecze as $m) {
        if ($m['wynik'] !== null) continue;
        $m['przelozony'] = !$m['data'] && $m['kolejka'] < $ostatniaRozegrana;
        $r[] = $m;
    }
    usort($r, fn($a, $b) => [$a['data'] ? 0 : 1, $a['data'] ?? '', $a['kolejka']] <=> [$b['data'] ? 0 : 1, $b['data'] ?? '', $b['kolejka']]);
    return $r;
}

// 'W' / 'R' / 'P' z perspektywy naszego klubu
function liga_rezultat(array $m): string
{
    [$g, $a] = $m['wynik'];
    $my = $m['gosp'] === NASZ_KLUB ? $g - $a : $a - $g;
    return $my > 0 ? 'W' : ($my < 0 ? 'P' : 'R');
}

function liga_pozycja(array $dane): ?array
{
    foreach ($dane['tabela'] as $t) if ($t['nazwa'] === NASZ_KLUB) return $t;
    return null;
}

function herb(string $slug, string $nazwa, string $klasa = 'herb'): string
{
    // SVG/PNG z przezroczystym tłem; JPG tylko jako ostatnia deska ratunku
    $plik = null;
    foreach (['svg', 'png', 'jpg'] as $ext) {
        if (is_file(__DIR__ . "/../assets/img/herby/$slug.$ext")) { $plik = "assets/img/herby/$slug.$ext"; break; }
    }
    if ($plik) {
        return '<img class="' . $klasa . '" src="' . $plik . '" alt="' . e($nazwa) . '" loading="lazy" width="100" height="100">';
    }
    $inicjaly = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', $nazwa), 0, 2)));
    return '<span class="' . $klasa . ' herb--brak" aria-label="' . e($nazwa) . '">' . e(mb_strtoupper($inicjaly)) . '</span>';
}

const DNI = ['niedziela','poniedziałek','wtorek','środa','czwartek','piątek','sobota'];
const MIESIACE = [1=>'stycznia','lutego','marca','kwietnia','maja','czerwca','lipca','sierpnia','września','października','listopada','grudnia'];

function data_pl(?string $iso, bool $zDniem = true): string
{
    if (!$iso) return 'termin do ustalenia';
    $t = strtotime($iso);
    $s = date('j', $t) . ' ' . MIESIACE[(int)date('n', $t)];
    if ($zDniem) $s = DNI[(int)date('w', $t)] . ', ' . $s;
    if (date('H:i', $t) !== '00:00') $s .= ', ' . date('H:i', $t);
    return $s;
}
