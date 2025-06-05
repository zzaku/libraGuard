<?php

/**
 * Nettoie les données entrées par l'utilisateur
 * @param string $data Les données à nettoyer
 * @return string Les données nettoyées
 */
function nettoyer($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirige vers une URL avec un message optionnel
 * @param string $url L'URL de redirection
 * @param string $message Le message à afficher
 */
function rediriger($url, $message = '') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
    }
    header("Location: $url");
    exit();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function estConnecte() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est administrateur
 * @return bool True si l'utilisateur est admin, false sinon
 */
function estAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Vérifie si un email existe déjà dans la base de données
 * @param PDO $pdo L'instance PDO
 * @param string $email L'email à vérifier
 * @return bool True si l'email existe, false sinon
 */
function emailExiste($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Récupère l'ID d'un rôle par son nom
 * @param PDO $pdo L'instance PDO
 * @param string $nom_role Le nom du rôle
 * @return int|false L'ID du rôle ou false si non trouvé
 */
function getRoleId($pdo, $nom_role) {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE nom = ?");
    $stmt->execute([$nom_role]);
    return $stmt->fetchColumn();
}

/**
 * Vérifie si un mot de passe respecte les critères de sécurité
 * @param string $mot_de_passe Le mot de passe à vérifier
 * @return array Un tableau contenant le statut (bool) et le message d'erreur si applicable
 */
function verifierForceMotDePasse($mot_de_passe) {
    if (strlen($mot_de_passe) < 8) {
        return [
            'valide' => false,
            'message' => 'Le mot de passe doit contenir au moins 8 caractères.'
        ];
    }

    if (!preg_match('/[A-Z]/', $mot_de_passe)) {
        return [
            'valide' => false,
            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule.'
        ];
    }

    if (!preg_match('/[a-z]/', $mot_de_passe)) {
        return [
            'valide' => false,
            'message' => 'Le mot de passe doit contenir au moins une lettre minuscule.'
        ];
    }

    if (!preg_match('/[0-9]/', $mot_de_passe)) {
        return [
            'valide' => false,
            'message' => 'Le mot de passe doit contenir au moins un chiffre.'
        ];
    }

    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $mot_de_passe)) {
        return [
            'valide' => false,
            'message' => 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()-_=+{};:,<.>).'
        ];
    }

    return [
        'valide' => true,
        'message' => 'Le mot de passe est valide.'
    ];
}

/**
 * Génère un code CAPTCHA
 * @return string Le code CAPTCHA généré
 */
function genererCaptcha() {
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    $_SESSION['captcha'] = $code;
    return $code;
}

/**
 * Vérifie si le code CAPTCHA est correct
 * @param string $code Le code à vérifier
 * @return bool True si le code est correct, false sinon
 */
function verifierCaptcha($code) {
    return isset($_SESSION['captcha']) && strtoupper($code) === $_SESSION['captcha'];
}

/**
 * Vérifie si un livre est disponible
 * @param PDO $pdo L'instance PDO
 * @param int $livre_id L'ID du livre
 * @return bool True si le livre est disponible, false sinon
 */
function livreEstDisponible($pdo, $livre_id) {
    try {
        // Vérifier si le livre est actuellement emprunté
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM emprunts 
            WHERE livre_id = ? 
            AND date_retour_effective IS NULL
        ");
        $stmt->execute([$livre_id]);
        $estEmprunte = $stmt->fetchColumn() > 0;

        // Vérifier le statut du livre
        $stmt = $pdo->prepare("SELECT statut FROM livres WHERE id = ?");
        $stmt->execute([$livre_id]);
        $statut = $stmt->fetchColumn();

        // Le livre est disponible seulement s'il n'est pas emprunté et son statut est 'disponible'
        return !$estEmprunte && $statut === 'disponible';
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification de disponibilité du livre : " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si un utilisateur peut emprunter un livre
 * @param PDO $pdo L'instance PDO
 * @param int $utilisateur_id L'ID de l'utilisateur
 * @return bool True si l'utilisateur peut emprunter, false sinon
 */
function utilisateurPeutEmprunter($pdo, $utilisateur_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM emprunts 
        WHERE utilisateur_id = ? 
        AND statut = 'en cours'
    ");
    $stmt->execute([$utilisateur_id]);
    return $stmt->fetchColumn() < 3; // Maximum 3 emprunts en cours
}

function afficherMessage() {
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
}

function genererTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifierTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('Erreur de sécurité : Token CSRF invalide');
    }
    return true;
}

function formaterDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function aRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function verifierAcces($roleRequis) {
    if (!estConnecte()) {
        rediriger('login.php', 'Vous devez être connecté pour accéder à cette page');
    }
    
    if (!aRole($roleRequis)) {
        rediriger('index.php', 'Vous n\'avez pas les droits nécessaires pour accéder à cette page');
    }
}

function getNombreUtilisateurs($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function getRoleUtilisateur($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.nom 
            FROM roles r 
            JOIN utilisateurs u ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return null;
    }
}

