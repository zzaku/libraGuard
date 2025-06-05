<?php
require_once('config.php');
require_once('includes/functions.php');

if (!isset($_GET['id'])) {
    rediriger('books.php', 'ID du livre non spécifié.');
}

$livre_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
    $stmt->execute([$livre_id]);
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        rediriger('books.php', 'Livre non trouvé.');
    }
} catch (PDOException $e) {
    rediriger('books.php', 'Erreur lors de la récupération du livre.');
}

$page_title = htmlspecialchars($livre['titre']);
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if ($livre['photo_url']): ?>
                        <img src="<?php echo htmlspecialchars($livre['photo_url']); ?>" 
                             class="img-fluid rounded" 
                             alt="Couverture du livre">
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            Pas d'image disponible
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h1>
                    <h6 class="card-subtitle mb-3 text-muted">par <?php echo htmlspecialchars($livre['auteur']); ?></h6>

                    <div class="mb-3">
                        <p><strong>ISBN :</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                        <p><strong>Date de publication :</strong> 
                            <?php echo $livre['date_publication'] ? date('d/m/Y', strtotime($livre['date_publication'])) : 'Non spécifiée'; ?>
                        </p>
                        <p><strong>Statut :</strong> 
                            <span class="badge bg-<?php 
                                echo $livre['statut'] === 'disponible' ? 'success' : 
                                    ($livre['statut'] === 'emprunté' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo htmlspecialchars($livre['statut']); ?>
                            </span>
                        </p>
                    </div>

                    <?php if ($livre['description']): ?>
                        <div class="mb-3">
                            <h3>Description</h3>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($livre['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <?php if ($livre['statut'] === 'disponible' && estConnecte()): ?>
                            <a href="emprunter.php?id=<?php echo $livre['id']; ?>" 
                               class="btn btn-primary">Emprunter ce livre</a>
                        <?php endif; ?>
                        
                        <?php if (estAdmin()): ?>
                            <a href="edit_book.php?id=<?php echo $livre['id']; ?>" 
                               class="btn btn-warning">Modifier</a>
                            <a href="delete_book.php?id=<?php echo $livre['id']; ?>" 
                               class="btn btn-danger">Supprimer</a>
                        <?php endif; ?>
                        
                        <a href="books.php" class="btn btn-secondary">Retour à la liste</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

