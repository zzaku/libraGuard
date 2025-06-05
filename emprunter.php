<?php
require_once('config.php');
require_once('includes/functions.php');

if (!estConnecte()) {
    rediriger('login.php', 'Vous devez être connecté pour emprunter un livre.');
}

$message = '';
$type_message = '';

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

    if ($livre['statut'] !== 'disponible') {
        rediriger('books.php', 'Ce livre n\'est pas disponible pour l\'emprunt.');
    }
} catch (PDOException $e) {
    rediriger('books.php', 'Erreur lors de la récupération du livre.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_retour = nettoyer($_POST['date_retour']);

    if (empty($date_retour)) {
        $message = 'La date de retour est requise.';
        $type_message = 'danger';
    } else {
        $date_retour_obj = new DateTime($date_retour);
        $date_aujourdhui = new DateTime();
        $interval = $date_aujourdhui->diff($date_retour_obj);
        
        if ($interval->days > 30) {
            $message = 'La durée d\'emprunt ne peut pas dépasser 30 jours.';
            $type_message = 'danger';
        } else {
            if (emprunterLivre($pdo, $livre_id, $_SESSION['user_id'], $date_retour)) {
                rediriger('mes_emprunts.php', 'Livre emprunté avec succès !');
            } else {
                $message = 'Erreur lors de l\'emprunt du livre.';
                $type_message = 'danger';
            }
        }
    }
}

$date_max = new DateTime();
$date_max->modify('+30 days');

$page_title = "Emprunter un Livre";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Emprunter un Livre</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $type_message; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h2>
            <p class="card-text"><strong>Auteur :</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
            <p class="card-text"><strong>ISBN :</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
            <?php if ($livre['photo_url']): ?>
                <img src="<?php echo htmlspecialchars($livre['photo_url']); ?>" alt="Couverture du livre" class="img-fluid mb-3" style="max-width: 200px;">
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" class="form">
                <div class="mb-3">
                    <label for="date_retour" class="form-label">Date de retour prévue :</label>
                    <input type="date" class="form-control" id="date_retour" name="date_retour" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           max="<?php echo $date_max->format('Y-m-d'); ?>" 
                           required>
                    <div class="form-text">La durée d'emprunt ne peut pas dépasser 30 jours.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Emprunter le livre</button>
                    <a href="books.php" class="btn btn-secondary">Retour à la liste</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 