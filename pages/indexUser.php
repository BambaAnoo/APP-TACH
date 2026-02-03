<?php
$dossierPublic = "http://localhost/projetTaches/public";
include_once "../includes/header.php";
include_once "../includes/navbar.php";
include_once "../includes/sidebar.php";

// Inclure le fichier de connexion à la base de données
include '../traitement/db.php';

// Initialisation des variables
$user = [
    'id' => null,
    'prenom' => '',
    'nom' => '',
    'email' => '',
];
$mode_edition = false; // Mode ajout par défaut

// Récupérer la liste des utilisateurs (sauf Ripperlo@gmail.com)
$requete = $pdo->query("SELECT * FROM users WHERE email != 'Ripperlo@gmail.com' ORDER BY id ASC");
$users = $requete->fetchAll(PDO::FETCH_ASSOC);

// Si on est en mode modification
if (isset($_GET['modifier'])) {
    $id = (int)$_GET['modifier'];
    $requete = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $requete->execute([':id' => $id]);
    $user = $requete->fetch(PDO::FETCH_ASSOC) ?: $user; // Si utilisateur non trouvé, garde les valeurs par défaut
    $mode_edition = true;
}

// Ajouter ou modifier un utilisateur
if (isset($_POST['enregistrer'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $prenom = htmlspecialchars($_POST['prenom']);
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($id) {
        // Mode modification
        $sql = "UPDATE users SET prenom = :prenom, nom = :nom, email = :email" .
               (!empty($password) ? ", password = :password" : "") .
               " WHERE id = :id";
        $params = [
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':id' => $id
        ];
        if (!empty($password)) {
            $params[':password'] = $password;
        }
        $requete = $pdo->prepare($sql);
        $requete->execute($params);
    } else {
        // Mode ajout
        $requete = $pdo->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (:prenom, :nom, :email, :password)");
        $requete->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':password' => $password ?: password_hash('default', PASSWORD_DEFAULT)
        ]);
    }

    // Utilisation de JavaScript pour la redirection pour éviter les problèmes de headers
    echo "<script>window.location.href='indexUser.php';</script>";
    exit();
}

// Supprimer un utilisateur
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];

    // Vérifier si l'utilisateur existe avant de supprimer
    $check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $check->execute([':id' => $id]);
    if ($check->rowCount() > 0) {
        $supprimer = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $supprimer->execute([':id' => $id]);
    }

    // Utilisation de JavaScript pour la redirection
    echo "<script>window.location.href='indexUser.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <style>
        .form-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Formulaire d'ajout/modification -->
        <div class="form-container col-md-8 offset-md-2">
            <h3 class="text-center mb-4">
                <i class="fas <?= $mode_edition ? "fa-edit" : "fa-user-plus" ?> me-2"></i>
                <?= $mode_edition ? "Modifier un utilisateur" : "Ajouter un utilisateur" ?>
            </h3>

            <form method="POST" action="">
                <!-- Champ caché pour l'ID en mode modification -->
                <?php if ($mode_edition): ?>
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom"
                               value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom"
                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= $mode_edition ? "Nouveau mot de passe (laisser vide pour ne pas changer)" : "Mot de passe" ?></label>
                    <input type="password" class="form-control" name="password"
                           <?= !$mode_edition ? "required" : "" ?>>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success" name="enregistrer">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <?php if ($mode_edition): ?>
                        <a href="indexUser.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <hr>

        <div class="text-center my-3 fw-bold border col-sm-4 rounded shadow bg-info mx-auto p-2">
            <p class="mb-0">Liste des Utilisateurs</p>
        </div>

        <table class="table table-striped my-5 border shadow table-hover">
            <thead class="table-warning">
                <tr>
                    <th>#</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-light table-group-divider table-bordered border-dark">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id']) ?></td>
                            <td><?= htmlspecialchars($u['prenom']) ?></td>
                            <td><?= htmlspecialchars($u['nom']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <a href="indexUser.php?modifier=<?= $u['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </a>
                                <a href="indexUser.php?supprimer=<?= $u['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <i class="fas fa-trash me-1"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun utilisateur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include_once "../includes/footer.php" ?>
</body>
</html>
