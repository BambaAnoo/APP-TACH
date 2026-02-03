<?php
$dossierPublic = "http://localhost/projetTaches/public";
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../includes/sidebar.php";

// Inclure le fichier de connexion à la base de données
include '../traitement/db.php';

// Initialisation des variables
$tache_a_modifier = null;
$message = '';
$message_type = '';

// Récupérer la liste des utilisateurs pour le select
$stmt_users = $pdo->query("SELECT id, nom FROM users ORDER BY nom ASC");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Récupérer une tâche à modifier
if (isset($_GET['modifier'])) {
    $id = (int)$_GET['modifier'];
    $stmt = $pdo->prepare("SELECT tache.*, users.nom AS user_nom FROM tache LEFT JOIN users ON tache.fk_users = users.id WHERE tache.id = ?");
    $stmt->execute([$id]);
    $tache_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Récupérer les tâches
$stmt = $pdo->query("
    SELECT tache.*, users.nom AS user_nom
    FROM tache
    LEFT JOIN users ON tache.fk_users = users.id
    ORDER BY tache.id DESC
");
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la suppression
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tache WHERE id = ?");
        $stmt->execute([$id]);

        // Rafraîchir les données après la suppression
        $stmt = $pdo->query("
            SELECT tache.*, users.nom AS user_nom
            FROM tache
            LEFT JOIN users ON tache.fk_users = users.id
            ORDER BY tache.id DESC
        ");
        $taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $message = "Tâche supprimée avec succès";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Traitement de l'ajout et de la modification de tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $statut = $_POST['statut'];
    $user_id = (int)$_POST['user_id'];

    try {
        if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
            // Ajouter une tâche
            $stmt = $pdo->prepare("INSERT INTO tache (titre, description, statut, fk_users) VALUES (?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $statut, $user_id]);
            $message = "Tâche ajoutée avec succès";
            $message_type = "success";
        } elseif (isset($_POST['action']) && $_POST['action'] === 'modifier') {
            $id = (int)$_POST['id'];
            // Modifier une tâche
            $stmt = $pdo->prepare("UPDATE tache SET titre = ?, description = ?, statut = ?, fk_users = ? WHERE id = ?");
            $stmt->execute([$titre, $description, $statut, $user_id, $id]);
            $message = "Tâche modifiée avec succès";
            $message_type = "success";
        }

        // Rafraîchir les données après l'ajout ou la modification
        $stmt = $pdo->query("
            SELECT tache.*, users.nom AS user_nom
            FROM tache
            LEFT JOIN users ON tache.fk_users = users.id
            ORDER BY tache.id DESC
        ");
        $taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Réinitialiser la tâche à modifier après une modification
        if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
            $tache_a_modifier = null;
        }
    } catch (PDOException $e) {
        $message = "Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tâches</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge {
            font-size: 0.85rem;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 300px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <!-- Affichage des messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> alert-message alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h1 class="text-center mb-4"><i class="fas fa-tasks me-2"></i>Gestion des Tâches</h1>

        <!-- ==== FORMULAIRE AJOUT / MODIFICATION ==== -->
        <?php if ($tache_a_modifier || !isset($_GET['supprimer'])): ?>
        <div class="card mb-4 col-md-8 offset-md-2">
            <div class="card-header bg-primary text-white">
                <i class="fas <?= $tache_a_modifier ? "fa-edit" : "fa-plus" ?> me-2"></i>
                <?= $tache_a_modifier ? "Modifier une tâche" : "Ajouter une tâche" ?>
            </div>

            <div class="card-body">
                <form method="POST" action="indexTache.php">
                    <input type="hidden" name="action" value="<?= $tache_a_modifier ? "modifier" : "ajouter" ?>">

                    <?php if ($tache_a_modifier): ?>
                        <input type="hidden" name="id" value="<?= $tache_a_modifier['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" required
                            value="<?= $tache_a_modifier ? htmlspecialchars($tache_a_modifier['titre']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= $tache_a_modifier ? htmlspecialchars($tache_a_modifier['description']) : '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Statut <span class="text-danger">*</span></label>
                        <select class="form-select" name="statut" required>
                            <option value="en cours" <?= isset($tache_a_modifier) && $tache_a_modifier['statut'] == "en cours" ? "selected" : "" ?>>En cours</option>
                            <option value="terminée" <?= isset($tache_a_modifier) && $tache_a_modifier['statut'] == "terminée" ? "selected" : "" ?>>Terminée</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Utilisateur <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Sélectionner un utilisateur --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"
                                    <?= (isset($tache_a_modifier) && $tache_a_modifier['fk_users'] == $user['id']) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($user['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">
                            <i class="fas <?= $tache_a_modifier ? "fa-save" : "fa-plus-circle" ?> me-1"></i>
                            <?= $tache_a_modifier ? "Enregistrer les modifications" : "Ajouter la tâche" ?>
                        </button>

                        <?php if ($tache_a_modifier): ?>
                            <a href="indexTache.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- ==== LISTE DES TÂCHES ==== -->
        <h2 class="mb-3 text-center"><i class="fas fa-list me-2"></i>Liste des tâches</h2>

        <?php if (empty($taches)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> Aucune tâche enregistrée.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($taches as $tache): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-3">
                                        <?= htmlspecialchars($tache['titre']) ?>
                                    </h5>
                                    <span class="badge bg-<?= $tache['statut'] == "terminée" ? "success" : "warning" ?>">
                                        <?= ucfirst(htmlspecialchars($tache['statut'])) ?>
                                    </span>
                                </div>

                                <p class="card-text mb-3">
                                    <?= nl2br(htmlspecialchars($tache['description'])) ?>
                                </p>

                                <p class="card-text text-muted mb-3">
                                    <small>
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($tache['user_nom'] ?? 'Utilisateur inconnu') ?>
                                    </small>
                                </p>

                                <div class="d-flex justify-content-end">
                                    <a href="indexTache.php?modifier=<?= $tache['id'] ?>"
                                       class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </a>

                                    <a href="indexTache.php?supprimer=<?= $tache['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');">
                                        <i class="fas fa-trash me-1"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="../js/bootstrap.bundle.min.js"></script>
    <!-- Script pour fermer les alertes après 5 secondes -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fermer les alertes après 5 secondes
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-message');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>

<?php
include_once "../includes/footer.php";
?>
