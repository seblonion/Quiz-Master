<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    header('Location: ../connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Récupérer les duels terminés
$completed_duels = getCompletedDuelsForUser($user_id, $limit, $offset);

// Récupérer le nombre total de duels pour la pagination
$database = new Database();
$db = $database->connect();

$query = "SELECT COUNT(*) as total FROM duels 
          WHERE (challenger_id = :user_id OR opponent_id = :user_id)
          AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_duels = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$total_pages = ceil($total_duels / $limit);

// Inclure l'en-tête
$titre_page = "Historique des duels";
include '../includes/header.php';
?>

<main class="history-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Historique des duels</h1>
            <p class="section-description">Consultez vos duels passés</p>
            <div class="header-actions">
                <a href="index.php" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Retour aux duels
                </a>
            </div>
        </div>

        <div class="history-container">
            <div class="card">
                <div class="history-filters">
                    <div class="filter-group">
                        <label for="filter-result">Résultat</label>
                        <select id="filter-result" class="form-control">
                            <option value="all" selected>Tous les résultats</option>
                            <option value="win">Victoires</option>
                            <option value="loss">Défaites</option>
                            <option value="draw">Égalités</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-type">Type de duel</label>
                        <select id="filter-type" class="form-control">
                            <option value="all" selected>Tous les types</option>
                            <option value="timed">Contre la montre</option>
                            <option value="accuracy">Précision</option>
                            <option value="mixed">Mixte</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($completed_duels)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🏆</div>
                        <h3>Aucun duel terminé</h3>
                        <p>Vous n'avez pas encore participé à des duels. Lancez un défi pour commencer!</p>
                        <a href="challenge.php" class="btn btn-primary">Lancer un défi</a>
                    </div>
                <?php else: ?>
                    <div class="duels-list">
                        <?php foreach ($completed_duels as $duel): ?>
                            <?php
                            // Récupérer les résultats de l'utilisateur connecté pour le score et la précision
                            $user_results = getDuelResults($duel['id'], $user_id);
                            $total_questions = count(getDuelQuestions($duel['id']));
                            $score = $user_results['score'] ?? 0;
                            $accuracy = $user_results['accuracy'] ?? 0;

                            // Log des données brutes pour débogage
                            error_log("History.php - Duel ID {$duel['id']} - User ID {$user_id} - Raw Results: " . json_encode($user_results));

                            // Si le score est 0, essayer de recalculer à partir de duel_answers
                            if ($score == 0) {
                                $stmt = $db->prepare("
                                    SELECT COUNT(*) as score
                                    FROM duel_answers da
                                    JOIN options o ON da.answer_id = o.id
                                    WHERE da.duel_id = :duel_id 
                                    AND da.user_id = :user_id 
                                    AND o.est_correcte = '1'
                                ");
                                $stmt->bindParam(':duel_id', $duel['id'], PDO::PARAM_INT);
                                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                $stmt->execute();
                                $recalculated_score = $stmt->fetch(PDO::FETCH_ASSOC);
                                $score = $recalculated_score['score'] ?? 0;
                                error_log("History.php - Duel ID {$duel['id']} - Recalculated Score: $score");

                                // Recalculer la précision si nécessaire
                                $accuracy = $total_questions > 0 ? round(($score / $total_questions) * 100, 1) : 0;
                            }

                            // Récupérer le completion_time directement depuis $duel (comme dans l'ancienne version)
                            $completion_time = isset($duel['completion_time']) && is_numeric($duel['completion_time']) 
                                ? round($duel['completion_time'], 1) 
                                : 'N/A';
                            ?>
                            <div class="duel-item">
                                <div class="duel-header">
                                    <div class="duel-players">
                                        <span class="player <?= $duel['challenger_id'] == $user_id ? 'current-user' : '' ?>">
                                            <?= htmlspecialchars($duel['challenger_nom']) ?>
                                        </span>
                                        <span class="vs">vs</span>
                                        <span class="player <?= $duel['opponent_id'] == $user_id ? 'current-user' : '' ?>">
                                            <?= htmlspecialchars($duel['opponent_nom']) ?>
                                        </span>
                                    </div>
                                    <div class="duel-result">
                                        <?php if ($duel['winner_id'] == $user_id): ?>
                                            <span class="result-badge win">Victoire</span>
                                        <?php elseif ($duel['winner_id'] && $duel['winner_id'] != $user_id): ?>
                                            <span class="result-badge loss">Défaite</span>
                                        <?php else: ?>
                                            <span class="result-badge draw">Égalité</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="duel-details">
                                    <div class="duel-info">
                                        <div class="info-item">
                                            <span class="info-label">Type</span>
                                            <span class="info-value">
                                                <span class="badge badge-<?= htmlspecialchars($duel['type']) ?>">
                                                    <?= htmlspecialchars(getDuelTypeLabel($duel['type'])) ?>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Catégorie</span>
                                            <span class="info-value"><?= htmlspecialchars($duel['categorie_nom'] ?? 'Toutes') ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Difficulté</span>
                                            <span class="info-value"><?= htmlspecialchars($duel['difficulte_id'] ?? 'Toutes') ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Date</span>
                                            <span class="info-value"><?= date('d/m/Y H:i', strtotime($duel['completed_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="duel-score">
                                        <div class="score-item">
                                            <span class="score-label">Score</span>
                                            <span class="score-value"><?= htmlspecialchars($score) ?>/<?= htmlspecialchars($total_questions) ?></span>
                                        </div>
                                        <div class="score-item">
                                            <span class="score-label">Précision</span>
                                            <span class="score-value"><?= htmlspecialchars($accuracy) ?>%</span>
                                        </div>
                                        <div class="score-item">
                                            <span class="score-label">Temps</span>
                                            <span class="score-value"><?= $completion_time !== 'N/A' ? $completion_time . ' sec' : 'N/A' ?></span>
                                        </div>
                                    </div>
                                    <div class="duel-actions">
                                        <a href="results.php?id=<?= htmlspecialchars($duel['id']) ?>" class="btn btn-outline btn-sm">Détails</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="pagination-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="pagination-icon">
                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                    </svg>
                                    Précédent
                                </a>
                            <?php endif; ?>
                            
                            <div class="pagination-pages">
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?= $i ?>" class="pagination-page <?= $i == $page ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="pagination-link">
                                    Suivant
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="pagination-icon">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --success-color: #10b981;
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

.history-page {
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
}

/* Card Styles */
.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

/* History Filters */
.history-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.filter-group {
    flex: 1;
    min-width: 200 сверх;
}

.filter-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-light);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.empty-state p {
    color: var(--text-muted);
    max-width: 400px;
    margin: 0 auto 1.5rem;
}

/* Duels List */
.duels-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.duel-item {
    background-color: var(--background-color);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.duel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    background-color: var(--primary-light);
    border-bottom: 1px solid var(--border-color);
}

.duel-players {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.player {
    font-weight: 500;
}

.player.current-user {
    font-weight: 700;
    color: var(--primary-color);
}

.vs {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.result-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.result-badge.win {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
}

.result-badge.loss {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
}

.result-badge.draw {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
}

.duel-details {
    padding: 1.25rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.duel-info {
    flex: 2;
    min-width: 250px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 0.875rem;
}

.duel-score {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.score-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.score-label {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.score-value {
    font-weight: 600;
}

.duel-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex: 1;
    min-width: 100px;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-timed {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-accuracy {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-mixed {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.pagination-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
}

.pagination-icon {
    width: 1rem;
    height: 1rem;
}

.pagination-pages {
    display: flex;
    gap: 0.5rem;
}

.pagination-page {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-color);
    text-decoration: none;
    transition: var(--transition);
}

.pagination-page:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.pagination-page.active {
    background-color: var(--primary-color);
    color: white;
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
    font-size: 0.75rem;
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

/* Responsive Design */
@media (max-width: 768px) {
    .duel-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .duel-details {
        flex-direction: column;
    }
    
    .duel-actions {
        justify-content: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterResult = document.getElementById('filter-result');
    const filterType = document.getElementById('filter-type');
    const duelItems = document.querySelectorAll('.duel-item');
    
    function applyFilters() {
        const resultFilter = filterResult.value;
        const typeFilter = filterType.value;
        
        duelItems.forEach(item => {
            let showItem = true;
            
            // Apply result filter
            if (resultFilter !== 'all') {
                const hasWin = item.querySelector('.result-badge.win');
                const hasLoss = item.querySelector('.result-badge.loss');
                const hasDraw = item.querySelector('.result-badge.draw');
                
                if (resultFilter === 'win' && !hasWin) showItem = false;
                if (resultFilter === 'loss' && !hasLoss) showItem = false;
                if (resultFilter === 'draw' && !hasDraw) showItem = false;
            }
            
            // Apply type filter
            if (typeFilter !== 'all' && showItem) {
                const typeBadge = item.querySelector(`.badge-${typeFilter}`);
                if (!typeBadge) showItem = false;
            }
            
            item.style.display = showItem ? 'block' : 'none';
        });
    }
    
    filterResult.addEventListener('change', applyFilters);
    filterType.addEventListener('change', applyFilters);
});
</script>

<?php
// Helper function for duel types
function getDuelTypeLabel($type) {
    switch($type) {
        case 'timed': return 'Contre la montre';
        case 'accuracy': return 'Précision';
        case 'mixed': return 'Mixte';
        default: return $type;
    }
}

include '../includes/footer.php';
?>