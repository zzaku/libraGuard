<?php
require_once('config.php');
require_once('includes/functions.php');

if (!estConnecte()) {
    rediriger('login.php', 'Vous devez être connecté pour accéder à cette page.', 'error');
}

$message = '';
$type_message = '';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (estAdmin()) {
        if (supprimerLivre($pdo, $_GET['id'])) {
            $message = 'Le livre a été supprimé avec succès.';
            $type_message = 'success';
        } else {
            $message = 'Erreur lors de la suppression du livre.';
            $type_message = 'error';
        }
    } else {
        $message = 'Vous n\'avez pas les droits pour supprimer un livre.';
        $type_message = 'error';
    }
}

$livres = getAllLivres($pdo);
$page_title = "Liste des Livres";
include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $type_message; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Liste des Livres</h2>
        <?php if (estAdmin()): ?>
            <a href="add_book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un livre
            </a>
        <?php endif; ?>
    </div>

    <?php if ($livres): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date de publication</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livres as $livre): ?>
                        <tr>
                            <td>
                                <?php if (!empty($livre['photo_url'])): ?>
                                    <img class="img-thumbnail" style="max-width: 100px; height: 150px; object-fit: cover;" 
                                         src="<?php echo htmlspecialchars($livre['photo_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($livre['titre']); ?>">
                                <?php else: ?>
                                    <img class="img-thumbnail" style="max-width: 100px; height: 150px; object-fit: cover;" 
                                         src="image/no-image.png" alt="Pas d'image">
                                <?php endif; ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($livre['titre']); ?></td>
                            <td class="align-middle"><?php echo htmlspecialchars($livre['auteur']); ?></td>
                            <td class="align-middle"><?php echo htmlspecialchars($livre['date_publication']); ?></td>
                            <td class="align-middle">
                                <span class="badge bg-<?php echo $livre['statut'] === 'disponible' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($livre['statut']); ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <div class="btn-group" role="group">
                                    <a href="book_details.php?id=<?php echo $livre['id']; ?>" 
                                       class="btn btn-info" title="Voir les détails">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                    <?php if (estAdmin()): ?>
                                        <a href="edit_book.php?id=<?php echo $livre['id']; ?>" 
                                           class="btn btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="delete_book.php?id=<?php echo $livre['id']; ?>" 
                                           class="btn btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Aucun livre trouvé.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
