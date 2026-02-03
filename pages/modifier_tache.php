<?php
include_once "includes/header.php";
include_once "includes/navbar.php";
include_once "traitement/db.php";

// Récupérer la tâche à modifier
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tache WHERE id = ?");
$stmt->execute([$id]);
$tache = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tache) {
    header("Location: index.php");
    exit();
}

// Récupérer la liste des utilisateurs
$stmt_users = $pdo->query("SELECT id, nom, prenom FROM users ORDER BY nom ASC");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $priorite = $_POST['priorite'];
    $date_limite = $_POST['date_limite'];
    $user_id = (int)$_POST['user_id'];

    $stmt = $pdo->prepare("UPDATE tache SET titre = ?, description = ?, priorite = ?, date_limite = ?, fk_users = ? WHERE id = ?");
    $stmt->execute([$titre, $description, $priorite, $date_limite, $user_id, $id]);

    // Conserver les paramètres de filtre
    $query_params = [];
    if (isset($_GET['statut'])) $query_params['statut'] = $_GET['statut'];
    if (isset($_GET['priorite'])) $query_params['priorite'] = $_GET['priorite'];
    if (isset($_GET['search'])) $query_params['search'] = $_GET['search'];

    header("Location: index.php" . (!empty($query_params) ? '?' . http_build_query($query_params) : ''));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier la tâche</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="modifier_tache.php?id=<?= $id ?>
                            <?= isset($_GET['statut']) ? "&statut=" . $_GET['statut'] : "" ?>
                            <?= isset($_GET['priorite']) ? "&priorite=" . $_GET['priorite'] : "" ?>
                            <?= isset($_GET['search']) ? "&search=" . $_GET['search'] : "" ?>">
                            <div class="mb-3">
                                <label class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" value="<?= htmlspecialchars($tache['titre']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($tache['description']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priorité <span class="text-danger">*</span></label>
                                <select class="form-select" name="priorite" required>
                                    <option value="basse" <?= $tache['priorite'] === 'basse' ? 'selected' : '' ?>>Basse</option>
                                    <option value="moyenne" <?= $tache['priorite'] === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                    <option value="haute" <?= $tache['priorite'] === 'haute' ? 'selected' : '' ?>>Haute</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date limite <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_limite" value="<?= $tache['date_limite'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Responsable <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_id" required>
                                    <option value="">-- Sélectionner un responsable --</option>
                                    <?php foreach ($users as $user):
                                        $selected = ($user['id'] == $tache['fk_users']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $user['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="fas fa-save me-2"></i>Mettre à jour
                                </button>
                                <a href="index.php
                                    <?= isset($_GET['statut']) ? "?statut=" . $_GET['statut'] : "" ?>
                                    <?= isset($_GET['priorite']) ? (isset($_GET['statut']) ? "&" : "?") . "priorite=" . $_GET['priorite'] : "" ?>
                                    <?= isset($_GET['search']) ? (isset($_GET['statut']) || isset($_GET['priorite']) ? "&" : "?") . "search=" . $_GET['search'] : "" ?>"
                                   class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
