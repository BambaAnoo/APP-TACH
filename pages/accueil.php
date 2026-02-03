<?php
$dossierPublic = "http://localhost/projetTaches/public";
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../includes/sidebar.php";

// Inclure le fichier de connexion à la base de données
include '../traitement/db.php';

// Initialisation des variables pour la recherche
$searchId = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';
$users = [];
$taches = [];
$users_list = [];

// Récupérer la liste des utilisateurs pour le select
$stmt_users = $pdo->query("SELECT id, nom FROM users ORDER BY nom ASC");
$users_list = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Déterminer le filtre à appliquer
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Récupérer les statistiques
$stmt_users_count = $pdo->query("SELECT COUNT(*) FROM users");
$users_count = $stmt_users_count->fetchColumn();

$stmt_taches_terminees = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut = 'terminée'");
$taches_terminees = $stmt_taches_terminees->fetchColumn();

$stmt_taches_en_cours = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut = 'en cours'");
$taches_en_cours = $stmt_taches_en_cours->fetchColumn();

// Récupérer les tâches selon le filtre sélectionné
$sql = "SELECT tache.*, users.nom AS user_nom FROM tache LEFT JOIN users ON tache.fk_users = users.id";

if ($filter === 'terminée') {
    $sql .= " WHERE tache.statut = 'terminée'";
} elseif ($filter === 'en cours') {
    $sql .= " WHERE tache.statut = 'en cours'";
}

$sql .= " ORDER BY tache.id DESC";

$stmt_taches = $pdo->query($sql);
$taches = $stmt_taches->fetchAll(PDO::FETCH_ASSOC);

// Rechercher un utilisateur ou une tâche par ID si un ID est saisi
if (!empty($searchId)) {
    $stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt_user->execute([':id' => $searchId]);
    $users = $stmt_user->fetchAll(PDO::FETCH_ASSOC);

    $stmt_tache = $pdo->prepare("SELECT tache.*, users.nom AS user_nom FROM tache LEFT JOIN users ON tache.fk_users = users.id WHERE tache.id = :id");
    $stmt_tache->execute([':id' => $searchId]);
    $taches_search = $stmt_tache->fetchAll(PDO::FETCH_ASSOC);
}

