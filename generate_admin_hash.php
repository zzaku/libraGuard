<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash pour le mot de passe 'admin123': " . $hash;
?> 