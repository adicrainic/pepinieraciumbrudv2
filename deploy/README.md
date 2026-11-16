# Ghid Complet de Build & Deploy — Pepiniera Ciumbrud V2

Acest director conține configurațiile și scripturile necesare pentru lansarea și actualizarea automată a aplicației în producție pe un VPS (Ubuntu 22.04 / 24.04).

---

## 🏗️ Arhitectura Producție

| Componentă | Tehnologie | Domeniu / URL | Servire |
| :--- | :--- | :--- | :--- |
| **Storefront** | Nuxt 3 (SSR) | `https://pepinieraciumbrud.ro` | Node.js (PM2 port 3000) prin Nginx Reverse Proxy |
| **Admin Panel** | Vue 3 (Vite SPA) | `https://admin.pepinieraciumbrud.ro` | Fișiere statice (`dist/`) servite direct de Nginx |
| **Backend API** | WordPress + WooCommerce | `https://api.pepinieraciumbrud.ro` | PHP 8.2-FPM + Nginx |

---

## 📋 Pasul 1: Pregătirea Serverului (O singură dată)

Pe serverul proaspăt instalat (Ubuntu):

```bash
# 1. Clonează repository-ul în /var/www/
sudo git clone git@github.com:adicrainic/pepinieraciumbrudv2.git /var/www/pepinieraciumbrudV2
cd /var/www/pepinieraciumbrudV2

# 2. Rulează scriptul de provizionare pachete (Nginx, MySQL, PHP 8.2, Composer, Node 20, PM2, Certbot, WP-CLI)
sudo bash deploy/setup-server.sh
```

---

## 🗄️ Pasul 2: Baza de Date & WordPress

