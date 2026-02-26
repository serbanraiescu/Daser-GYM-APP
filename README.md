# Daser GYM APP

Sistem de management pentru săli de fitness, bazat pe Laravel 12 și Filament 3.

## Cerințe Sistem
- PHP 8.2+
- MySQL 8.0+ sau SQLite
- Composer 2.x

## Instalare Locală
1. Clonează repository-ul.
2. Instalează dependențele: `composer install`.
3. Copiază `.env.example` în `.env`.
4. Generează cheia aplicației: `php artisan key:generate`.
5. Configurează baza de date în `.env`.
6. Rulează migrațiile și seeder-ele: `php artisan migrate --seed`.
7. Rulează serverul: `php artisan serve`.

## Deployment pe cPanel
1. Urcă fișierele pe server (recomandat via Git sau Zip fără `vendor`).
2. Pentru instalări unde `index.php` trebuie să fie în root (nu în `/public`), folosește configurația din `index-root.php` redenumit în `index.php`.
3. Asigură-te că linkul simbolic către storage este creat: `php artisan storage:link`.
4. Setează permisiuni de scriere (755/775) pe folderele `storage/` și `bootstrap/cache/`.
5. Configurează `.env` cu datele de producție.

## Mentenanță & Update
- Actualizare cod: `git pull origin main`.
- Actualizare dependențe: `composer install --no-dev`.
- Migrare DB: `php artisan migrate --force`.
- Curățare cache: `php artisan optimize:clear`.

## Audit & Structură
Vezi raportul complet în `audit_report.md` (dacă este disponibil în root) sau contactează dezvoltatorul principal.
