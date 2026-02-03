<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>App TACH – Tableau de bord</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- CSS personnalisé -->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">

  <!-- Style personnalisé -->
  <style>
    .sb-topnav {
      box-shadow: 0 0.15rem 1rem rgba(0, 0, 0, 0.1);
    }
    .navbar-brand {
      font-weight: 700;
    }
  </style>
</head>
<body>
  <!-- Navbar principale -->
  <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Lien de la marque (logo/nom) - à gauche -->
    <a class="navbar-brand ps-3" href="../pages/accueil.php">
      <i class="fas fa-robot me-2"></i>App TACH
    </a>

    <!-- Bouton pour afficher/masquer la sidebar (menu latéral) - à gauche -->
    <button
      class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0"
      id="sidebarToggle"
      aria-label="Toggle sidebar"
    >
      <i class="fas fa-bars"></i>
    </button>

    <!-- Éléments à droite de la navbar -->
    <div class="d-flex ms-auto">
      <!-- Menu utilisateur (dropdown) - à droite -->
      <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
          <a
            class="nav-link dropdown-toggle"
            id="navbarDropdownUser"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="fas fa-user-circle fa-fw"></i>
            <span class="d-none d-lg-inline-block ms-2">Anoo Lo</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
            <li>
              <a class="dropdown-item" href="profil.php">
                <i class="fas fa-user-cog me-2"></i>Mon profil
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="activites.php">
                <i class="fas fa-clock me-2"></i>Historique
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="deconnexion.php">
                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Scripts JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Gestion de la sidebar
      const sidebarToggle = document.getElementById('sidebarToggle');
      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
          e.preventDefault();
          document.body.classList.toggle('sb-sidenav-toggled');
        });
      }
    });
  </script>
</body>
</html>
