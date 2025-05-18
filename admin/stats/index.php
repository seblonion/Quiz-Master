<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Récupérer les statistiques générales
$statistiques = obtenirStatistiquesGenerales();

// Récupérer les utilisateurs les plus actifs
$utilisateurs_actifs = obtenirUtilisateursActifs(10);

// Récupérer les données pour les graphiques
$database = new Database();
$db = $database->connect();

// Données pour le graphique des quiz par catégorie
$query = "SELECT c.nom, COUNT(qc.id) as total 
          FROM categories c
          LEFT JOIN quiz_completes qc ON c.id = qc.categorie_id
          GROUP BY c.id
          ORDER BY total DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$quiz_par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Données pour le graphique des quiz par difficulté
$query = "SELECT d.nom, COUNT(qc.id) as total 
          FROM difficultes d
          LEFT JOIN quiz_completes qc ON d.id = qc.difficulte_id
          GROUP BY d.id
          ORDER BY d.id";
$stmt = $db->prepare($query);
$stmt->execute();
$quiz_par_difficulte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Données pour le graphique de l'évolution dans le temps
$query = "SELECT 
            DATE_FORMAT(date_completion, '%Y-%m') as mois,
            COUNT(*) as total_quiz
          FROM quiz_completes
          GROUP BY DATE_FORMAT(date_completion, '%Y-%m')
          ORDER BY mois ASC
          LIMIT 12";
$stmt = $db->prepare($query);
$stmt->execute();
$evolution_mensuelle = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatage des données pour les graphiques JavaScript
$categories_labels = [];
$categories_data = [];
foreach ($quiz_par_categorie as $item) {
    $categories_labels[] = $item['nom'];
    $categories_data[] = (int)$item['total'];
}

$difficultes_labels = [];
$difficultes_data = [];
foreach ($quiz_par_difficulte as $item) {
    $difficultes_labels[] = $item['nom'];
    $difficultes_data[] = (int)$item['total'];
}

$evolution_labels = [];
$evolution_data = [];
foreach ($evolution_mensuelle as $item) {
    $date = new DateTime($item['mois'] . '-01');
    $evolution_labels[] = $date->format('m/Y');
    $evolution_data[] = (int)$item['total_quiz'];
}

// Inclure l'en-tête
$titre_page = "Statistiques de l'application";
include '../includes/header.php';
?>

