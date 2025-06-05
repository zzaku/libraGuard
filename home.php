<?php
    require_once('config.php');
    require_once('includes/functions.php');


$queryTotalBooks = "SELECT COUNT(*) as total_books FROM livres";
$stmtTotalBooks = $pdo->prepare($queryTotalBooks);
$stmtTotalBooks->execute();
$resultTotalBooks = $stmtTotalBooks->fetch(PDO::FETCH_ASSOC);

$totalUsers = getNombreUtilisateurs($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accueil - LibraGuard</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>LibraGuard</h1>
    </header>

    <div class="wrapper">
        <nav id="sidebar">
            <ul>
                <?php if (estConnecte()) : ?>
                    <li>Bonjour <?php echo htmlspecialchars($_SESSION['user_prenom']); ?></li>
                    <li><a href="books.php">Voir la liste des livres</a></li>
                    <li><a href="profile.php">Mon profil</a></li>
                    <?php if (estAdmin()) : ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else : ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div id="content">
            <div class="container">
                <h1>Tableau de bord</h1>
                
                <div class="statistics-grid">
                    <div class="statistic">
                        <h3>Total des Livres</h3>
                        <p><?php echo $resultTotalBooks['total_books']; ?></p>
                    </div>

                    <div class="statistic">
                        <h3>Utilisateurs Enregistrés</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>

                <?php if (!estConnecte()) : ?>
                    <div class="welcome-message">
                        <h2>Bienvenue sur LibraGuard</h2>
                        <p>Connectez-vous ou créez un compte pour accéder à toutes les fonctionnalités.</p>
                        <div class="cta-buttons">
                            <a href="login.php" class="btn btn-primary">Se connecter</a>
                            <a href="register.php" class="btn btn-primary">S'inscrire</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>