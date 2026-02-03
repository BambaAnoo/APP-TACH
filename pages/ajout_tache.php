<?php
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../traitement/db.php";

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

    $stmt = $pdo->prepare("INSERT INTO tache (titre, description, priorite, date_limite, fk_users) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $priorite, $date_limite, $user_id]);

    header("Location: index.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une tâche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter une nouvelle tâche</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="ajout_tache.php">
                            <div class="mb-3">
                                <label class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priorité <span class="text-danger">*</span></label>
                                <select class="form-select" name="priorite" required>
                                    <option value="basse">Basse</option>
                                    <option value="moyenne" selected>Moyenne</option>
                                    <option value="haute">Haute</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date limite <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_limite" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Responsable <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_id" required>
                                    <option value="">-- Sélectionner un responsable --</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>">
                                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                                <a href="index.php" class="btn btn-secondary">
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
