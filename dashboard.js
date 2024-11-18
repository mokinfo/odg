// Configuration globale des couleurs
const CHART_COLORS = {
    primary: '#1f77b4',
    secondary: '#ff7f0e',
    success: '#2ca02c',
    danger: '#d62728',
    warning: '#ffbb33',
    info: '#17a2b8',
    background: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf']
};

// Gestionnaire d'erreurs global
function handleError(error, context) {
    console.error(`Erreur dans ${context}:`, error);
    const errorContainer = document.getElementById('error-container');
    errorContainer.textContent = `Une erreur est survenue lors de ${context}. Veuillez rafraîchir la page.`;
    errorContainer.style.display = 'block';
}

// Fonction pour afficher/masquer l'indicateur de chargement
function toggleLoading(show) {
    const loader = document.getElementById('loading');
    loader.style.display = show ? 'flex' : 'none';
}

// Configuration commune pour les graphiques
const commonChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                boxWidth: 10
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleColor: '#ffffff',
            bodyColor: '#ffffff',
            cornerRadius: 4
        }
    }
};

// Initialisation du graphique des patients
function initPatientsChart() {
    try {
        const ctx = document.getElementById('patientsChart').getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Admissions', 'Sorties'],
                datasets: [{
                    label: 'Nombre de patients',
                    data: [
                        dashboardData.patients.total_admis,
                        dashboardData.patients.total_sortis
                    ],
                    backgroundColor: [CHART_COLORS.primary, CHART_COLORS.secondary],
                    borderWidth: 0
                }]
            },
            options: {
                ...commonChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    } catch (error) {
        handleError(error, 'l\'initialisation du graphique des patients');
    }
}

// Initialisation du graphique des incidents
function initIncidentsChart() {
    try {
        const ctx = document.getElementById('incidentsChart').getContext('2d');
        const labels = dashboardData.incidents.map(item => item.type_incident);
        const data = dashboardData.incidents.map(item => item.count);

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: CHART_COLORS.background,
                    borderWidth: 1
                }]
            },
            options: {
                ...commonChartOptions,
                cutout: '60%'
            }
        });
    } catch (error) {
        handleError(error, 'l\'initialisation du graphique des incidents');
    }
}

// Initialisation du graphique du personnel
function initPersonnelChart() {
    try {
        const ctx = document.getElementById('personnelChart').getContext('2d');
        const absences = dashboardData.personnel.absences;
        const total = dashboardData.personnel.total_personnel;
        const presents = total - absences;

        return new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Présents', 'Absents'],
                datasets: [{
                    data: [presents, absences],
                    backgroundColor: [CHART_COLORS.success, CHART_COLORS.danger],
                    borderWidth: 1
                }]
            },
            options: commonChartOptions
        });
    } catch (error) {
        handleError(error, 'l\'initialisation du graphique du personnel');
    }
}

// Initialisation de la table des incidents
function initIncidentsTable() {
    try {
        return $('#incidentsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            order: [[0, 'desc']],
            responsive: true,
            columnDefs: [
                { className: 'dt-body-center', targets: '_all' }
            ],
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
                $('.dataTables_length select').addClass('form-control');
            }
        });
    } catch (error) {
        handleError(error, 'l\'initialisation du tableau des incidents');
    }
}

// Initialisation du scrollbar personnalisé
function initScrollbar() {
    try {
        if (typeof Scrollbar !== 'undefined') {
            if (document.querySelector('#sidenav-scrollbar')) {
                Scrollbar.init(document.querySelector('#sidenav-scrollbar'), {
                    damping: '0.5'
                });
            }
        }
    } catch (error) {
        console.warn('Scrollbar initialization skipped:', error);
    }
}

// Gestionnaire d'événements pour le menu mobile
function initMobileMenu() {
    const iconNavbarSidenav = document.getElementById('iconNavbarSidenav');
    const iconSidenav = document.getElementById('iconSidenav');
    const sidenav = document.getElementById('sidenav-main');

    if (iconNavbarSidenav) {
        iconNavbarSidenav.addEventListener('click', toggleSidenav);
    }

    if (iconSidenav) {
        iconSidenav.addEventListener('click', toggleSidenav);
    }

    function toggleSidenav() {
        sidenav.classList.toggle('show');
    }
}

// Fonction principale d'initialisation
async function initDashboard() {
    toggleLoading(true);
    try {
        // Vérification des données
        if (!dashboardData || dashboardData.error) {
            throw new Error('Les données du dashboard sont invalides');
        }

        // Initialisation des composants
        const charts = {
            patients: await initPatientsChart(),
            incidents: await initIncidentsChart(),
            personnel: await initPersonnelChart()
        };

        const table = initIncidentsTable();
        initScrollbar();
        initMobileMenu();

        // Gestion du redimensionnement
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                Object.values(charts).forEach(chart => {
                    if (chart) chart.resize();
                });
            }, 250);
        });

    } catch (error) {
        handleError(error, 'l\'initialisation du dashboard');
    } finally {
        toggleLoading(false);
    }
}

// Démarrage de l'application quand le DOM est chargé
document.addEventListener('DOMContentLoaded', initDashboard);