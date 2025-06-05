<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (estConnecte()) {
    rediriger('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = nettoyer($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';

    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (empty($captcha)) $errors[] = "Le code CAPTCHA est requis";
    
    if (!empty($captcha) && !verifierCaptcha($captcha)) {
        $errors[] = "Code CAPTCHA incorrect";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT u.*, r.nom as role_nom FROM utilisateurs u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role_nom'];

                // Mettre à jour la dernière connexion
                $stmt = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                rediriger('index.php', 'Connexion réussie !');
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }
}

$page_title = "Connexion";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Connexion</h2>
                </div>
                <div class="card-body">
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
                            <label for="email" class="form-label">Email :</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe :</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="captcha" class="form-label">Code de sécurité :</label>
                            <div class="d-flex flex-column gap-2">
                                <img src="includes/captcha.php" alt="CAPTCHA" class="border rounded" style="height: 60px; width: 200px; object-fit: contain;">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" class="form-control" id="captcha" name="captcha" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.previousElementSibling.previousElementSibling.src='includes/captcha.php?'+Math.random()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">Entrez le code affiché dans l'image</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <p>Pas encore de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 