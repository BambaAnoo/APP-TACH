<?php
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../traitement/db.php";

// Récupérer les statistiques
$stmt_total = $pdo->query("SELECT COUNT(*) FROM tache");
$total_taches = $stmt_total->fetchColumn();

$stmt_terminees = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut = 'terminée'");
$taches_terminees = $stmt_terminees->fetchColumn();

$stmt_en_cours = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut = 'en cours'");
$taches_en_cours = $stmt_en_cours->fetchColumn();

$stmt_a_faire = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut = 'à faire'");
$taches_a_faire = $stmt_a_faire->fetchColumn();

$stmt_en_retard = $pdo->query("SELECT COUNT(*) FROM tache WHERE statut != 'terminée' AND date_limite < CURDATE()");
$taches_en_retard = $stmt_en_retard->fetchColumn();

$pourcentage_terminees = $total_taches > 0 ? round(($taches_terminees / $total_taches) * 100) : 0;

// Récupérer les tâches par priorité
$stmt_priorites = $pdo->query("
    SELECT priorite, COUNT(*) as count
    FROM tache
    GROUP BY priorite
");
$taches_par_priorite = $stmt_priorites->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tâches par responsable
$stmt_responsables = $pdo->query("
    SELECT u.id, CONCAT(u.prenom, ' ', u.nom) as nom_complet, COUNT(t.id) as count
    FROM users u
    LEFT JOIN tache t ON u.id = t.fk_users
    GROUP BY u.id
    HAVING count > 0
    ORDER BY count DESC
");
$taches_par_responsable = $stmt_responsables->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card { border-left: 4px solid; }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .progress { height: 20px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4"><i class="fas fa-chart-bar me-2"></i>Statistiques des Tâches</h1>

        <!-- Statistiques générales -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <h5 class="card-title">Tâches totales</h5>
                        <div class="d-flex align-items-center">
                            <div class="display-4 me-3"><?= $total_taches ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <h5 class="card-title">Tâches terminées</h5>
                        <div class="d-flex align-items-center">
                            <div class="display-4 me-3"><?= $taches_terminees ?></div>
                        </div>
                        <div class="progress mt-3">
                            <div class="progress-bar bg-success" style="width: <?= $pourcentage_terminees ?>%"></div>
                        </div>
                        <small class="text-muted mt-2"><?= $pourcentage_terminees ?>% des tâches</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card warning">
                    <div class="card-body">
                        <h5 class="card-title">Tâches en cours</h5>
                        <div class="d-flex align-items-center">
                            <div class="display-4 me-3"><?= $taches_en_cours ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card danger">
                    <div class="card-body">
                        <h5 class="card-title">Tâches à faire</h5>
                        <div class="d-flex align-items-center">
                            <div class="display-4 me-3"><?= $taches_a_faire ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tâches en retard -->
        <?php if ($taches_en_retard > 0): ?>
        <div class="alert alert-danger mb-4">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Tâches en retard</h4>
            <p>Il y a <?= $taches_en_retard ?> tâche<?= $taches_en_retard > 1 ? 's' : '' ?> en retard.</p>
            <a href="index.php?statut=en%20cours" class="btn btn-danger">Voir les tâches</a>
        </div>
        <?php endif; ?>

        <!-- Répartition par priorité -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-signal me-2"></i>Répartition par priorité</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $priorites = ['haute' => 0, 'moyenne' => 0, 'basse' => 0];
                    foreach ($taches_par_priorite as $item) {
                        $priorites[$item['priorite']] = $item['count'];
                    }
                    $total_priorites = array_sum($priorites);
                    ?>
                    <?php if ($total_priorites > 0): ?>
                        <div class="col-md-4">
                            <div class="card text-white bg-danger mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Haute priorité</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="display-4"><?= $priorites['haute'] ?></span>
                                        <span><?= round(($priorites['haute'] / $total_priorites) * 100) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-dark bg-warning mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Priorité moyenne</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="display-4"><?= $priorites['moyenne'] ?></span>
                                        <span><?= round(($priorites['moyenne'] / $total_priorites) * 100) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Basse priorité</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="display-4"><?= $priorites['basse'] ?></span>
                                        <span><?= round(($priorites['basse'] / $total_priorites) * 100) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">Aucune tâche enregistrée.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Répartition par responsable -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Tâches par responsable</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($taches_par_responsable)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Responsable</th>
                                    <th>Nombre de tâches</th>
                                    <th>% du total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($taches_par_responsable as $responsable):
                                    $pourcentage = round(($responsable['count'] / $total_taches) * 100);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($responsable['nom_complet']) ?></td>
                                        <td><?= $responsable['count'] ?></td>
                                        <td>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar" style="width: <?= $pourcentage ?>%"></div>
                                            </div>
                                            <small><?= $pourcentage ?>%</small>
                                        </td>
                                        <td>
                                            <a href="index.php?search=<?= urlencode($responsable['nom_complet']) ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Aucun responsable avec des tâches.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
