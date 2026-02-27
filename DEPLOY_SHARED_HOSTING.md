# Ghid de Deployment Shared Hosting (Zero-Terminal)

Acest document explică cum se face deployment-ul aplicației Daser GYM APP pe un hosting shared care nu oferă acces la SSH, Terminal, Composer sau Node.

## Strategia de Deployment
Aplicația a fost configurată special pentru acest tip de hosting:
- **Folder-ul `vendor/` este inclus în Git**: Toate dependențele PHP sunt deja pre-instalate și optimizate local.
- **Folder-ul `public/build/` este inclus în Git**: Toate asset-urile (JS/CSS) sunt deja compilate în varianta de producție.
- **Fără Comenzi pe Server**: Nu este necesar să rulați `composer install` sau `npm run build` pe server.

## Structura Recordată pe Server (cPanel)
Pentru o securitate optimă, structura trebuie să fie:
```text
/home/utilizator/
    ├── daser_gym_app/ (Tot conținutul repository-ului)
    └── public_html/ (Doar conținutul din folderul public al proiectului)
```

### Configurare `index.php` (în `public_html/`)
Fișierul `index.php` este deja pre-configurat să caute aplicația în `../daser_gym_app`:
```php
$APP_DIR = __DIR__ . '/../daser_gym_app';
require $APP_DIR.'/vendor/autoload.php';
$app = require_once $APP_DIR.'/bootstrap/app.php';
```

## Pași de Deployment via Git (cPanel)
1.  **Cloneaza Repo**: Folosiți „Git Version Control” din cPanel pentru a clona repository-ul în `/home/utilizator/daser_gym_app`.
2.  **Deploy**: Apăsați butonul „Deploy” din interfața cPanel. Fișierul `.cpanel.yml` va copia automat totul din `public/` în `public_html/`.
3.  **Fișier .env**: Creați manual fișierul `.env` în `/home/utilizator/daser_gym_app/` și adăugați datele bazei de date.
4.  **Permisiuni**: Asigurați-vă că următoarele directoare au permisiuni de scriere (755 sau 775):
    - `storage/`
    - `bootstrap/cache/`

## Update-uri viitoare
Pentru a actualiza aplicația, doar rulați **Git Pull** din interfața cPanel. Toate modificările de cod și dependențele se vor actualiza automat deoarece sunt incluse în repository.

---
**Notă**: Înainte de fiecare `git push` local, dezvoltatorul a rulat:
- `composer install --no-dev --optimize-autoloader`
- `npm run build`
- `php artisan optimize:clear`
