<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Traitement de l'ajout d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json');
    $nom = nettoyer($_POST['nom']);
    $prenom = nettoyer($_POST['prenom']);
    $email = nettoyer($_POST['email']);
    $password = $_POST['password'];
    $role = nettoyer($_POST['role']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
        exit;
    }

    if (emailExiste($pdo, $email)) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $roleId = getRoleId($pdo, $role);

    if (!$roleId) {
        echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role_id, date_inscription) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$nom, $prenom, $email, $hashedPassword, $roleId]);
        if ($result) {
            $user_id = $pdo->lastInsertId();
            $role_label = $role === 'admin' ? 'Administrateur' : 'Utilisateur';
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur ajouté avec succès',
                'user' => [
                    'id' => $user_id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'role' => $role,
                    'role_label' => $role_label,
                    'date_inscription' => date('Y-m-d'),
                    'derniere_connexion' => ''
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'utilisateur']);
        }
    } catch (PDOException $e) {
        error_log('Erreur ajout utilisateur : ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
    }
    exit;
}

// Endpoint AJAX pour la mise à jour du rôle
if (
    isset($_POST['action']) && $_POST['action'] === 'update_role'
    && isset($_POST['user_id']) && isset($_POST['role'])
) {
    header('Content-Type: application/json');
    if (!estConnecte() || !estAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
        exit;
    }
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];
    if ($user_id === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre rôle']);
        exit;
    }
    if ($role !== 'admin' && $role !== 'utilisateur') {
        echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
        exit;
    }
    $result = updateUserRole($pdo, $user_id, $role);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Rôle mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du rôle']);
    }
    exit;
}

// Vérifier si l'utilisateur est connecté et est admin
if (!estConnecte() || !estAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Administration";
include 'includes/header.php';

// Récupérer tous les utilisateurs
$utilisateurs = getAllUsers($pdo);
?>

<div class="container mt-4">
    <h1 class="mb-4">Administration</h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users"></i> Gestion des utilisateurs
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Boutons d'action -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button class="btn btn-success btn-lg w-100 mb-3" onclick="showModule('add-user')">
                                <i class="fas fa-user-plus"></i> Ajouter un utilisateur
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info btn-lg w-100 mb-3" onclick="showModule('user-list')">
                                <i class="fas fa-list"></i> Liste des utilisateurs
                            </button>
                        </div>
                    </div>

                    <!-- Module d'ajout d'utilisateur -->
                    <div id="add-user" class="module" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">Ajouter un utilisateur</h5>
                            </div>
                            <div class="card-body">
                                <form id="add-user-form" onsubmit="return handleAddUser(event)">
                                    <input type="hidden" name="action" value="add">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom</label>
                                            <input type="text" class="form-control" id="nom" name="nom" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="prenom" class="form-label">Prénom</label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rôle</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="utilisateur">Utilisateur</option>
                                            <option value="admin">Administrateur</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Module liste des utilisateurs -->
                    <div id="user-list" class="module" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">Liste des utilisateurs</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Email</th>
                                                <th>Rôle</th>
                                                <th>Date d'inscription</th>
                                                <th>Dernière connexion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($utilisateurs as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['id'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($user['nom'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($user['prenom'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                                <td>
                                                    <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                            <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <select class="form-select" onchange="updateRole(<?php echo $user['id']; ?>, this.value)">
                                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                                            <option value="utilisateur" <?php echo $user['role'] === 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                                                        </select>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['date_inscription'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($user['derniere_connexion'] ?? ''); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showModule(moduleId) {
    // Cacher tous les modules
    document.querySelectorAll('.module').forEach(module => {
        module.style.display = 'none';
    });
    // Afficher le module sélectionné
    document.getElementById(moduleId).style.display = 'block';
}

function handleAddUser(event) {
    event.preventDefault();
    const form = document.getElementById('add-user-form');
    const formData = new FormData(form);
    fetch('admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
        messageDiv.textContent = data.message;
        form.parentNode.insertBefore(messageDiv, form);
        setTimeout(() => messageDiv.remove(), 3000);
        if (data.success) {
            form.reset();
            // Ajout dynamique dans le tableau
            if (data.user) {
                const tbody = document.querySelector('#user-list table tbody');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${data.user.id}</td>
                    <td>${data.user.nom}</td>
                    <td>${data.user.prenom}</td>
                    <td>${data.user.email}</td>
                    <td>
                        <select class="form-select" onchange="updateRole(${data.user.id}, this.value)">
                            <option value="admin" ${data.user.role === 'admin' ? 'selected' : ''}>Administrateur</option>
                            <option value="utilisateur" ${data.user.role === 'utilisateur' ? 'selected' : ''}>Utilisateur</option>
                        </select>
                    </td>
                    <td>${data.user.date_inscription}</td>
                    <td></td>
                `;
                tbody.appendChild(tr);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        const messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Erreur lors de l\'enregistrement';
        form.parentNode.insertBefore(messageDiv, form);
        setTimeout(() => messageDiv.remove(), 3000);
    });
    return false;
}

function updateRole(userId, newRole) {
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_role&user_id=' + userId + '&role=' + newRole
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success';
            successMessage.textContent = data.message;
            document.querySelector('.card-body').prepend(successMessage);
            setTimeout(() => successMessage.remove(), 3000);
        } else {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger';
            errorMessage.textContent = data.message;
            document.querySelector('.card-body').prepend(errorMessage);
            setTimeout(() => errorMessage.remove(), 3000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        const errorMessage = document.createElement('div');
        errorMessage.className = 'alert alert-danger';
        errorMessage.textContent = 'Erreur lors de la mise à jour du rôle';
        document.querySelector('.card-body').prepend(errorMessage);
        setTimeout(() => errorMessage.remove(), 3000);
    });
}
</script>

<?php include 'includes/footer.php'; ?> 