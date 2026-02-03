<?php
// Inclure le fichier de connexion à la base de données
include 'db.php';

// Récupérer la liste des utilisateurs pour vérifier leur existence
$stmt_users = $pdo->query("SELECT id, nom FROM users");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// 1. AJOUTER UNE TÂCHE
if (isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $statut = $_POST['statut'];
    $user_id = (int)$_POST['user_id']; // Récupérer l'ID de l'utilisateur depuis le formulaire

    // Vérifier que l'utilisateur sélectionné existe
    $user_exists = false;
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            $user_exists = true;
            break;
        }
    }

    if (!$user_exists) {
        die("Erreur : L'utilisateur sélectionné n'existe pas.");
    }

    // Préparer et exécuter la requête d'insertion avec fk_users
    $stmt = $pdo->prepare("INSERT INTO tache (titre, description, statut, fk_users) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $statut, $user_id]);

    // Rediriger vers la page principale
    header("Location: ../pages/indexTache.php");
    exit;
}

// 2. SUPPRIMER UNE TÂCHE
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];

    // Vérifier que l'ID est valide
    if ($id <= 0) {
        die("Erreur : ID de tâche invalide.");
    }

    // Préparer et exécuter la requête de suppression
    $stmt = $pdo->prepare("DELETE FROM tache WHERE id = ?");
    $stmt->execute([$id]);

    // Rediriger vers la page principale
    header("Location: indexTache.php");
    exit;
}

// 3. RÉCUPÉRER UNE TÂCHE POUR MODIFICATION
$tache_a_modifier = null;
if (isset($_GET['modifier'])) {
    $id = (int)$_GET['modifier'];

    // Vérifier que l'ID est valide
    if ($id <= 0) {
        die("Erreur : ID de tâche invalide.");
    }

    // Préparer et exécuter la requête de sélection
    $stmt = $pdo->prepare("
        SELECT tache.*, users.nom AS user_nom
        FROM tache
        LEFT JOIN users ON tache.fk_users = users.id
        WHERE tache.id = ?
    ");
    $stmt->execute([$id]);
    $tache_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. MODIFIER UNE TÂCHE
if (isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id = (int)$_POST['id'];
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $statut = $_POST['statut'];
    $user_id = (int)$_POST['user_id']; // Récupérer l'ID de l'utilisateur depuis le formulaire

    // Vérifier que l'ID de la tâche et l'utilisateur sont valides
    if ($id <= 0) {
        die("Erreur : ID de tâche invalide.");
    }

    // Vérifier que l'utilisateur sélectionné existe
    $user_exists = false;
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            $user_exists = true;
            break;
        }
    }

    if (!$user_exists) {
        die("Erreur : L'utilisateur sélectionné n'existe pas.");
    }

    // Préparer et exécuter la requête de mise à jour avec fk_users
    $stmt = $pdo->prepare("UPDATE tache SET titre = ?, description = ?, statut = ?, fk_users = ? WHERE id = ?");
    $stmt->execute([$titre, $description, $statut, $user_id, $id]);

    // Rediriger vers la page principale
    header("Location: ../public/indexTache.php");
    exit;
}

// 5. RÉCUPÉRER LA LISTE DES TÂCHES
$stmt = $pdo->query("
    SELECT tache.*, users.nom AS user_nom
    FROM tache
    LEFT JOIN users ON tache.fk_users = users.id
    ORDER BY tache.id DESC
");
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