<main class="stats-page">
    <div class="container">
        <section class="stats">
            <div class="section-header">
                <h1 class="section-title">Statistiques de l'application</h1>
                <div class="header-actions">
                    <button id="print-stats" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" />
                        </svg>
                        Imprimer
                    </button>
                </div>
            </div>

            <div class="stats-summary">
                <div class="stats-cards">
                    <div class="card stat-card" data-animation-delay="0.1s">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3>Utilisateurs</h3>
                            <p class="stat-value"><?= $statistiques['utilisateurs'] ?></p>
                        </div>
                    </div>
                    
                    <div class="card stat-card" data-animation-delay="0.2s">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3>Questions</h3>
                            <p class="stat-value"><?= $statistiques['questions'] ?></p>
                        </div>
                    </div>
                    
                    <div class="card stat-card" data-animation-delay="0.3s">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3>Quiz complétés</h3>
                            <p class="stat-value"><?= $statistiques['quiz_completes'] ?></p>
                        </div>
                    </div>
                    
                    <div class="card stat-card" data-animation-delay="0.4s">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 001 1h2a1 1 0 001-1v-5a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 001 1h2a1 1 0 001-1v-5a3 3 0 00-3-3V7a3 3 0 00-3-3H7a3 3 0 00-3 3v1a3 3 0 00-3 3v5a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3>Score moyen</h3>
                            <p class="stat-value"><?= $statistiques['score_moyen'] ?>%</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-charts">
                <div class="card chart-container" data-animation-delay="0.1s">
                    <h3 class="chart-title">Quiz par catégorie</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
                
                <div class="card chart-container" data-animation-delay="0.2s">
                    <h3 class="chart-title">Quiz par difficulté</h3>
                    <canvas id="difficultyChart"></canvas>
                </div>
                
                <div class="card chart-container wide" data-animation-delay="0.3s">
                    <h3 class="chart-title">Évolution mensuelle des quiz</h3>
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
            
            <div class="stats-tables">
                <div class="card table-container" data-animation-delay="0.1s">
                    <h3 class="table-title">Utilisateurs les plus actifs</h3>
                    <?php if (empty($utilisateurs_actifs)): ?>
                        <p class="text-muted">Aucune donnée disponible.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Quiz complétés</th>
                                        <th>Score moyen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs_actifs as $index => $utilisateur): ?>
                                        <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                            <td>
                                                <a href="../users/view.php?id=<?= $utilisateur['id'] ?>">
                                                    <?= htmlspecialchars($utilisateur['nom']) ?>
                                                </a>
                                            </td>
                                            <td><?= $utilisateur['total_quiz'] ?></td>
                                            <td><?= round($utilisateur['score_moyen']) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card table-container" data-animation-delay="0.2s">
                    <h3 class="table-title">Catégories populaires</h3>
                    <?php if (empty($quiz_par_categorie)): ?>
                        <p class="text-muted">Aucune donnée disponible.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Quiz complétés</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($quiz_par_categorie, 0, 5) as $index => $categorie): ?>
                                        <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                            <td><?= htmlspecialchars($categorie['nom']) ?></td>
                                            <td><?= $categorie['total'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card export-section" data-animation-delay="0.3s">
                <h3 class="section-title">Exporter les données</h3>
                <div class="export-buttons">
                    <a href="export.php?format=csv" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exporter en CSV
                    </a>
                    <a href="export.php?format=pdf" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exporter en PDF
                    </a>
                    <a href="export.php?format=excel" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exporter en Excel
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
    --text-color: #1f2937;
    --text-muted: #6b7280;
    --background-color: #f9fafb;
    --card-background: #ffffff;
    --border-color: #e5e7eb;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
    --font-sans: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Base Styles */
body {
    font-family: var(--font-sans);
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.stats-page {
    padding: 2rem 0 4rem;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 48px;
    height: 48px;
    background-color: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-icon svg {
    width: 24px;
    height: 24px;
    color: var(--primary-color);
}

.stat-content h3 {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-color);
}

/* Stats Charts */
.stats-charts {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.chart-container {
    padding: 1.5rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    animation: fadeIn 0.5s ease-out forwards;
}

.chart-container.wide {
    grid-column: 1 / -1;
}

.chart-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.chart-container canvas {
    max-height: 300px;
    width: 100%;
}

/* Stats Tables */
.stats-tables {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.table-container {
    padding: 1.5rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    animation: fadeIn 0.5s ease-out forwards;
}

.table-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.table-responsive {
    border-radius: var(--border-radius);
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--card-background);
    margin-bottom: 1rem;
}

.table thead {
    background-color: var(--primary-light);
}

.table th {
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    color: var(--text-color);
}

.table td {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.table tbody tr {
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
}

.table tbody tr:hover {
    background-color: var(--primary-light);
}

.text-muted {
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Export Section */
.export-section {
    padding: 1.5rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    animation: fadeIn 0.5s ease-out forwards;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 1rem;
    transition: var(--transition);
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.btn-outline {
    background-color: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 992px) {
    .stats-tables {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .stats-cards,
    .stats-charts {
        grid-template-columns: 1fr;
    }

    .export-buttons {
        flex-direction: column;
    }

    .table td, .table th {
        padding: 0.75rem;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .header-actions .btn,
    .export-buttons .btn {
        width: auto;
    }

    .table-responsive {
        overflow-x: auto;
    }
}
</style>

<!-- Inclure Chart.js pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des quiz par catégorie
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($categories_labels) ?>,
            datasets: [{
                data: <?= json_encode($categories_data) ?>,
                backgroundColor: [
                    '#ef4444', '#f97316', '#eab308', '#22c55e', 
                    '#3b82f6', '#8b5cf6', '#ec4899', '#6b7280', '#1e293b'
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
    
    // Graphique des quiz par difficulté
    const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
    const difficultyChart = new Chart(difficultyCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($difficultes_labels) ?>,
            datasets: [{
                label: 'Nombre de quiz',
                data: <?= json_encode($difficultes_data) ?>,
                backgroundColor: [
                    '#22c55e',
                    '#eab308',
                    '#ef4444'
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Graphique de l'évolution mensuelle
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($evolution_labels) ?>,
            datasets: [{
                label: 'Nombre de quiz',
                data: <?= json_encode($evolution_data) ?>,
                fill: false,
                borderColor: '#3b82f6',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
    
    // Fonction d'impression
    document.getElementById('print-stats').addEventListener('click', function() {
        window.print();
    });
});
</script>

<?php include '../includes/footer.php'; ?>