<?php 

include "db.php"; 

// cette fonction permet de nettoyer les espaces et caracteres restante dans  buffer
function validation($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
                                //  AFFICHAGE 

$requete_liste = $pdo->query("SELECT * FROM tache ");
$affiche = $requete_liste->fetchAll(PDO::FETCH_ASSOC);

                                //  AJOUT 
if(isset($_POST['ajout'])){
    $titre = validation($_POST['titre']);
    $description = validation($_POST['description']);
    $statut = validation($_POST['statut']);

    // Préparation en cas d'erreur 
    $user_data = 'titre='.$titre.'&description='.$description;

    if(empty($titre)){
        header("Location: user.php?error=Le titre est requis&$user_data");
        exit();
    } elseif (empty($description)) {
        header("Location: user.php?error=La description est requise&$user_data");
        exit();
    } else {
                                // AJOUT RÉEL
        $requete = $pdo->prepare('INSERT INTO tache(titre, description, statut) VALUES(:titre, :description, :statut)');
        $requete->bindValue(':titre', $titre);
        $requete->bindValue(':description', $description);
        $requete->bindValue(':statut', $statut);
        
        header("Location: user.php?");
        exit();
    }
}
// SUPPRESSION -
if(isset($_GET['supprimer'])){
    $id = $_GET['supprimer'];
    $requete = $db->prepare('DELETE FROM tache WHERE id = :id');
    $requete->bindValue(':id', $id, PDO::PARAM_INT);
    $requete->execute();

    header("Location: user.php?");
    exit();
}
// SUPPRIMER 

if(isset($_GET['supprimer'])){
    $id = $_GET['supprimer'];
    
    $supp = $db->prepare('DELETE FROM tache WHERE id = :id');
    $supp->execute([':id' => $id]);
    
    header("Location: user.php?success=Tâche supprimée");
    exit();
}
//  MODIFIER 
if(isset($_POST['modifier'])){
    $id = $_POST['id']; 
    $titre = validation($_POST['titre']);
    $description = validation($_POST['description']);
    $statut = validation($_POST['statut']);

    $upd = $db->prepare('UPDATE tache SET titre = :t, description = :d, statut = :s WHERE id = :id');
    $upd->execute([
        ':t' => $titre, 
        ':d' => $description, 
        ':s' => $statut, 
        ':id' => $id
    ]);

    header("Location: user.php?success=Tâche mise à jour");
    exit();
}
$mode_edition = false;
$id_form = "";
$titre_form = "";
$desc_form = "";
$statut_form = "En Cours";

if(isset($_GET['editer'])){
    $mode_edition = true;
    $id_recherche = $_GET['editer'];

    foreach($affiche as $tache) {
        if($tache['id'] == $id_recherche) {
            $id_form = $tache['id'];
            $titre_form = $tache['titre'];
            $desc_form = $tache['description'];
            $statut_form = $tache['statut'];
        }
    }
}
?>
