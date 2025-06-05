-- Réinitialisation du mot de passe de l'admin : password : password
UPDATE utilisateurs 
SET mot_de_passe = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@example.com'; 