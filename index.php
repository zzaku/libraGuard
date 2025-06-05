<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('config.php');
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($page === 'books') {
    include("books.php");
} else {
    $page_title = "Accueil";
    include 'includes/header.php';

    // Récupérer les emprunts de l'utilisateur connecté
    $emprunts_en_cours = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT e.*, l.titre, l.auteur, l.photo_url
            FROM emprunts e
            JOIN livres l ON e.livre_id = l.id
            WHERE e.utilisateur_id = ? AND e.statut = 'en cours'
            ORDER BY e.date_emprunt DESC
            LIMIT 3
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $emprunts_en_cours = $stmt->fetchAll();
    }
    ?>

    <div class="container mt-4">
        <div class="jumbotron bg-light p-5 rounded">
            <h1 class="display-4">Bienvenue sur LibraGuard</h1>
            <p class="lead">Votre système de gestion de bibliothèque en ligne</p>
            <hr class="my-4">
            <p>Gérez vos emprunts, découvrez de nouveaux livres et plus encore.</p>
            <a class="btn btn-primary btn-lg" href="books.php" role="button">Voir les livres</a>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="row mt-5">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Mes emprunts en cours</h5>
                            <a href="mes_emprunts.php" class="btn btn-primary btn-sm">Voir tous mes emprunts</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($emprunts_en_cours)): ?>
                                <p class="text-muted">Vous n'avez aucun emprunt en cours.</p>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($emprunts_en_cours as $emprunt): ?>
                                        <div class="col-md-4 mb-3">
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
                                                        <strong>Date de retour prévue:</strong><br>
                                                        <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?>
                                                    </p>
                                                    <a href="mes_emprunts.php" class="btn btn-primary">Gérer mes emprunts</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Connectez-vous</h5>
                            <p class="card-text">Accédez à votre compte pour gérer vos emprunts.</p>
                            <a href="login.php" class="btn btn-primary">Se connecter</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Nouveau ?</h5>
                            <p class="card-text">Créez un compte pour commencer à emprunter des livres.</p>
                            <a href="register.php" class="btn btn-success">S'inscrire</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
<?php
}
