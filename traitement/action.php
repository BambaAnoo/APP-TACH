<?php
    include "db.php";

    function validation($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
}
    $prenom = filter_input(INPUT_POST, 'prenom');
    $nom = filter_input(INPUT_POST, 'nom');
    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');
    
    // INSERTION
    if(isset($_POST['inscrire'])){
    $prenom = validation($_POST['prenom']);
    $nom = validation($_POST['nom']);
    $email = validation($_POST['email']);
    $password = validation($_POST['password']);

    // Préparation en cas d'erreur 
    $user_data = '&prenom='.$prenom.'&nom='.$nom.'&email='.$email.'&password='.$password;

    if(empty($prenom)){
        header("Location: inscription.php?error=Le Prenom est requis&$user_data");
    } elseif (empty($nom)) {
        header("Location: inscription.php?error=Le Nom est requise&$user_data");
        exit();
    } elseif (empty($email)) {
        header("Location: inscription.php?error=L' Email est requise&$user_data");
        exit();
    } elseif (empty($password)) {
        header("Location: inscription.php?error=Le Password est requise&$user_data");
        exit();
    } 
    else {
                                // AJOUT RÉEL
        $sql = "INSERT INTO users (nom, prenom, email, password) 
            VALUES (:nom, :prenom, :email, MD5(:password))";
    $requete = $pdo->prepare($sql);
    $requete->bindValue(':prenom', $prenom);
    $requete->bindValue(':nom', $nom);
    $requete->bindValue(':email', $email);
    $requete->bindValue(':password', $password);
    $requete->execute();
        
        header("Location: accueil.php?success=Utilisateur ajouté avec succès");
        exit();
    }
}

    // AFFICHAGE
    $requete = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $affiche = $requete->fetchAll(PDO::FETCH_ASSOC);

    // CONNEXION

    

    
 
