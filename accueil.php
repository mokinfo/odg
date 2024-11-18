<?php 
session_start();
if (!isset($_SESSION['nom_utilisateur'])) {
    header("Location: login.php");
    exit();
}
// Connexion à la base de données
$conn = new mysqli('132.148.183.180', 'a7zq6mn2qjza', 'Sna#2024@!!', 'Officier_de_garde');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
// Définir l'encodage de la connexion
$conn->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>
    Rapport Officier de garde
  </title>
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
  <style>
        .tab { display: none; }
    </style>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style général pour les onglets */
        .tab {
            display: none;
            animation: fadeEffect 0.5s;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
        }
        /* Animation de transition entre les onglets */
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        /* Style pour les titres des onglets */
        .tab h3 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 20px;
            color: #007bff;
        }
        /* Style des boutons */
        .btn {
            margin-top: 20px;
        }
        /* Espacement pour les étiquettes et champs de formulaire */
        label {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
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
              <a class="nav-link active bg-gradient-dark text-white" href="Accueil">
                <i class="material-symbols-rounded opacity-5">table_view</i>
                <span class="nav-link-text ms-1">Tables</span>
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
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Formulaire</li>
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
            <li class="mt-1">
              <a class="github-button" href="Logout" data-icon="octicon-sign-out" data-size="large"  aria-label="Logout">Déconnexion</a>
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
    <div class="container-fluid py-2">
      <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Officier de Garde</h6>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="container mt-5">
                    
                    <form method="POST" action="envoie.php">

                        <!-- Onglet 1 : Informations générales -->
                        <div class="tab">
                            <h3>Informations générales</h3>
                            <label>Date et heure de la garde :</label>
                            <input type="datetime-local" name="date_heure_garde" class="form-control" required>
                            <label>Nom de l'officier de garde :</label>
                            <input type="text" name="directeur_de_garde" class="form-control" required>
                            <label>Hôpital concerné :</label>
                            <input type="text" name="hopital_concerne" class="form-control" required>
                        </div>

                        <!-- Onglet 2 : Personnel -->
                        <div class="tab">
                            <h3>Personnel</h3>
                            <div id="personnel-container">
                                <div class="personnel-entry">
                                    <label>Nom :</label>
                                    <input type="text" name="personnel[0][nom]" class="form-control">
                                    <label>Fonction :</label>
                                    <input type="text" name="personnel[0][fonction]" class="form-control">
                                    <label>Shift :</label>
                                    <select name="personnel[0][shift]" class="form-control">
                                        <option value="08H00 – 20H00">08H00 – 20H00</option>
                                        <option value="20H00 – 08H00">20H00 – 08H00</option>
                                    </select>
                                    <label>Remplacement ou absence :</label>
                                    <select name="personnel[0][remplacement_ou_absence]" class="form-control">
                                        <option value="Absence">Absence</option>
                                        <option value="Remplacement">Remplacement</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" onclick="ajouterPersonnel()">Ajouter un autre personnel</button>
                        </div>
                        <!-- Onglet 3 : Incidents ou problèmes rencontrés -->
                        <div class="tab">
                            <h3>Incidents ou problèmes rencontrés</h3>
                            <label>Type d'incident/problème :</label>
                            <select name="incident_type" class="form-control">
                                <option value="">Sélectionner un type</option>
                                <option value="Incident médical">Incident médical</option>
                                <option value="Incident technique">Incident technique</option>
                                <option value="Incident administratif">Incident administratif</option>
                                <option value="Incident de sécurité">Incident de sécurité</option>
                                <option value="Problème">Problème</option>
                            </select>
                            <label>Description détaillée :</label>
                            <textarea name="incident_description" class="form-control" required></textarea>
                            <label>Heure et lieu de l'incident/problème :</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="time" name="incident_heure" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <select name="incident_lieu" class="form-control" multiple>
                                        <?php
                                        $services = $conn->query("SELECT nom_service FROM services ORDER BY nom_service");
                                        if ($services) {
                                            while ($row = $services->fetch_assoc()) {
                                                $nom_service = htmlspecialchars($row['nom_service'], ENT_QUOTES, 'UTF-8');
                                                echo "<option value='" . $nom_service . "'>" . $nom_service . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <label>Personnel impliqué :</label>
                            <textarea name="incident_personnel" class="form-control"></textarea>
                        </div>
                        <!-- Onglet 4 : Impacts sur les opérations -->
                        <div class="tab">
                            <h3>Impact sur les opérations</h3>
                            <label>Services affectés :</label>
                            <select name="services_affectes[]" class="form-control" multiple>
                                <?php
                                $services = $conn->query("SELECT nom_service FROM services ORDER BY nom_service");
                                if ($services) {
                                    while ($row = $services->fetch_assoc()) {
                                        $nom_service = htmlspecialchars($row['nom_service'], ENT_QUOTES, 'UTF-8');
                                        echo "<option value='" . $nom_service . "'>" . $nom_service . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <label>Conséquences sur les patients :</label>
                            <textarea name="consequences_sur_patients" class="form-control"></textarea>
                        </div>
                        <!-- Onglet 5 : Réponses et recommandations -->
                        <div class="tab">
                            <h3>Réponses et recommandations</h3>
                            <label>Actions prises :</label>
                            <textarea name="actions_prises" class="form-control"></textarea>
                            <label>Améliorations proposées :</label>
                            <textarea name="ameliorations_proposees" class="form-control"></textarea>
                        </div>
                        <!-- Onglet 6 : Activité médicale -->
                        <div class="tab">
                            <h3>Activité médicale</h3>
                            <label>Nombre de patients admis :</label>
                            <input type="number" name="nb_patients_admis" class="form-control">
                            <label>Nombre de patients sortis :</label>
                            <input type="number" name="nb_patients_sortis" class="form-control">
                            <label>Interventions importantes :</label>
                            <textarea name="interventions_importantes" class="form-control"></textarea>
                        </div>

                        <!-- Onglet 7 : Ressources utilisées -->
                        <div class="tab">
                            <h3>Ressources utilisées</h3>
                            <label>Médicaments et équipements :</label>
                            <textarea name="medicaments_equipements" class="form-control"></textarea>
                            <label>Besoins en ressources supplémentaires :</label>
                            <textarea name="besoin_ressources_sup" class="form-control"></textarea>
                        </div>

                        <!-- Onglet 8 : Communications -->
                        <div class="tab">
                            <h3>Communications</h3>
                            <label>Détails des communications :</label>
                            <textarea name="details_communications" class="form-control"></textarea>
                        </div>

                        <!-- Onglet 9 : Observations et recommandations -->
                        <div class="tab">
                            <h3>Observations et recommandations</h3>
                            <label>Points à améliorer :</label>
                            <textarea name="points_a_ameliorer" class="form-control"></textarea>
                            <label>Suggestions futures :</label>
                            <textarea name="suggestions_futures" class="form-control"></textarea>
                        </div>

                        <!-- Boutons de navigation -->
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" id="prevBtn" onclick="nextPrev(-1)" class="btn btn-secondary">Précédent</button>
                            <button type="button" id="nextBtn" onclick="nextPrev(1)" class="btn btn-primary">Suivant</button>
                            <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">Soumettre</button>
                        </div>
                    </form>

                </div>
            </div>
          </div>
        </div>
      </div>
      
    </div>

    </main>
    <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-info text-lg-start">
                Mokinfo <i class="fa fa-folder text-white" aria-hidden="true" ></i> 
                <a href="https://www.creative-tim.com" class="font-weight-bold text-purble" target="_blank"> © 2024</a>
              </div>
            </div>
          </div>
        </div>
    </footer>
  
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
    let currentTab = 0;
    let personnelCount = 1; // Compteur pour le personnel
    showTab(currentTab);

    function showTab(n) {
        let tabs = document.getElementsByClassName("tab");
        tabs[n].style.display = "block";
        document.getElementById("prevBtn").style.display = n == 0 ? "none" : "inline";
        document.getElementById("nextBtn").style.display = n == (tabs.length - 1) ? "none" : "inline";
        document.getElementById("submitBtn").style.display = n == (tabs.length - 1) ? "inline" : "none";
    }

    function nextPrev(n) {
        let tabs = document.getElementsByClassName("tab");
        tabs[currentTab].style.display = "none";
        currentTab += n;
        if (currentTab >= tabs.length) {
            document.getElementById("form").submit();
            return false;
        }
        showTab(currentTab);
    }

    function ajouterPersonnel() {
        let container = document.getElementById("personnel-container");
        let newEntry = document.createElement("div");
        newEntry.classList.add("personnel-entry");
        
        newEntry.innerHTML = `
            <hr>
            <label>Nom :</label>
            <input type="text" name="personnel[${personnelCount}][nom]" class="form-control">
            <label>Fonction :</label>
            <input type="text" name="personnel[${personnelCount}][fonction]" class="form-control">
            <label>Remplacement ou absence :</label>
            <textarea name="personnel[${personnelCount}][remplacement_ou_absence]" class="form-control"></textarea>
        `;
        
        container.appendChild(newEntry);
        personnelCount++;
    }
</script>
</body>

</html>