$page = isset($_GET['page']) ? $_GET['page'] : "accueil";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation des Tâches - App TACH</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge {
            font-size: 0.85rem;
        }
        .card {
            transition: transform 0.2s;
            border: none;
            border-radius: 10px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            min-height: 120px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        .search-container {
            max-width: 500px;
            margin: 0 auto 30px;
        }
        .filter-btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .active-filter {
            background-color: #0d6efd;
            color: white;
        }
        .tache-card {
            border-left: 4px solid;
        }
        .tache-card.terminée {
            border-left-color: #198754;
        }
        .tache-card.en-cours {
            border-left-color: #ffc107;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <!-- Titre de la page -->
        <h1 class="text-center mb-5">Visualisation des Tâches - App TACH</h1>

        <!-- Statistiques -->
        <h2 class="text-center my-5">Statistiques</h2>
        <div class="row g-4 justify-content-center">
            <!-- Nombre d'utilisateurs -->
            <div class="col-md-4 col-lg-3">
                <div class="card stats-card h-100 border-0">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="text-primary me-3">
                            <i class="fas fa-users stats-icon"></i>
                        </div>
                        <div class="text-end flex-grow-1">
                            <h6 class="text-muted mb-1">Utilisateurs</h6>
                            <h2 class="mb-0"><?= $users_count ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tâches terminées -->
            <div class="col-md-4 col-lg-3">
                <div class="card stats-card h-100 border-0">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="text-success me-3">
                            <i class="fas fa-check-circle stats-icon"></i>
                        </div>
                        <div class="text-end flex-grow-1">
                            <h6 class="text-muted mb-1">Tâches terminées</h6>
                            <h2 class="mb-0"><?= $taches_terminees ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tâches en cours -->
            <div class="col-md-4 col-lg-3">
                <div class="card stats-card h-100 border-0">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="text-warning me-3">
                            <i class="fas fa-tasks stats-icon"></i>
                        </div>
                        <div class="text-end flex-grow-1">
                            <h6 class="text-muted mb-1">Tâches en cours</h6>
                            <h2 class="mb-0"><?= $taches_en_cours ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <!-- Barre de recherche -->
        <div class="search-container">
            <form method="GET" action="" class="input-group">
                <input type="text" class="form-control" name="search_id" placeholder="Rechercher par ID (utilisateur ou tâche)..." value="<?= htmlspecialchars($searchId) ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search me-2"></i> Rechercher
                </button>
            </form>
        </div>

        <!-- Filtres pour les tâches -->
        <div class="text-center mb-4">
            <h4 class="mb-3">Filtrer les tâches</h4>
            <div class="btn-group" role="group">
                <a href="?filter=all<?= !empty($searchId) ? '&search_id='.htmlspecialchars($searchId) : '' ?>" class="btn btn-outline-primary filter-btn <?= $filter === 'all' ? 'active-filter' : '' ?>">
                    Toutes (<span class="badge bg-secondary"><?= $taches_terminees + $taches_en_cours ?></span>)
                </a>
                <a href="?filter=terminée<?= !empty($searchId) ? '&search_id='.htmlspecialchars($searchId) : '' ?>" class="btn btn-outline-success filter-btn <?= $filter === 'terminée' ? 'active-filter' : '' ?>">
                    Terminées (<span class="badge bg-success"><?= $taches_terminees ?></span>)
                </a>
                <a href="?filter=en cours<?= !empty($searchId) ? '&search_id='.htmlspecialchars($searchId) : '' ?>" class="btn btn-outline-warning filter-btn <?= $filter === 'en cours' ? 'active-filter' : '' ?>">
                    En cours (<span class="badge bg-warning"><?= $taches_en_cours ?></span>)
                </a>
            </div>
        </div>

        <!-- Affichage des résultats de recherche -->
        <?php if (!empty($searchId)): ?>
            <!-- Résultats pour les utilisateurs -->
            <?php if (!empty($users)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Résultat utilisateur (ID: <?= htmlspecialchars($searchId) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Prénom</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                                            <td><?= htmlspecialchars($user['nom']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Résultats pour les tâches -->
            <?php if (!empty($taches_search)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Résultat tâche (ID: <?= htmlspecialchars($searchId) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($taches_search as $tache): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tache['id']) ?></td>
                                            <td><?= htmlspecialchars($tache['titre']) ?></td>
                                            <td><?= htmlspecialchars($tache['description']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $tache['statut'] == 'terminée' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst(htmlspecialchars($tache['statut'])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($tache['user_nom'] ?? 'Inconnu') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php if (empty($users)): ?>
                    <div class="alert alert-warning text-center py-3">
                        Aucun résultat trouvé pour l'ID "<?= htmlspecialchars($searchId) ?>"
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Liste des tâches filtrées -->
        <h2 class="mb-3 text-center mt-4">
            <i class="fas fa-list me-2"></i>
            Liste des tâches <?= $filter === 'terminée' ? 'terminées' : ($filter === 'en cours' ? 'en cours' : '') ?>
        </h2>

        <?php if (empty($taches)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> Aucune tâche trouvée avec ce filtre.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($taches as $tache): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm tache-card <?= $tache['statut'] == 'terminée' ? 'terminée' : 'en-cours' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-3">
                                        <?= htmlspecialchars($tache['titre']) ?>
                                    </h5>
                                    <span class="badge bg-<?= $tache['statut'] == 'terminée' ? 'success' : 'warning' ?>">
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

                                <!-- Informations supplémentaires -->
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        ID: <?= htmlspecialchars($tache['id']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        <?= $tache['statut'] == 'terminée' ? 'Terminée' : 'En cours' ?>
                                    </span>
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
</body>
</html>
<?php include_once "../includes/footer.php" ?>
