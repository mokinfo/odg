<?php
require_once 'config.php';

// Vérification supplémentaire des données
if (!isset($json_data)) {
    http_response_code(500);
    die("Erreur: Impossible de charger les données");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Dashboard de reporting des gardes hospitalières">
    
    
    <title>Rapport Officier de garde</title>
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/fontgoogle.css" />
    <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <script src="assets/js/fontawesome.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet" href="assets/css/css2.css" />
    <!-- CSS Files -->
    <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <style>
        .tab { display: none; }
    </style>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="assets/js/chart.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom CSS -->
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
        }
        
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>
</head>

<body class="bg-gray-200">
    <!-- Loading indicator -->
    <div id="loading" class="loading" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Chargement...</span>
        </div>
    </div>

    <!-- Error container -->
    <div id="error-container" class="error-message" style="display: none;"></div>

    <!-- Sidebar -->
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
              <a class="nav-link active bg-gradient-dark text-white" href="Dashboard">
                <i class="material-symbols-rounded opacity-5">dashboard</i>
                <span class="nav-link-text ms-1">Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="Accueil">
                <i class="material-symbols-rounded opacity-5">Table</i>
                <span class="nav-link-text ms-1">Données</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="Gestion">
                <i class="fa fa-users opacity-5"></i>
                <span class="nav-link-text ms-1">Gestion des utilisateurs</span>
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

    <!-- Main content -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="#">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <h5 class="mb-0">Bienvenue, <?php echo htmlspecialchars($_SESSION['nom_utilisateur']); ?></h5>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard content -->
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-4 pt-4">Dashboard de Reporting des Gardes</h1>
            
            <!-- Cartes de statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Carte Admissions/Sorties -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Admissions/Sorties</h3>
                    <div class="chart-container">
                        <canvas id="patientsChart"></canvas>
                    </div>
                </div>
                
                <!-- Carte Types d'Incidents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Types d'Incidents</h3>
                    <div class="chart-container">
                        <canvas id="incidentsChart"></canvas>
                    </div>
                </div>
                
                <!-- Carte Absences du Personnel -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Absences du Personnel</h3>
                    <div class="chart-container">
                        <canvas id="personnelChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tableau des derniers incidents -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-semibold">Liste des rapports</h3>
                    <?php   /* <a href="rapport_complet.php" class="px-4 py-2 bg-gradient-primary text-white rounded-lg hover:bg-blue-600 transition-all">
                        Voir tous les rapports
                    </a>*/?>
                </div>
                <div class="overflow-x-auto"><?php  
                    $conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');
                    $sql = $conn->query("SELECT * FROM reporting_garde ORDER by date_heure_garde DESC");
                    ?>
                    <table id="incidentsTable" class="w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Officier de garde</th>
                                <th>Lieu</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($data = $sql->fetch_array()){  ?>
                                <tr>
                                    <td><?php  echo $data['date_heure_garde']; ?></td>
                                    <td><?php  echo $data['directeur_de_garde']; ?></td>
                                    <td><?php  echo $data['hopital_concerne']; ?></td>
                                    <td><a href="Rapport?id=<?php echo $data['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye fa-lg"></i> Consulter
                                        </a>
                                        <a href="Modifier?id=<?php echo $data['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit fa-lg"></i> Modifier
                                        </a>
                                        <a href="Supprimer?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash fa-lg"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>


    <!-- Core JS Files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>

    <!-- Passage des données PHP vers JavaScript -->
    <script>
        var dashboardData = <?php echo $json_data; ?>;
    </script>
    
    <!-- Chargement du script principal -->
    <script src="dashboard.js"></script>
    <script>
$(document).ready(function() {
    $('a[href^="Supprimer"]').on('click', function(e) {
        e.preventDefault();
        
        const url = $(this).attr('href');
        const id = url.split('=')[1];
        
        if (!id) {
            Swal.fire('Erreur', 'ID non trouvé', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Confirmer la suppression',
            text: "Voulez-vous vraiment supprimer cet enregistrement ? Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: 'supprimer.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(response.error || 'Une erreur est survenue');
                        }
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        reject(`Erreur serveur: ${textStatus}`);
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'L\'enregistrement a été supprimé avec succès',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            }
        }).catch(error => {
            Swal.fire(
                'Erreur!',
                error,
                'error'
            );
        });
    });
});
</script>
</body>
</html>