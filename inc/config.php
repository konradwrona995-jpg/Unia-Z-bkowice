<?php
// Podstawowe dane klubu – jedno miejsce do edycji.
date_default_timezone_set('Europe/Warsaw');

$KLUB = [
    'nazwa'       => 'KS Unia Ząbkowice',
    'pelna_nazwa' => 'Klub Sportowy „Unia” Ząbkowice w Dąbrowie Górniczej',
    'rok'         => 1922,
    'barwy'       => 'biało-niebieskie',
    'adres'       => 'Aleja Zwycięstwa 29, 42-520 Dąbrowa Górnicza',
    'telefon'     => '32 264 08 27',
    'email'       => 'kontakt@uniazabkowice.pl', // do potwierdzenia z klubem
    'krs'         => '0000113935',
    'prezes'      => 'Jarosław Zagajski',
    'trener'      => 'Krystian Białkowski',
    'liga'        => 'Klasa okręgowa, grupa śląska IV (Katowice–Sosnowiec)',
    'sezon'       => '2026/27',
    'stadion'     => [
        'nazwa'      => 'Stadion Sportowy Ząbkowice',
        'pojemnosc'  => '1 200 miejsc (100 siedzących)',
        'boisko'     => '101 × 64 m',
        'otwarcie'   => 1974,
        'lat'        => 50.3853,
        'lon'        => 19.2430,
    ],
    'social' => [
        'facebook'  => 'https://www.facebook.com/UniaZabkowice/',
        'instagram' => 'https://www.instagram.com/uniazabkowice1922/',
        'akademia'  => 'https://www.facebook.com/p/Akademia-KS-Unia-Z%C4%85bkowice-61579629253238/',
    ],
];

// Źródło tabeli i terminarza (90minut.pl, grupa śląska IV, sezon 2026/27).
const LIGA_URL       = 'http://www.90minut.pl/liga/1/liga14959.html';
const LIGA_CACHE_TTL = 15 * 60; // sekundy
const NASZ_KLUB      = 'Unia Ząbkowice';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
