<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$host = 'Adresse de la base de donnée';
$dbname = 'Nom de la base de donnée';
$username = 'Nom de l\'utilisateur';
$password = 'mot de passe utilisateur';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}