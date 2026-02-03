<?php 
$dossierPublic = "http://localhost/projetTaches/public";
    include_once "../includes/header.php";
    include_once "../includes/navbar.php";
    include_once "../includes/sidebar.php";

    $page = isset($_GET['page']) ?  $_GET['page'] : "accueil" ;

    if(file_exists("pages/$page.php")){
        include_once "pages/$page.php";
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Tach</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
<body class="">
    <form action="../traitement/action.php" method="post">
        <div class="container col-sm-7">
            <div class="card  mx-4 my-3 ">
                <div class="card-body bg-light shadow  rounded border border-primary">
                    <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger py-1 small"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                    <div class="bg-primary rounded px-0 py-1 ">
                        <p class="text-center small fw-bold">Ajout utilisateur</p>
                    </div>
                    <hr> 
                    <div class="mb-3">
                        <label for="prenomInput" class="form-label small fw-bold mx-2 my-0 ">Prenom : </label>
                        <input type="text" name="prenom" class="form-control form-control-sm" placeholder="Votre Prenom ">
                    </div>
                    <div class="mb-3 ">
                        <label for="nomInput" class="form-label small fw-bold mx-2 my-0">Nom : </label>
                        <input type="text" name="nom" class="form-control form-control-sm" placeholder="Votre Nom ">
                    </div> 
                    <div class="mb-3">
                        <label for="emailInput" class="form-label small fw-bold mx-2 my-0">Email : </label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="nom@exemple.com">
                    </div>
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label small fw-bold mx-2 my-0">Password : </label>
                        <input type="password"  name="password" class="form-control form-control-sm "  placeholder="Password">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-outline-primary fw-bold" name="inscrire" value="Submit">Ajouter</button>
                    </div>
                    <div class="text-center">
                        <p class=" fw-bold">j'ai deja un compte ?</p>
                        <a href="connexion.php" class="link-success fw-bold">se Connecter</a>   
                    </div>
                </div>   
            </div>
        </div>        
    </form>
    
</body>
</html>
<?php
    include_once "../includes/footer.php"

?>