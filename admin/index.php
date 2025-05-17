<?php
require_once '../includes/db.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Récupérer quelques statistiques
$database = new Database();
$db = $database->connect();

// Nombre total d'utilisateurs
$query = "SELECT COUNT(*) as total FROM utilisateurs";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_utilisateurs = $result['total'];

// Nombre total de questions
$query = "SELECT COUNT(*) as total FROM questions";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_questions = $result['total'];

// Nombre total de quiz complétés
$query = "SELECT COUNT(*) as total FROM quiz_completes";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_quiz_completes = $result['total'];

// Derniers utilisateurs inscrits
$query = "SELECT id, nom, email, date_inscription FROM utilisateurs ORDER BY date_inscription DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$derniers_utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Derniers quiz complétés
$query = "SELECT qc.id, qc.score, qc.total, qc.date_completion, 
                 u.nom as utilisateur_nom, 
                 c.nom as categorie_nom, 
                 d.nom as difficulte_nom
          FROM quiz_completes qc
          JOIN utilisateurs u ON qc.utilisateur_id = u.id
          JOIN categories c ON qc.categorie_id = c.id
          JOIN difficultes d ON qc.difficulte_id = d.id
          ORDER BY qc.date_completion DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$derniers_quiz = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclure l'en-tête
$titre_page = "Tableau de bord";
include 'includes/header.php';
?>

<main class="dashboard-page">
    <div class="container">
        <section class="dashboard">
            <div class="section-header">
                <h1 class="section-title">Tableau de bord</h1>
                <p class="section-description">Vue d'ensemble des statistiques et activités récentes</p>
            </div>

            <div class="dashboard-cards">
                <div class="card stat-card" data-animation-delay="0.1s">
                    <h5 class="card-title">Utilisateurs</h5>
                    <p class="stat-value"><?= $total_utilisateurs ?></p>
                    <a href="users/index.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Gérer les utilisateurs
                    </a>
                </div>
                
                <div class="card stat-card" data-animation-delay="0.2s">
                    <h5 class="card-title">Questions</h5>
                    <p class="stat-value"><?= $total_questions ?></p>
                    <a href="quiz/index.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Gérer les questions
                    </a>
                </div>
                
                <div class="card stat-card" data-animation-delay="0.3s">
                    <h5 class="card-title">Quiz complétés</h5>
                    <p class="stat-value"><?= $total_quiz_completes ?></p>
                    <a href="#" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Voir les statistiques
                    </a>
                </div>
            </div>
            
            <div class="dashboard-tables">
                <div class="table-container card">
                    <h3 class="table-title">Derniers utilisateurs inscrits</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derniers_utilisateurs as $index => $utilisateur): ?>
                                    <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                        <td><?= htmlspecialchars($utilisateur['nom']) ?></td>
                                        <td><?= htmlspecialchars($utilisateur['email']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($utilisateur['date_inscription'])) ?></td>
                                        <td>
                                            <a href="users/edit.php?id=<?= $utilisateur['id'] ?>" class="btn btn-sm btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="users/index.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Voir tous les utilisateurs
                    </a>
                </div>
                
                <div class="table-container card">
                    <h3 class="table-title">Derniers quiz complétés</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Catégorie</th>
                                    <th>Difficulté</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derniers_quiz as $index => $quiz): ?>
                                    <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                        <td><?= htmlspecialchars($quiz['utilisateur_nom']) ?></td>
                                        <td><?= htmlspecialchars($quiz['categorie_nom']) ?></td>
                                        <td><?= htmlspecialchars($quiz['difficulte_nom']) ?></td>
                                        <td><?= $quiz['score'] ?> / <?= $quiz['total'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($quiz['date_completion'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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

.dashboard-page {
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

.section-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Dashboard Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background-color: var(--card-background);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

/* Dashboard Tables */
.dashboard-tables {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.table-container {
    background-color: var(--card-background);
    padding: 1.5rem;
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

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
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

.btn-icon {
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 992px) {
    .dashboard-tables {
        grid-template-columns: 1fr;
    }

    .section-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }

    .table td, .table th {
        padding: 0.75rem;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .table-container .btn {
        width: auto;
    }

    .table-responsive {
        overflow-x: auto;
    }
}
</style>

<?php include 'includes/footer.php'; ?>