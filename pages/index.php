<?php
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../traitement/db.php";

// Récupérer les tâches avec filtres
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
$priorite_filter = isset($_GET['priorite']) ? $_GET['priorite'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT tache.*, users.nom, users.prenom FROM tache LEFT JOIN users ON tache.fk_users = users.id WHERE 1=1";

if (!empty($statut_filter)) {
    $sql .= " AND statut = :statut";
}
if (!empty($priorite_filter)) {
    $sql .= " AND priorite = :priorite";
}
if (!empty($search)) {
    $sql .= " AND (titre LIKE :search OR description LIKE :search)";
}

$sql .= " ORDER BY
    CASE WHEN statut = 'terminée' THEN 1 ELSE 0 END,
    CASE WHEN date_limite < CURDATE() AND statut != 'terminée' THEN 1 ELSE 0 END,
    date_limite";

$stmt = $pdo->prepare($sql);

if (!empty($statut_filter)) {
    $stmt->bindValue(':statut', $statut_filter);
}
if (!empty($priorite_filter)) {
    $stmt->bindValue(':priorite', $priorite_filter);
}
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bindValue(':search', $searchParam);
}

$stmt->execute();
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Changer le statut d'une tâche
if (isset($_GET['changer_statut']) && isset($_GET['tache_id'])) {
    $tache_id = (int)$_GET['tache_id'];
    $nouveau_statut = $_GET['changer_statut'];

    $stmt = $pdo->prepare("UPDATE tache SET statut = ? WHERE id = ?");
    $stmt->execute([$nouveau_statut, $tache_id]);

    // Rafraîchir la page
    header("Location: index.php" . (!empty($statut_filter) ? "?statut=$statut_filter" : "") .
           (!empty($priorite_filter) ? (strpos($_SERVER['QUERY_STRING'], 'statut') !== false ? "&" : "?") . "priorite=$priorite_filter" : "") .
           (!empty($search) ? (strpos($_SERVER['QUERY_STRING'], 'statut') !== false || strpos($_SERVER['QUERY_STRING'], 'priorite') !== false ? "&" : "?") . "search=$search" : ""));
    exit();
}

// Supprimer une tâche
if (isset($_GET['supprimer']) && isset($_GET['tache_id'])) {
    $tache_id = (int)$_GET['tache_id'];

    $stmt = $pdo->prepare("DELETE FROM tache WHERE id = ?");
    $stmt->execute([$tache_id]);

    // Rafraîchir la page
    header("Location: index.php" . (!empty($statut_filter) ? "?statut=$statut_filter" : "") .
           (!empty($priorite_filter) ? (strpos($_SERVER['QUERY_STRING'], 'statut') !== false ? "&" : "?") . "priorite=$priorite_filter" : "") .
           (!empty($search) ? (strpos($_SERVER['QUERY_STRING'], 'statut') !== false || strpos($_SERVER['QUERY_STRING'], 'priorite') !== false ? "&" : "?") . "search=$search" : ""));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tache-card { border-left: 4px solid; transition: all 0.3s; }
        .tache-card.haute { border-left-color: #dc3545; }
        .tache-card.moyenne { border-left-color: #ffc107; }
        .tache-card.basse { border-left-color: #198754; }
        .en-retard { background-color: rgba(255, 0, 0, 0.1); }
        .statut-badge { font-size: 0.8em; }
        .statut-a-faire { background-color: #6c757d; }
        .statut-en-cours { background-color: #0d6efd; }
        .statut-terminee { background-color: #198754; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4"><i class="fas fa-tasks me-2"></i>Liste des Tâches</h1>

        <!-- Filtres et recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="index.php" class="row g-3">
                    <div class="col-md-4">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="à faire" <?= $statut_filter === 'à faire' ? 'selected' : '' ?>>À faire</option>
                            <option value="en cours" <?= $statut_filter === 'en cours' ? 'selected' : '' ?>>En cours</option>
                            <option value="terminée" <?= $statut_filter === 'terminée' ? 'selected' : '' ?>>Terminée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="priorite" class="form-select">
                            <option value="">Toutes les priorités</option>
                            <option value="haute" <?= $priorite_filter === 'haute' ? 'selected' : '' ?>>Haute</option>
                            <option value="moyenne" <?= $priorite_filter === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                            <option value="basse" <?= $priorite_filter === 'basse' ? 'selected' : '' ?>>Basse</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <a href="index.php" class="btn btn-secondary w-100"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des tâches -->
        <?php if (empty($taches)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> Aucune tâche trouvée.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($taches as $tache):
                    $en_retard = ($tache['statut'] != 'terminée' && strtotime($tache['date_limite']) < time());
                    $priorite_class = $tache['priorite'];
                    $statut_class = 'statut-' . str_replace(' ', '-', strtolower($tache['statut']));
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm tache-card <?= $priorite_class ?> <?= $en_retard ? 'en-retard' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-3"><?= htmlspecialchars($tache['titre']) ?></h5>
                                    <span class="badge <?= $statut_class ?> statut-badge">
                                        <?= ucfirst(htmlspecialchars($tache['statut'])) ?>
                                    </span>
                                </div>

                                <p class="card-text mb-3"><?= nl2br(htmlspecialchars($tache['description'])) ?></p>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($tache['prenom'] . ' ' . $tache['nom']) ?><br>
                                        <i class="fas fa-calendar-alt me-1"></i> Créé le: <?= (new DateTime($tache['date_creation']))->format('d/m/Y H:i') ?><br>
                                        <i class="fas fa-calendar-check me-1"></i> Limite: <?= (new DateTime($tache['date_limite']))->format('d/m/Y') ?>
                                        <?= $en_retard ? '<span class="badge bg-danger ms-2">En retard</span>' : '' ?>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-<?= $priorite_class === 'haute' ? 'danger' : ($priorite_class === 'moyenne' ? 'warning' : 'success') ?>">
                                        Priorité: <?= ucfirst(htmlspecialchars($tache['priorite'])) ?>
                                    </span>
                                    <div>
                                        <?php if ($tache['statut'] != 'terminée'): ?>
                                            <a href="?changer_statut=<?= $tache['statut'] === 'à faire' ? 'en cours' : 'terminée' ?>&tache_id=<?= $tache['id'] ?>
                                                <?= !empty($statut_filter) ? "&statut=$statut_filter" : "" ?>
                                                <?= !empty($priorite_filter) ? "&priorite=$priorite_filter" : "" ?>
                                                <?= !empty($search) ? "&search=$search" : "" ?>"
                                               class="btn btn-sm btn-<?= $tache['statut'] === 'à faire' ? 'primary' : 'success' ?>">
                                                <?= $tache['statut'] === 'à faire' ? 'Démarrer' : 'Terminer' ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tache['statut'] != 'terminée'): ?>
                                            <a href="modifier_tache.php?id=<?= $tache['id'] ?>
                                                <?= !empty($statut_filter) ? "&statut=$statut_filter" : "" ?>
                                                <?= !empty($priorite_filter) ? "&priorite=$priorite_filter" : "" ?>
                                                <?= !empty($search) ? "&search=$search" : "" ?>"
                                               class="btn btn-sm btn-warning ms-2">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?supprimer=1&tache_id=<?= $tache['id'] ?>
                                            <?= !empty($statut_filter) ? "&statut=$statut_filter" : "" ?>
                                            <?= !empty($priorite_filter) ? "&priorite=$priorite_filter" : "" ?>
                                            <?= !empty($search) ? "&search=$search" : "" ?>"
                                           class="btn btn-sm btn-danger ms-2"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
