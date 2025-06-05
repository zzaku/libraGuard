<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Traitement du retour d'un livre
if (isset($_POST['retourner_livre']) && isset($_POST['emprunt_id'])) {
    $emprunt_id = (int)$_POST['emprunt_id'];
    
    // Vérifier que l'emprunt appartient bien à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM emprunts WHERE id = ? AND utilisateur_id = ?");
    $stmt->execute([$emprunt_id, $_SESSION['user_id']]);
    $emprunt = $stmt->fetch();
    
    if ($emprunt) {
        // Mettre à jour le statut de l'emprunt
        $stmt = $pdo->prepare("UPDATE emprunts SET 
            statut = 'retourné',
            date_retour_effective = NOW()
            WHERE id = ?");
        $stmt->execute([$emprunt_id]);
        
        // Mettre à jour le statut du livre
        $stmt = $pdo->prepare("UPDATE livres SET statut = 'disponible' WHERE id = ?");
        $stmt->execute([$emprunt['livre_id']]);
        
        $_SESSION['success'] = "Le livre a été retourné avec succès.";
    }
    
    header('Location: mes_emprunts.php');
    exit();
}

// Récupérer tous les emprunts de l'utilisateur
$stmt = $pdo->prepare("
    SELECT e.*, l.titre, l.auteur, l.isbn, l.photo_url
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id
    WHERE e.utilisateur_id = ?
    ORDER BY e.date_emprunt DESC
");
$stmt->execute([$_SESSION['user_id']]);
$emprunts = $stmt->fetchAll();

$page_title = "Mes Emprunts";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Mes Emprunts</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($emprunts)): ?>
        <div class="alert alert-info">
            Vous n'avez aucun emprunt en cours.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($emprunts as $emprunt): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <?php if ($emprunt['photo_url']): ?>
                            <img src="<?php echo htmlspecialchars($emprunt['photo_url']); ?>" 
                                 class="card-img-top" alt="Couverture du livre"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($emprunt['titre']); ?></h5>
                            <p class="card-text">
                                <strong>Auteur:</strong> <?php echo htmlspecialchars($emprunt['auteur']); ?><br>
                                <strong>ISBN:</strong> <?php echo htmlspecialchars($emprunt['isbn']); ?><br>
                                <strong>Date d'emprunt:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?><br>
                                <strong>Date de retour prévue:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?><br>
                                <strong>Statut:</strong> 
                                <span class="badge <?php 
                                    echo $emprunt['statut'] === 'en cours' ? 'bg-primary' : 
                                        ($emprunt['statut'] === 'retourné' ? 'bg-success' : 'bg-danger'); 
                                ?>">
                                    <?php echo ucfirst($emprunt['statut']); ?>
                                </span>
                            </p>
                            
                            <?php if ($emprunt['statut'] === 'en cours'): ?>
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="emprunt_id" value="<?php echo $emprunt['id']; ?>">
                                    <button type="submit" name="retourner_livre" class="btn btn-primary">
                                        Retourner le livre
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 