1. **Creează baza de date MySQL pe server**:
   ```sql
   CREATE DATABASE pepinieraciumbrudshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'pepinieraciumbrudshop'@'localhost' IDENTIFIED BY 'parola_secreta_aici';
   GRANT ALL PRIVILEGES ON pepinieraciumbrudshop.* TO 'pepinieraciumbrudshop'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Importă dump-ul SQL inițial**:
   ```bash
   mysql -u pepinieraciumbrudshop -p pepinieraciumbrudshop < backup_local.sql
   ```

3. **Creează fișierul `wp-config.php`**:
   ```bash
   cp pepinieraciumbrudshop/wp-config.example.php pepinieraciumbrudshop/wp-config.php
   nano pepinieraciumbrudshop/wp-config.php
   ```
   Completează datele DB și cheile:
   ```php
   define( 'DB_NAME', 'pepinieraciumbrudshop' );
   define( 'DB_USER', 'pepinieraciumbrudshop' );
   define( 'DB_PASSWORD', 'parola_secreta_aici' );

   // Chei WooCommerce REST API (Generate în WP Admin -> WooCommerce -> Setări -> Avansat -> REST API)
   define( 'PCS_WC_CONSUMER_KEY',    'ck_...' );
   define( 'PCS_WC_CONSUMER_SECRET', 'cs_...' );
   define( 'PCS_WC_API_URL',         'https://api.pepinieraciumbrud.ro/wp-json/wc/v3' );
   define( 'PCS_JWT_SECRET',         'sir_lung_aleatoriu_pentru_jwt' );
   define( 'PCS_STOREFRONT_ORIGIN',  'https://pepinieraciumbrud.ro' );
   define( 'PCS_ADMIN_ORIGIN',       'https://admin.pepinieraciumbrud.ro' );
   ```

4. **Actualizează URL-urile vechi (Localhost -> Producție)**:
   ```bash
   wp search-replace "http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop" "https://api.pepinieraciumbrud.ro" --path=/var/www/pepinieraciumbrudV2/pepinieraciumbrudshop --all-tables
   ```

5. **Permisiuni folder uploads**:
   ```bash
   sudo chown -R www-data:www-data /var/www/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-content/uploads/
   sudo chmod -R 775 /var/www/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-content/uploads/
   ```

---

## 🧬 Migrații Bază de Date (Phinx)

Proiectul folosește **Phinx** pentru a versiona orice modificare de schemă a bazei de date. Astfel, după lansare în producție, modificările de tabele sau coloane se fac strict prin migrații, fără downtime și fără riscul de pierdere a comenzilor clienților.

- **Cum creezi o migrație nouă în development**:
  ```bash
  cd pepinieraciumbrudshop
  vendor/bin/phinx create NumeMigratieNoua -c database/phinx.php
  ```
- **Cum verifici statusul migrațiilor**:
  ```bash
  cd pepinieraciumbrudshop
  composer status
  # sau: vendor/bin/phinx status -c database/phinx.php
  ```
- **Cum rulezi manual migrațiile**:
  ```bash
  cd pepinieraciumbrudshop
  composer migrate
  # sau: vendor/bin/phinx migrate -c database/phinx.php
  ```
- **Cum dai rollback (în caz de nevoie)**:
  ```bash
  cd pepinieraciumbrudshop
  composer rollback
  ```

*(La fiecare rulare a scriptului `./deploy/deploy.sh`, migrațiile noi se aplică automat!)*

---

## 🌐 Pasul 3: Configurare Nginx & Certificate SSL (Let's Encrypt)

1. **Copiază configurațiile Nginx**:
   ```bash
   sudo cp deploy/nginx/storefront.conf /etc/nginx/sites-available/pepinieraciumbrud-storefront.conf
   sudo cp deploy/nginx/admin.conf /etc/nginx/sites-available/pepinieraciumbrud-admin.conf
   sudo cp deploy/nginx/api.conf /etc/nginx/sites-available/pepinieraciumbrud-api.conf

   # Activează site-urile
   sudo ln -s /etc/nginx/sites-available/pepinieraciumbrud-storefront.conf /etc/nginx/sites-enabled/
   sudo ln -s /etc/nginx/sites-available/pepinieraciumbrud-admin.conf /etc/nginx/sites-enabled/
   sudo ln -s /etc/nginx/sites-available/pepinieraciumbrud-api.conf /etc/nginx/sites-enabled/
   ```

2. **Obține certificatele SSL automate**:
   ```bash
   sudo certbot --nginx -d pepinieraciumbrud.ro -d www.pepinieraciumbrud.ro
   sudo certbot --nginx -d admin.pepinieraciumbrud.ro
   sudo certbot --nginx -d api.pepinieraciumbrud.ro
   ```

3. **Verifică și reîncarcă Nginx**:
   ```bash
   sudo nginx -t
   sudo systemctl reload nginx
   ```

---

## ⚙️ Pasul 4: Configurare `.env` de Producție

### Storefront (`pepinieraciumbrud-storefront/.env`)
```bash
cp pepinieraciumbrud-storefront/.env.production.example pepinieraciumbrud-storefront/.env
```
Conținutul va fi:
```env
NUXT_PUBLIC_API_BASE=https://api.pepinieraciumbrud.ro/wp-json/wc/store/v1
NUXT_PUBLIC_WP_BASE=https://api.pepinieraciumbrud.ro/wp-json
NUXT_PUBLIC_SITE_URL=https://pepinieraciumbrud.ro
PORT=3000
NODE_ENV=production
```

### Admin (`pepinieraciumbrud-admin/.env`)
```bash
cp pepinieraciumbrud-admin/.env.production.example pepinieraciumbrud-admin/.env
```
Conținutul va fi:
```env
VITE_API_BASE=https://api.pepinieraciumbrud.ro/wp-json/pcs/v1
VITE_WP_BASE=https://api.pepinieraciumbrud.ro/wp-json
```

---

## 🚀 Pasul 5: Primul Build & Pornire

Rulează scriptul de deploy:
```bash
cd /var/www/pepinieraciumbrudV2
./deploy/deploy.sh
```

Activează pornirea automată a PM2 la repornirea serverului:
```bash
pm2 startup
# (Rulează comanda pe care ți-o afișează PM2 pe ecran)
pm2 save
```

---

## 🔁 Actualizări Ulterioare (Continuous Deployment)

De fiecare dată când faci `git push` de pe calculatorul local, pe server este suficient să rulezi:

```bash
cd /var/www/pepinieraciumbrudV2
./deploy/deploy.sh
```

Scriptul:
1. Trage ultimele modificări (`git pull`)
2. Compilează Storefront-ul Nuxt 3 și reîncarcă procesul în PM2 fără downtime
3. Compilează Admin Panel-ul Vue 3 (`dist/`)
4. Asigură permisiunile corecte pentru WordPress și curăță cache-ul PHP-FPM.

