# Clinic Appointment System (HTML, CSS, JS, PHP, MySQL)

## Run
1. Install XAMPP/WAMP (PHP 8.1+, MySQL). Start Apache and MySQL.
2. Import `database.sql` (phpMyAdmin > Import, or `mysql -u root < database.sql`).
3. Edit DB credentials in `config.php` if needed.
4. Option A: copy this folder to `htdocs/` and open http://localhost/clinic-appointment/
   Option B (VS Code terminal): `php -S localhost:8000` inside this folder, then open http://localhost:8000
   (needs the PHP `pdo_mysql` extension enabled).
