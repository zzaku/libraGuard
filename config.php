<?php

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

define('SITE_NAME', 'LibraGuard');
define('SITE_URL', 'http://localhost/libraGuard');

define('DB_HOST', 'localhost');
define('DB_NAME', 'libraguard');
define('DB_USER', 'root');
define('DB_PASS', '');

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Europe/Paris');

$host = '127.0.0.1:3306';
$dbname = 'LibraGuard';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}