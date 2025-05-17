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

// Récupérer la question
$question = obtenirQuestion($id);

if (!$question) {
    $_SESSION['message'] = 'Question non trouvée';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Inclure l'en-tête
$titre_page = "Détails de la question";
include '../includes/header.php';
?>

<main class="questions-page">
    <div class="container">
        <section class="questions-list">
            <div class="section-header">
                <h1 class="section-title">Détails de la question #<?= $id ?></h1>
                <div class="header-actions">
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
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

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="close-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <div class="question-details">
                <div class="question-meta card">
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($question['categorie_nom']) ?></p>
                    <p><strong>Difficulté :</strong> <?= htmlspecialchars($question['difficulte_nom']) ?></p>
                </div>
                
                <div class="question-text card">
                    <h3>Question</h3>
                    <p class="question-content"><?= htmlspecialchars($question['question']) ?></p>
                </div>
                
                <div class="question-options card">
                    <h3>Options</h3>
                    <ul class="options-list">
                        <?php foreach ($question['options'] as $index => $option): ?>
                            <li class="option-item <?= $option['est_correcte'] ? 'correct' : '' ?>" data-animation-delay="<?= $index * 0.1 ?>s">
                                <span class="option-text"><?= htmlspecialchars($option['texte']) ?></span>
                                <?php if ($option['est_correcte']): ?>
                                    <span class="correct-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="badge-icon">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Correcte
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Statistiques d'utilisation -->
                <?php
                $database = new Database();
                $db = $database->connect();
                
                // Nombre de fois que cette question a été répondue
                $query = "SELECT COUNT(*) as total FROM reponses_utilisateurs WHERE question_id = :question_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':question_id', $id);
                $stmt->execute();
                $total_reponses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Taux de réussite
                $query = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN o.est_correcte = 1 THEN 1 ELSE 0 END) as correctes
                          FROM reponses_utilisateurs ru
                          JOIN options o ON ru.option_id = o.id
                          WHERE ru.question_id = :question_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':question_id', $id);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $taux_reussite = $result['total'] > 0 ? round(($result['correctes'] / $result['total']) * 100) : 0;
                
                // Distribution des réponses
                $query = "SELECT 
                            o.texte,
                            o.est_correcte,
                            COUNT(*) as count
                          FROM reponses_utilisateurs ru
                          JOIN options o ON ru.option_id = o.id
                          WHERE ru.question_id = :question_id
                          GROUP BY o.id
                          ORDER BY count DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':question_id', $id);
                $stmt->execute();
                $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="question-stats card">
                    <h3>Statistiques</h3>
                    
                    <div class="stats-cards">
                        <div class="stat-card">
                            <h4>Réponses</h4>
                            <p class="stat-value"><?= $total_reponses ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h4>Taux de réussite</h4>
                            <p class="stat-value"><?= $taux_reussite ?>%</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($distribution)): ?>
                        <div class="response-distribution">
                            <h4>Distribution des réponses</h4>
                            <ul class="distribution-list">
                                <?php foreach ($distribution as $index => $item): ?>
                                    <li class="distribution-item <?= $item['est_correcte'] ? 'correct' : '' ?>" data-animation-delay="<?= $index * 0.1 ?>s">
                                        <span class="option-text"><?= htmlspecialchars($item['texte']) ?></span>
                                        <div class="distribution-bar-container">
                                            <div class="distribution-bar" style="width: <?= $total_reponses > 0 ? ($item['count'] / $total_reponses) * 100 : 0 ?>%"></div>
                                        </div>
                                        <span class="distribution-count"><?= $item['count'] ?></span>
                                        <span class="distribution-percent">(<?= $total_reponses > 0 ? round(($item['count'] / $total_reponses) * 100) : 0 ?>%)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Aucune donnée disponible sur la distribution des réponses.
                            <button type="button" class="close-alert">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
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

.questions-page {
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

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
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

.btn-icon, .btn-icon-left {
    width: 1rem;
    height: 1rem;
}

.btn-icon {
    margin-left: 0.5rem;
}

.btn-icon-left {
    margin-right: 0.5rem;
}

/* Card Styles */
.card {
    background-color: var(--card-background);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    animation: fadeIn 0.5s ease-out forwards;
}

/* Question Details */
.question-details {
    display: grid;
    gap: 1.5rem;
}

.question-content {
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Options List */
.options-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.option-item {
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--primary-light);
    transition: var(--transition);
}

.option-item.correct {
    background-color: rgba(16, 185, 129, 0.1);
    border-left: 4px solid var(--secondary-color);
}

.correct-badge {
    display: inline-flex;
    align-items: center;
    background-color: var(--secondary-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-icon {
    width: 0.875rem;
    height: 0.875rem;
    margin-right: 0.25rem;
}

.option-text {
    flex: 1;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background-color: var(--card-background);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0.5rem 0 0;
}

/* Response Distribution */
.distribution-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.distribution-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 8px;
    background-color: var(--primary-light);
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: var(--animation-delay, 0s);
}

.distribution-item.correct {
    background-color: rgba(16, 185, 129, 0.1);
}

.distribution-bar-container {
    flex-grow: 1;
    height: 10px;
    background-color: var(--border-color);
    border-radius: 5px;
    margin: 0 1rem;
    overflow: hidden;
}

.distribution-bar {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 5px;
    transition: width 0.5s ease;
}

.distribution-item.correct .distribution-bar {
    background-color: var(--secondary-color);
}

.distribution-count, .distribution-percent {
    flex: 0 0 10%;
    text-align: right;
    font-size: 0.875rem;
}

.distribution-percent {
    color: var(--text-muted);
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

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
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

    .stats-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .distribution-item {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .distribution-count, .distribution-percent {
        flex: 0 0 auto;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .header-actions .btn {
        width: auto;
    }

    .option-text {
        white-space: normal;
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