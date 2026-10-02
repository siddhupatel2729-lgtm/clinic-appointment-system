<?php
// ---- Edit these for your MySQL setup (XAMPP/WAMP default shown) ----
const DB_HOST = 'localhost';
const DB_NAME = 'clinic_appointment';
const DB_USER = 'root';
const DB_PASS = '';

session_start();
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed. Import database.sql and check config.php. (" . htmlspecialchars($e->getMessage()) . ")");
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($u) { header("Location: $u"); exit; }
function flash($msg = null, $type = 'success') {
    if ($msg) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (isset($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}
function valid_phone($p) { return $p === '' || preg_match('/^[0-9+\-\s]{7,15}$/', $p); }
function valid_email($m) { return $m === '' || filter_var($m, FILTER_VALIDATE_EMAIL); }
