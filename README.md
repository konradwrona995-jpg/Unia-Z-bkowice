# KS Unia Ząbkowice – strona klubu (prototyp)

PHP 8 + SQLite, bez frameworków. Działa na XAMPP i na zwykłym hostingu z PHP.

## Uruchomienie lokalnie
Włącz Apache w XAMPP i otwórz `http://localhost/strona/`.

## Panel klubu
`/admin/` – przy pierwszym wejściu panel prosi o założenie konta administratora.
**Na serwerze publicznym zrób to od razu po wgraniu strony** – do tego czasu konto może założyć każdy.

W panelu:
- **Aktualności** – tytuł, wstęp, treść (pusta linia = nowy akapit), kategoria, zdjęcie, szkic/publikacja.
- **Kadra** – imię, nazwisko, numer, pozycja, rocznik, kapitan, zdjęcie (przycinane do 4:5).
- **Konto** – zmiana hasła, dodawanie i usuwanie osób z dostępem.

## Gdzie co jest
| Ścieżka | Co |
|---|---|
| `inc/config.php` | dane klubu (adres, telefon, social media, adres tabeli 90minut) |
| `inc/liga.php` | pobieranie tabeli i terminarza z 90minut.pl (bufor 15 min w `cache/`) |
| `inc/db.php` | baza `data/unia.sqlite` (tworzy się sama), zdjęcia w `uploads/` |
| `data/aktualnosci.php` | wpisy startowe, wczytywane do nowej bazy |

Nowy sezon: zmień `LIGA_URL` i `sezon` w `inc/config.php`.

Kopia zapasowa = plik `data/unia.sqlite` + folder `uploads/`.
