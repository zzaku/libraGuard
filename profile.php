<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!estConnecte()) {
    rediriger('login.php', 'Vous devez être connecté pour accéder à cette page.', 'error');
}

$utilisateur_id = $_SESSION['user_id'];
$message = '';
$type_message = '';

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = nettoyer($_POST['nom']);
    $prenom = nettoyer($_POST['prenom']);
    $email = nettoyer($_POST['email']);

    try {
        $query = "UPDATE utilisateurs SET 
                 nom = :nom,
                 prenom = :prenom,
                 email = :email
                 WHERE id = :id";
        
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':id' => $utilisateur_id
        ]);

        if ($result) {
            $message = 'Profil mis à jour avec succès.';
            $type_message = 'success';
        } else {
            $message = 'Erreur lors de la mise à jour du profil.';
            $type_message = 'error';
        }
    } catch (PDOException $e) {
        $message = 'Erreur lors de la mise à jour du profil.';
        $type_message = 'error';
    }
}

// Récupération des informations de l'utilisateur
try {
    $query = "SELECT u.*, r.nom as role_nom 
              FROM utilisateurs u 
              JOIN roles r ON u.role_id = r.id 
              WHERE u.id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $utilisateur_id]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Erreur lors de la récupération des informations.';
    $type_message = 'error';
}

$page_title = "Mon Profil";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $type_message; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">
                        <i class="fas fa-user"></i> Mon Profil
                    </h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($utilisateur['role_nom']); ?>" readonly>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="change_password.php" class="btn btn-warning">
                                <i class="fas fa-key"></i> Changer le mot de passe
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section des emprunts en cours -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-book-reader"></i> Mes emprunts en cours
                    </h3>
                </div>
                <div class="card-body">
                    <?php
                    $emprunts = getEmpruntsUtilisateur($pdo, $utilisateur_id);
                    if ($emprunts): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Livre</th>
                                        <th>Date d'emprunt</th>
                                        <th>Date de retour prévue</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emprunts as $emprunt): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($emprunt['titre']); ?></td>
                                            <td><?php echo formaterDate($emprunt['date_emprunt']); ?></td>
                                            <td><?php echo formaterDate($emprunt['date_retour_prevue']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $emprunt['statut'] === 'en cours' ? 'primary' : 'success'; ?>">
                                                    <?php echo htmlspecialchars($emprunt['statut']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Vous n'avez aucun emprunt en cours.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 