function getRoles($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function ajouterLivre($pdo, $titre, $auteur, $isbn, $date_publication, $description, $statut, $photo_url) {
    try {
        $query = "INSERT INTO livres (titre, auteur, isbn, date_publication, description, statut, photo_url) 
                 VALUES (:titre, :auteur, :isbn, :date_publication, :description, :statut, :photo_url)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            ':titre' => $titre,
            ':auteur' => $auteur,
            ':isbn' => $isbn,
            ':date_publication' => $date_publication,
            ':description' => $description,
            ':statut' => $statut,
            ':photo_url' => $photo_url
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function modifierLivre($pdo, $id, $titre, $auteur, $isbn, $date_publication, $description, $statut, $photo_url) {
    try {
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
        return $stmt->execute([
            ':titre' => $titre,
            ':auteur' => $auteur,
            ':isbn' => $isbn,
            ':date_publication' => $date_publication,
            ':description' => $description,
            ':statut' => $statut,
            ':photo_url' => $photo_url,
            ':id' => $id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function supprimerLivre($pdo, $id) {
    try {
        if (!livreEstDisponible($pdo, $id)) {
            return false; 
        }

        $query = "SELECT photo_url FROM livres WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $id]);
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$livre) {
            return false; 
        }

        $query = "DELETE FROM emprunts WHERE livre_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $id]);

        $query = "DELETE FROM livres WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([':id' => $id]);

        if ($result && !empty($livre['photo_url']) && file_exists($livre['photo_url'])) {
            unlink($livre['photo_url']);
        }

        return $result;
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression du livre : " . $e->getMessage());
        return false;
    }
}

function getLivre($pdo, $id) {
    try {
        $query = "SELECT * FROM livres WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function getAllLivres($pdo) {
    try {
        $query = "SELECT * FROM livres ORDER BY titre";
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function uploadImage($file, $destination_dir = 'image/books/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return false;
    }

    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0777, true);
    }

    $new_filename = uniqid() . '.' . $ext;
    $upload_path = $destination_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }

    return false;
}

function emprunterLivre($pdo, $livre_id, $utilisateur_id, $date_retour_prevue) {
    try {
        $stmt = $pdo->prepare("SELECT statut FROM livres WHERE id = ?");
        $stmt->execute([$livre_id]);
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$livre || $livre['statut'] !== 'disponible') {
            return false;
        }

        $stmt = $pdo->prepare("SELECT id FROM emprunts WHERE livre_id = ? AND utilisateur_id = ? AND statut = 'en cours'");
        $stmt->execute([$livre_id, $utilisateur_id]);
        if ($stmt->fetch()) {
            return false;
        }

        $stmt = $pdo->prepare("INSERT INTO emprunts (livre_id, utilisateur_id, date_retour_prevue) VALUES (?, ?, ?)");
        $stmt->execute([$livre_id, $utilisateur_id, $date_retour_prevue]);

        $stmt = $pdo->prepare("UPDATE livres SET statut = 'emprunté' WHERE id = ?");
        $stmt->execute([$livre_id]);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function retournerLivre($pdo, $emprunt_id) {
    try {
        $stmt = $pdo->prepare("SELECT livre_id FROM emprunts WHERE id = ?");
        $stmt->execute([$emprunt_id]);
        $emprunt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$emprunt) {
            return false;
        }

        $stmt = $pdo->prepare("UPDATE emprunts SET statut = 'retourné', date_retour_effective = NOW() WHERE id = ?");
        $stmt->execute([$emprunt_id]);

        $stmt = $pdo->prepare("UPDATE livres SET statut = 'disponible' WHERE id = ?");
        $stmt->execute([$emprunt['livre_id']]);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getEmpruntsUtilisateur($pdo, $utilisateur_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, l.titre, l.auteur, l.isbn, l.photo_url
            FROM emprunts e
            JOIN livres l ON e.livre_id = l.id
            WHERE e.utilisateur_id = ?
            ORDER BY e.date_emprunt DESC
        ");
        $stmt->execute([$utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function verifierEmpruntsEnRetard($pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE emprunts 
            SET statut = 'en retard'
            WHERE statut = 'en cours' 
            AND date_retour_prevue < CURDATE()
        ");
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getEmpruntsEnRetard($pdo, $utilisateur_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, l.titre, l.auteur
            FROM emprunts e
            JOIN livres l ON e.livre_id = l.id
            WHERE e.utilisateur_id = ? 
            AND e.statut = 'en retard'
        ");
        $stmt->execute([$utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT u.id, u.nom, u.prenom, u.email, r.nom AS role, u.date_inscription, u.derniere_connexion
            FROM utilisateurs u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération des utilisateurs : ' . $e->getMessage());
        return [];
    }
}

function updateUserRole($pdo, $user_id, $role_nom) {
    $role_id = getRoleId($pdo, $role_nom);
    if (!$role_id) return false;
    try {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET role_id = ? WHERE id = ?");
        return $stmt->execute([$role_id, $user_id]);
    } catch (PDOException $e) {
        error_log('Erreur updateUserRole : ' . $e->getMessage());
        return false;
    }
}
