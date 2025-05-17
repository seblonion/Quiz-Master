<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer l'utilisateur
$utilisateur = obtenirUtilisateur($id);

if (!$utilisateur) {
    $_SESSION['message'] = 'Utilisateur non trouvé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Récupérer les statistiques de l'utilisateur
$database = new Database();
$db = $database->connect();

// Quiz complétés
$query = "SELECT COUNT(*) as total FROM quiz_completes WHERE utilisateur_id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$total_quiz = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Score moyen
$query = "SELECT AVG(score / total * 100) as moyenne FROM quiz_completes WHERE utilisateur_id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$score_moyen = round($stmt->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0);

// Derniers quiz
$query = "SELECT qc.*, c.nom as categorie_nom, d.nom as difficulte_nom 
          FROM quiz_completes qc
          JOIN categories c ON qc.categorie_id = c.id
          JOIN difficultes d ON qc.difficulte_id = d.id
          WHERE qc.utilisateur_id = :id
          ORDER BY qc.date_completion DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$derniers_quiz = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Badges obtenus
$query = "SELECT b.*, bu.date_obtention, c.nom as categorie_nom 
          FROM badges_utilisateurs bu
          JOIN badges b ON bu.badge_id = b.id
          LEFT JOIN categories c ON b.categorie_id = c.id
          WHERE bu.utilisateur_id = :id
          ORDER BY bu.date_obtention DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclure l'en-tête
$titre_page = "Profil de " . htmlspecialchars($utilisateur['nom']);
include '../includes/header.php';
?>

<main class="user-profile-page">
    <div class="container">
        <section class="user-profile">
            <div class="section-header">
                <h1 class="section-title">
                    Profil : <?= htmlspecialchars($utilisateur['nom']) ?>
                    <?php if (isset($utilisateur['est_contributeur']) && $utilisateur['est_contributeur']): ?>
                        <span class="contributor-badge" title="Contributeur certifié">
                            <i class="certified-icon fas fa-check-circle"></i>
                            Contributeur
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="section-description">Détails et statistiques de l'utilisateur</p>
                <div class="header-actions">
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Modifier
                    </a>
                    <a href="index.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                        Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Informations de l'utilisateur -->
            <div class="card">
                <h2 class="card-title">Informations de l'utilisateur</h2>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td><?= $utilisateur['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Nom</th>
                                <td>
                                    <?= htmlspecialchars($utilisateur['nom']) ?>
                                    <?php if (isset($utilisateur['est_contributeur']) && $utilisateur['est_contributeur']): ?>
                                        <i class="certified-icon fas fa-check-circle" title="Contributeur certifié"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($utilisateur['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Inscrit le</th>
                                <td><?= date('d/m/Y H:i', strtotime($utilisateur['date_inscription'])) ?></td>
                            </tr>
                            <tr>
                                <th>Admin</th>
                                <td>
                                    <?php if ($utilisateur['est_admin']): ?>
                                        <span class="status status-success">Oui</span>
                                    <?php else: ?>
                                        <span class="status status-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Contributeur</th>
                                <td>
                                    <?php if (isset($utilisateur['est_contributeur']) && $utilisateur['est_contributeur']): ?>
                                        <span class="status status-contributor">Oui</span>
                                    <?php else: ?>
                                        <span class="status status-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card">
                <h2 class="card-title">Statistiques</h2>
                <div class="dashboard-cards">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h5 class="card-title">Quiz complétés</h5>
                            <p class="card-value"><?= $total_quiz ?></p>
                        </div>
                    </div>
                    <div class="card stat-card">
                        <div class="card-body">
                            <h5 class="card-title">Score moyen</h5>
                            <p class="card-value"><?= $score_moyen ?>%</p>
                        </div>
                    </div>
                    <div class="card stat-card">
                        <div class="card-body">
                            <h5 class="card-title">Badges obtenus</h5>
                            <p class="card-value"><?= count($badges) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernière activité -->
            <div class="card">
                <h2 class="card-title">Dernière activité</h2>
                <?php if (empty($derniers_quiz)): ?>
                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Aucun quiz complété
                        <button type="button" class="close-alert">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Catégorie</th>
                                    <th>Difficulté</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derniers_quiz as $index => $quiz): ?>
                                    <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                        <td><?= date('d/m/Y H:i', strtotime($quiz['date_completion'])) ?></td>
                                        <td><?= htmlspecialchars($quiz['categorie_nom']) ?></td>
                                        <td><?= htmlspecialchars($quiz['difficulte_nom']) ?></td>
                                        <td><?= $quiz['score'] ?> / <?= $quiz['total'] ?> (<?= round(($quiz['score'] / $quiz['total']) * 100) ?>%)</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Badges obtenus -->
            <?php if (!empty($badges)): ?>
                <div class="card">
                    <h2 class="card-title">Badges obtenus</h2>
                    <div class="badges-container">
                        <?php foreach ($badges as $index => $badge): ?>
                            <div class="badge-item" data-animation-delay="<?= $index * 0.1 ?>s">
                                <div class="badge-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color: <?= $badge['categorie_nom'] ? '#' . substr(md5($badge['categorie_nom']), 0, 6) : '#888' ?>">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1.586l-.293-.293a1 1 0 00-1.414 1.414L10 8.414l2.707-2.707a1 1 0 00-1.414-1.414L11 4.586V3a1 1 0 00-1-1zm4.707 3.293a1 1 0 00-1.414 0L10 8.586 6.707 5.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 000-1.414zM10 12a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="badge-info">
                                    <h4><?= htmlspecialchars($badge['nom']) ?></h4>
                                    <p><?= htmlspecialchars($badge['description']) ?></p>
                                    <small>Obtenu le <?= date('d/m/Y', strtotime($badge['date_obtention'])) ?></small>
                                    <?php if ($badge['categorie_nom']): ?>
                                        <small>Catégorie: <?= htmlspecialchars($badge['categorie_nom']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Contributions (si l'utilisateur est contributeur) -->
            <?php if (isset($utilisateur['est_contributeur']) && $utilisateur['est_contributeur']): ?>
                <div class="card">
                    <h2 class="card-title">Contributions</h2>
                    <?php
                    // Récupérer les quiz créés par l'utilisateur et approuvés
                    $query = "SELECT uq.*, c.nom as categorie_nom, c.couleur as categorie_couleur, 
                              d.nom as difficulte_nom, COUNT(qc.id) as completions 
                              FROM user_quizzes uq 
                              LEFT JOIN categories c ON uq.categorie_id = c.id 
                              LEFT JOIN difficultes d ON uq.difficulte_id = d.id 
                              LEFT JOIN quiz_completes qc ON uq.id = qc.quiz_id AND qc.type = 'community' 
                              WHERE uq.utilisateur_id = :utilisateur_id AND uq.status = 'approved' 
                              GROUP BY uq.id 
                              ORDER BY uq.date_creation DESC";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':utilisateur_id', $id);
                    $stmt->execute();
                    $contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <?php if (!empty($contributions)): ?>
                        <div class="contributions-list">
                            <?php foreach ($contributions as $contribution): ?>
                                <div class="contribution-item">
                                    <div class="contribution-header">
                                        <h4 class="contribution-title"><?= htmlspecialchars($contribution['titre']) ?></h4>
                                        <div class="contribution-meta">
                                            <span class="contribution-category" style="background-color: <?= $contribution['categorie_couleur'] ?>">
                                                <?= htmlspecialchars($contribution['categorie_nom']) ?>
                                            </span>
                                            <span class="contribution-difficulty">
                                                <?= htmlspecialchars($contribution['difficulte_nom']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="contribution-body">
                                        <p class="contribution-description"><?= htmlspecialchars($contribution['description']) ?></p>
                                        <div class="contribution-stats">
                                            <div class="stat">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="stat-icon">
                                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                </svg>
                                                <span><?= $contribution['completions'] ?> joueurs</span>
                                            </div>
                                            <div class="stat">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="stat-icon">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Publié le <?= date('d/m/Y', strtotime($contribution['date_creation'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Aucune contribution publiée
                            <button type="button" class="close-alert">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

.user-profile-page {
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
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Card Styles */
.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.card-body {
    padding: 1rem;
}

/* Dashboard Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    text-align: center;
    padding: 1rem;
}

.card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0.5rem 0 0;
}

/* Table Styles */
.table-responsive {
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--card-background);
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    color: var(--text-color);
    background-color: var(--primary-light);
}

.table td {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.table tbody tr {
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: var(--animation-delay, 0s);
}

.table tbody tr:hover {
    background-color: var(--primary-light);
}

/* Status Badges */
.status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--secondary-color);
}

.status-secondary {
    background-color: rgba(107, 114, 128, 0.1);
    color: var(--text-muted);
}

.status-contributor {
    background-color: rgba(29, 161, 242, 0.1);
    color: #1DA1F2;
}

/* Certified Icon */
.certified-icon {
    color: #1DA1F2;
    margin-left: 5px;
    font-size: 0.85em;
    vertical-align: middle;
}

.certified-icon:hover {
    transform: scale(1.2);
    transition: transform 0.2s ease;
}

/* Contributor Badge */
.contributor-badge {
    display: inline-flex;
    align-items: center;
    background-color: rgba(29, 161, 242, 0.1);
    color: #1DA1F2;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    margin-left: 1rem;
}

/* Badges Container */
.badges-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.badge-item {
    display: flex;
    align-items: center;
    background-color: var(--card-background);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: var(--animation-delay, 0s);
}

.badge-item:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.badge-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.badge-icon svg {
    width: 2rem;
    height: 2rem;
}

.badge-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.badge-info p {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.badge-info small {
    display: block;
    color: var(--text-muted);
    font-size: 0.75rem;
}

/* Contributions */
.contributions-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contribution-item {
    background-color: var(--background-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: var(--transition);
}

.contribution-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

.contribution-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.contribution-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
}

.contribution-meta {
    display: flex;
    gap: 0.5rem;
}

.contribution-category {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
}

.contribution-difficulty {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-size: 0.75rem;
    font-weight: 500;
}

.contribution-body {
    margin-bottom: 1rem;
}

.contribution-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0 0 1rem;
}

.contribution-stats {
    display: flex;
    gap: 1.5rem;
}

.stat {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.stat-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
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

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Alert Styles */
.alert {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    position: relative;
    font-size: 0.875rem;
}

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.alert-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
}

.close-alert {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: inherit;
    opacity: 0.7;
}

.close-alert:hover {
    opacity: 1;
}

.close-icon {
    width: 1rem;
    height: 1rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 992px) {
    .section-title {
        font-size: 1.75rem;
    }

    .dashboard-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .table td, .table th {
        padding: 0.75rem;
    }

    .badges-container {
        grid-template-columns: 1fr;
    }
    
    .section-title {
        flex-direction: column;
    }
    
    .contributor-badge {
        margin-left: 0;
        margin-top: 0.5rem;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .header-actions .btn {
        width: auto;
    }

    .header-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .contribution-header {
        flex-direction: column;
    }
    
    .contribution-meta {
        margin-top: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des alertes
    const closeAlerts = document.querySelectorAll('.close-alert');
    closeAlerts.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.style.display = 'none';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>