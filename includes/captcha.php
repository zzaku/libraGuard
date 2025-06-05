<?php
session_start();

// Vérifier si l'extension GD est chargée
if (!extension_loaded('gd')) {
    die('L\'extension GD n\'est pas installée. Veuillez l\'activer dans votre configuration PHP.');
}

// Générer une image de 200x80 pixels
$image = imagecreatetruecolor(200, 80);

// Définir les couleurs
$bg = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$noise_color = imagecolorallocate($image, 100, 120, 180);

// Remplir le fond
imagefilledrectangle($image, 0, 0, 200, 80, $bg);

// Ajouter du bruit
for($i = 0; $i < 1000; $i++) {
    $x = rand(0, 200);
    $y = rand(0, 80);
    imagesetpixel($image, $x, $y, $noise_color);
}

// Générer un code aléatoire de 6 caractères
$code = '';
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
for($i = 0; $i < 6; $i++) {
    $code .= $chars[rand(0, strlen($chars) - 1)];
}

// Stocker le code dans la session
$_SESSION['captcha'] = $code;

// Ajouter le texte à l'image
$x = 20;
$y = 50;

// Dessiner chaque caractère
for($i = 0; $i < strlen($code); $i++) {
    imagestring($image, 5, $x + ($i * 25), $y - 20, $code[$i], $text_color);
}

// Ajouter quelques lignes pour plus de sécurité
for($i = 0; $i < 5; $i++) {
    $x1 = rand(0, 200);
    $y1 = rand(0, 80);
    $x2 = rand(0, 200);
    $y2 = rand(0, 80);
    imageline($image, $x1, $y1, $x2, $y2, $noise_color);
}

// Envoyer l'en-tête pour indiquer que c'est une image
header('Content-Type: image/png');

// Afficher l'image
imagepng($image);
imagedestroy($image); 