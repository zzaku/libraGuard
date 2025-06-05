<?php
require_once 'config.php';
require_once 'includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = nettoyer($_POST['nom'] ?? '');
    $prenom = nettoyer($_POST['prenom'] ?? '');
    $email = nettoyer($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide";
    if (emailExiste($pdo, $email)) $errors[] = "Cet email est déjà utilisé";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    
    $verification = verifierForceMotDePasse($password);
    if (!$verification['valide']) {
        $errors[] = $verification['message'];
    }
    
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_id = getRoleId($pdo, 'utilisateur');
            
            if (!$role_id) {
                throw new Exception("Rôle utilisateur non trouvé");
            }

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hashed_password, $role_id]);
            
            rediriger('login.php', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - LibraGuard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Inscription</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">
                    Le mot de passe doit contenir :
                    <ul>
                        <li>Au moins 8 caractères</li>
                        <li>Au moins une lettre majuscule</li>
                        <li>Au moins une lettre minuscule</li>
                        <li>Au moins un chiffre</li>
                        <li>Au moins un caractère spécial (!@#$%^&*()-_=+{};:,<.>)</li>
                    </ul>
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe :</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="mb-3">
                <label for="captcha" class="form-label">Code de sécurité :</label>
                <div class="d-flex flex-column gap-2">
                    <img src="includes/captcha.php" alt="CAPTCHA" class="border rounded" style="height: 60px; width: 200px; object-fit: contain;" id="captchaImage">
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control" id="captcha" name="captcha" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('captchaImage').src='includes/captcha.php?'+Math.random()">
                            Rafraîchir
                        </button>
                    </div>
                </div>
                <div class="form-text">Entrez le code affiché dans l'image</div>
            </div>

            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>

        <p class="mt-3">Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 