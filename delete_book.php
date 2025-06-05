<?php
require_once('config.php');
require_once('includes/functions.php');

// Vérifier si l'utilisateur est connecté et a le rôle approprié
if (!estConnecte() || !estAdmin()) {
    rediriger('login.php', 'Vous devez être administrateur pour accéder à cette page.', 'error');
}

// Vérifier si un ID de livre est fourni
if (!isset($_GET['id'])) {
    rediriger('books.php', 'ID du livre non spécifié.', 'error');
}

$id = (int)$_GET['id'];

// Vérifier si le livre est disponible avant d'afficher la page
if (!livreEstDisponible($pdo, $id)) {
    rediriger('books.php', 'Ce livre ne peut pas être supprimé car il est actuellement emprunté.', 'error');
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du CAPTCHA
    if (!isset($_POST['captcha']) || !verifierCaptcha($_POST['captcha'])) {
        rediriger('books.php', 'Le CAPTCHA est incorrect.', 'error');
    }

    if (supprimerLivre($pdo, $id)) {
        rediriger('books.php', 'Le livre a été supprimé avec succès.', 'success');
    } else {
        rediriger('books.php', 'Erreur lors de la suppression du livre.', 'error');
    }
}

try {
    $query = "SELECT * FROM livres WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $id]);
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        rediriger('books.php', 'Livre non trouvé.', 'error');
    }
} catch (PDOException $e) {
    rediriger('books.php', 'Erreur lors de la récupération du livre.', 'error');
}

$page_title = "Supprimer un Livre";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Supprimer un Livre</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">Attention !</h4>
                        <p>Vous êtes sur le point de supprimer le livre suivant :</p>
                        <ul>
                            <li><strong>Titre :</strong> <?php echo htmlspecialchars($livre['titre']); ?></li>
                            <li><strong>Auteur :</strong> <?php echo htmlspecialchars($livre['auteur']); ?></li>
                            <li><strong>ISBN :</strong> <?php echo htmlspecialchars($livre['isbn']); ?></li>
                            <li><strong>Statut :</strong> <?php echo htmlspecialchars($livre['statut']); ?></li>
                        </ul>
                        <p>Cette action est irréversible. Êtes-vous sûr de vouloir continuer ?</p>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
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
                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                            <a href="books.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
