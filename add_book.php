<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('includes/functions.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!estConnecte() || !estAdmin()) {
    rediriger('login.php', 'Vous devez être administrateur pour accéder à cette page.', 'error');
}

$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du CAPTCHA
    if (!isset($_POST['captcha']) || !verifierCaptcha($_POST['captcha'])) {
        $message = 'Le CAPTCHA est incorrect.';
        $type_message = 'error';
    } else {
        $titre = nettoyer($_POST['titre']);
        $auteur = nettoyer($_POST['auteur']);
        $isbn = nettoyer($_POST['isbn']);
        $date_publication = nettoyer($_POST['date_publication']);
        $description = nettoyer($_POST['description']);
        $statut = nettoyer($_POST['statut']);

        if (empty($titre) || empty($auteur) || empty($isbn)) {
            $message = 'Tous les champs obligatoires doivent être remplis.';
            $type_message = 'error';
        } else {
            try {
                $photo_url = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $photo_url = uploadImage($_FILES['photo']);
                }

                if (ajouterLivre($pdo, $titre, $auteur, $isbn, $date_publication, $description, $statut, $photo_url)) {
                    $message = 'Le livre a été ajouté avec succès.';
                    $type_message = 'success';
                } else {
                    $message = 'Erreur lors de l\'ajout du livre.';
                    $type_message = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Erreur lors de l\'ajout du livre.';
                $type_message = 'error';
            }
        }
    }
}

$page_title = "Ajouter un Livre";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Ajouter un Livre</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $type_message === 'error' ? 'danger' : 'success'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre *</label>
                            <input type="text" class="form-control" id="titre" name="titre" required>
                        </div>

                        <div class="mb-3">
                            <label for="auteur" class="form-label">Auteur *</label>
                            <input type="text" class="form-control" id="auteur" name="auteur" required>
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN *</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_publication" class="form-label">Date de publication</label>
                            <input type="date" class="form-control" id="date_publication" name="date_publication">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="disponible">Disponible</option>
                                <option value="emprunté">Emprunté</option>
                                <option value="en réparation">En réparation</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo du livre</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Ajouter le livre</button>
                            <a href="books.php" class="btn btn-secondary">Retour à la liste</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
