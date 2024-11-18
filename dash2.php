<?php
// Configuration de la base de données
$config = [
    'host' => 'localhost',
    'dbname' => 'Officier_de_garde',
    'user' => 'root',
    'pass' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonctions pour récupérer les données
function getIncidentsStats($pdo) {
    $query = "SELECT 
                DATE(rg.date_heure_garde) as date,
                COUNT(i.id) as nombre_incidents,
                i.type_incident
              FROM reporting_garde rg
              LEFT JOIN incidents i ON i.reporting_id = rg.id
              WHERE i.id IS NOT NULL
              GROUP BY DATE(rg.date_heure_garde), i.type_incident
              ORDER BY date DESC
              LIMIT 30";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getPatientsStats($pdo) {
    $query = "SELECT 
                DATE(rg.date_heure_garde) as date,
                SUM(p.nb_admis) as total_admis,
                SUM(p.nb_sortis) as total_sortis
              FROM reporting_garde rg
              LEFT JOIN patients p ON p.reporting_id = rg.id
              GROUP BY DATE(rg.date_heure_garde)
              ORDER BY date DESC
              LIMIT 30";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function getPersonnelAbsences($pdo) {
    $query = "SELECT 
                p.fonction,
                COUNT(*) as nombre_absences
              FROM personnel p
              WHERE p.remplacement_ou_absence IS NOT NULL
              GROUP BY p.fonction";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des données
$incidentsStats = getIncidentsStats($pdo);
$patientsStats = getPatientsStats($pdo);
$personnelAbsences = getPersonnelAbsences($pdo);

// Conversion des données pour les graphiques
$incidentsData = json_encode($incidentsStats);
$patientsData = json_encode($patientsStats);
$personnelData = json_encode($personnelAbsences);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Reporting des Gardes</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Dashboard de Reporting des Gardes</h1>
        
        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Admissions/Sorties</h3>
                <canvas id="patientsChart"></canvas>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Types d'Incidents</h3>
                <canvas id="incidentsChart"></canvas>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Absences du Personnel</h3>
                <canvas id="personnelChart"></canvas>
            </div>
        </div>
        
        <!-- Tableau des derniers incidents -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Derniers Incidents</h3>
            <table id="incidentsTable" class="w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Lieu</th>
                        <th>Services Affectés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT 
                                DATE(rg.date_heure_garde) as date,
                                i.type_incident,
                                i.description,
                                i.lieu_incident,
                                io.services_affectes
                             FROM incidents i
                             JOIN reporting_garde rg ON i.reporting_id = rg.id
                             LEFT JOIN impacts_operations io ON io.incident_id = i.id
                             ORDER BY rg.date_heure_garde DESC
                             LIMIT 50";
                    $incidents = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                    foreach($incidents as $incident): ?>
                    <tr>
                        <td><?= htmlspecialchars($incident['date']) ?></td>
                        <td><?= htmlspecialchars($incident['type_incident']) ?></td>
                        <td><?= htmlspecialchars($incident['description']) ?></td>
                        <td><?= htmlspecialchars($incident['lieu_incident']) ?></td>
                        <td><?= htmlspecialchars($incident['services_affectes']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialisation de DataTables
        $(document).ready(function() {
            $('#incidentsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });

        // Graphique des patients
        const patientsCtx = document.getElementById('patientsChart').getContext('2d');
        new Chart(patientsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($patientsStats, 'date')) ?>,
                datasets: [{
                    label: 'Admissions',
                    data: <?= json_encode(array_column($patientsStats, 'total_admis')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Sorties',
                    data: <?= json_encode(array_column($patientsStats, 'total_sortis')) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique des incidents
        const incidentsCtx = document.getElementById('incidentsChart').getContext('2d');
        new Chart(incidentsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_unique(array_column($incidentsStats, 'type_incident'))) ?>,
                datasets: [{
                    label: 'Nombre d\'incidents',
                    data: <?= json_encode(array_column($incidentsStats, 'nombre_incidents')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique du personnel
        const personnelCtx = document.getElementById('personnelChart').getContext('2d');
        new Chart(personnelCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($personnelAbsences, 'fonction')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($personnelAbsences, 'nombre_absences')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>