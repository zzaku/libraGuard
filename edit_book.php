<?php
require_once('config.php');
require_once('includes/functions.php');

// Vérifier si l'utilisateur est connecté et a le rôle approprié
if (!estConnecte() || !estAdmin()) {
    rediriger('login.php', 'Vous devez être administrateur pour accéder à cette page.', 'error');
}

$message = '';
$type_message = '';
$livre = null;

// Vérifier si un ID de livre est fourni
if (!isset($_GET['id'])) {
    rediriger('books.php', 'ID du livre non spécifié.', 'error');
}

$id = (int)$_GET['id'];

// Récupérer les informations du livre
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du CAPTCHA
    if (!isset($_POST['captcha']) || !verifierCaptcha($_POST['captcha'])) {
        $message = 'Le CAPTCHA est incorrect.';
        $type_message = 'error';
    } else {
        // Récupération et nettoyage des données
        $titre = nettoyer($_POST['titre']);
        $auteur = nettoyer($_POST['auteur']);
        $isbn = nettoyer($_POST['isbn']);
        $date_publication = nettoyer($_POST['date_publication']);
        $description = nettoyer($_POST['description']);
        $statut = nettoyer($_POST['statut']);

        // Validation des données
        if (empty($titre) || empty($auteur) || empty($isbn)) {
            $message = 'Tous les champs obligatoires doivent être remplis.';
            $type_message = 'error';
        } else {
            try {
                // Gestion de l'upload d'image
                $photo_url = $livre['photo_url']; // Garder l'ancienne image par défaut
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['photo']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_path = 'image/books/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                            // Supprimer l'ancienne image si elle existe
                            if (!empty($livre['photo_url']) && file_exists($livre['photo_url'])) {
                                unlink($livre['photo_url']);
                            }
                            $photo_url = $upload_path;
                        }
                    }
                }

                // Mise à jour dans la base de données
                $query = "UPDATE livres SET 
                         titre = :titre,
                         auteur = :auteur,
                         isbn = :isbn,
                         date_publication = :date_publication,
                         description = :description,
                         statut = :statut,
                         photo_url = :photo_url
                         WHERE id = :id";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':titre' => $titre,
                    ':auteur' => $auteur,
                    ':isbn' => $isbn,
                    ':date_publication' => $date_publication,
                    ':description' => $description,
                    ':statut' => $statut,
                    ':photo_url' => $photo_url,
                    ':id' => $id
                ]);

                $message = 'Le livre a été modifié avec succès.';
                $type_message = 'success';
                
                // Mettre à jour les données du livre affichées
                $livre = array_merge($livre, [
                    'titre' => $titre,
                    'auteur' => $auteur,
                    'isbn' => $isbn,
                    'date_publication' => $date_publication,
                    'description' => $description,
                    'statut' => $statut,
                    'photo_url' => $photo_url
                ]);
            } catch (PDOException $e) {
                $message = 'Erreur lors de la modification du livre.';
                $type_message = 'error';
            }
        }
    }
}

$page_title = "Modifier un Livre";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Modifier un Livre</h2>
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
                            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="auteur" class="form-label">Auteur *</label>
                            <input type="text" class="form-control" id="auteur" name="auteur" value="<?php echo htmlspecialchars($livre['auteur']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN *</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($livre['isbn']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_publication" class="form-label">Date de publication</label>
                            <input type="date" class="form-control" id="date_publication" name="date_publication" value="<?php echo htmlspecialchars($livre['date_publication']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($livre['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="disponible" <?php echo $livre['statut'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                <option value="emprunté" <?php echo $livre['statut'] === 'emprunté' ? 'selected' : ''; ?>>Emprunté</option>
                                <option value="en réparation" <?php echo $livre['statut'] === 'en réparation' ? 'selected' : ''; ?>>En réparation</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo du livre</label>
                            <?php if (!empty($livre['photo_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($livre['photo_url']); ?>" alt="Photo actuelle" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
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
                            <button type="submit" class="btn btn-primary">Modifier le livre</button>
                            <a href="books.php" class="btn btn-secondary">Retour à la liste</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
