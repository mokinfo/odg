<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>Gestion des Utilisateurs</title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="assets/css/fontgoogle.css" />
  <!-- Nucleo Icons -->
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="assets/js/fontawesome.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="assets/css/css2.css" />
  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  
  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  
  <!-- JavaScript -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-gray-200">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
        <div class="sidenav-header">
          <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
          <a class="navbar-brand px-4 py-3 m-0" href="#" target="_blank">
            <img src="assets/img/logo.jpg" width="150" height="102">
          </a>
        </div>
        <hr class="horizontal dark mt-0 mb-2">
        <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link text-dark" href="Dashboard">
                <i class="material-symbols-rounded opacity-5">dashboard</i>
                <span class="nav-link-text ms-1">Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="Rapports">
                <i class="material-symbols-rounded opacity-5">Table</i>
                <span class="nav-link-text ms-1">Rapports</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active bg-gradient-dark text-white" href="Utilisateurs">
                <i class="material-symbols-rounded opacity-5">people</i>
                <span class="nav-link-text ms-1">Utilisateurs</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="Logout">
                <i class="material-symbols-rounded opacity-5">assignment</i>
                <span class="nav-link-text ms-1">Se déconnecter</span>
              </a>
            </li>
          </ul>
        </div>
    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Utilisateurs</li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <div class="input-group input-group-outline">
                        </div>
                    </div>
                    <ul class="navbar-nav d-flex align-items-center  justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <h5 style="text-align:left;">Bienvenue, <?php echo htmlspecialchars($_SESSION['nom_utilisateur']); ?> !</h5>
                        </li>
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="container mx-auto px-4 ">
            <h1 class="text-3xl font-bold mb-4">Gestion des Utilisateurs</h1>
            
            <div class="bg-white rounded-lg shadow p-6">
                <table id="usersTable" class="w-full">
                    <thead>
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT 
                                    id, 
                                    nom_utilisateur,
                                    role
                                 FROM utilisateurs
                                 ORDER BY nom_utilisateur ASC";
                        $users = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                        foreach($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nom_utilisateur']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <a href="modifier-utilisateur.php?id=<?= $user['id'] ?>" class="text-blue-500 hover:text-blue-700">Modifier</a>
                                <a href="supprimer-utilisateur.php?id=<?= $user['id'] ?>" class="text-red-500 hover:text-red-700">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="creer-utilisateur.php" class="btn btn-primary mt-4">Ajouter un utilisateur</a>
            </div>
        </div>

    </main>

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script>
        // Initialisation de DataTables
        $(document).ready(function() {
            $('#usersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 10,
                order: [[0, 'asc']]
            });
        });
    </script>
</body>

</html>
