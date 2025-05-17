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
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$period = isset($_GET['period']) ? $_GET['period'] : 'all';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Récupérer le classement
$leaderboard = getDuelLeaderboard($limit, $offset, $period, $type);

// Débogage : Vérifier les données brutes du classement
error_log("Données du classement (leaderboard.php) : " . print_r($leaderboard, true));

// Récupérer le nombre total de joueurs pour la pagination
$database = new Database();
$db = $database->connect();

$query = "SELECT COUNT(*) as total FROM duel_leaderboard WHERE total_duels > 0";
$params = [];

if ($period == 'month') {
    $query .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($period == 'week') {
    $query .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
}

if ($type != 'all') {
    $query .= " AND preferred_duel_type = :type";
    $params[':type'] = $type;
}

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_players = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$total_pages = ceil($total_players / $limit);

// Récupérer le rang de l'utilisateur actuel
$user_rank = getUserDuelRank($user_id, $period, $type);

// Débogage : Vérifier les données du rang de l'utilisateur
error_log("Données du rang utilisateur (leaderboard.php) : " . print_r($user_rank, true));

// Recalculer avg_accuracy pour $user_rank si nécessaire
if ($user_rank && (!isset($user_rank['avg_accuracy']) || $user_rank['avg_accuracy'] == 0)) {
    $stmt = $db->prepare("
        SELECT 
            dr.duel_id,
            dr.score,
            (SELECT COUNT(*) FROM duel_questions dq WHERE dq.duel_id = dr.duel_id) as total_questions
        FROM duel_results dr
        JOIN duels d ON d.id = dr.duel_id
        WHERE dr.user_id = :user_id
        AND d.status = 'completed'
        " . ($period == 'month' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" : "") . "
        " . ($period == 'week' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)" : "") . "
        " . ($type != 'all' ? "AND d.type = :type" : "") . "
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if ($type != 'all') {
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_accuracy = 0;
    $duel_count = 0;
    foreach ($results as $result) {
        $total_questions = $result['total_questions'];
        $score = $result['score'];
        if ($total_questions > 0) {
            $accuracy = ($score / $total_questions) * 100;
            $total_accuracy += $accuracy;
            $duel_count++;
        }
    }
    $user_rank['avg_accuracy'] = $duel_count > 0 ? round($total_accuracy / $duel_count, 1) : 0;
    error_log("Leaderboard.php - User ID {$user_id} - Recalculated user avg_accuracy: {$user_rank['avg_accuracy']}");
}

// Recalculer les draws pour $user_rank si nécessaire
if ($user_rank && (!isset($user_rank['draws']) || $user_rank['draws'] == 0)) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as draws
        FROM duels d
        WHERE d.status = 'completed'
        AND d.winner_id IS NULL
        AND (d.challenger_id = :user_id OR d.opponent_id = :user_id)
        " . ($period == 'month' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" : "") . "
        " . ($period == 'week' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)" : "") . "
        " . ($type != 'all' ? "AND d.type = :type" : "") . "
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if ($type != 'all') {
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    }
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_rank['draws'] = $result['draws'] ?? 0;
    error_log("Leaderboard.php - User ID {$user_id} - Recalculated user draws: {$user_rank['draws']}");
}

// Ajouter une logique de recalcul pour les données du classement si nécessaire
foreach ($leaderboard as &$player) {
    // Recalculer draws si nécessaire
    if (!isset($player['draws']) || $player['draws'] == 0) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as draws
            FROM duels d
            WHERE d.status = 'completed'
            AND d.winner_id IS NULL
            AND (d.challenger_id = :user_id OR d.opponent_id = :user_id)
            " . ($period == 'month' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" : "") . "
            " . ($period == 'week' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)" : "") . "
            " . ($type != 'all' ? "AND d.type = :type" : "") . "
        ");
        $stmt->bindParam(':user_id', $player['id'], PDO::PARAM_INT);
        if ($type != 'all') {
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $player['draws'] = $result['draws'] ?? 0;
        error_log("Leaderboard.php - User ID {$player['id']} - Recalculated draws: {$player['draws']}");
    }

    // Recalculer avg_accuracy si nécessaire
    if (!isset($player['avg_accuracy']) || $player['avg_accuracy'] == 0) {
        $stmt = $db->prepare("
            SELECT 
                dr.duel_id,
                dr.score,
                (SELECT COUNT(*) FROM duel_questions dq WHERE dq.duel_id = dr.duel_id) as total_questions
            FROM duel_results dr
            JOIN duels d ON d.id = dr.duel_id
            WHERE dr.user_id = :user_id
            AND d.status = 'completed'
            " . ($period == 'month' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" : "") . "
            " . ($period == 'week' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)" : "") . "
            " . ($type != 'all' ? "AND d.type = :type" : "") . "
        ");
        $stmt->bindParam(':user_id', $player['id'], PDO::PARAM_INT);
        if ($type != 'all') {
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_accuracy = 0;
        $duel_count = 0;
        foreach ($results as $result) {
            $total_questions = $result['total_questions'];
            $score = $result['score'];
            if ($total_questions > 0) {
                $accuracy = ($score / $total_questions) * 100;
                $total_accuracy += $accuracy;
                $duel_count++;
            }
        }
        $player['avg_accuracy'] = $duel_count > 0 ? round($total_accuracy / $duel_count, 1) : 0;
        error_log("Leaderboard.php - User ID {$player['id']} - Recalculated avg_accuracy: {$player['avg_accuracy']}");
    }

    // Recalculer avg_completion_time si nécessaire
    if (!isset($player['avg_completion_time']) || $player['avg_completion_time'] == 0) {
        $stmt = $db->prepare("
            SELECT AVG(dr.completion_time) as avg_completion_time
            FROM duel_results dr
            JOIN duels d ON d.id = dr.duel_id
            WHERE dr.user_id = :user_id
            AND d.status = 'completed'
            " . ($period == 'month' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" : "") . "
            " . ($period == 'week' ? "AND d.completed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)" : "") . "
            " . ($type != 'all' ? "AND d.type = :type" : "") . "
        ");
        $stmt->bindParam(':user_id', $player['id'], PDO::PARAM_INT);
        if ($type != 'all') {
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $player['avg_completion_time'] = isset($result['avg_completion_time']) && is_numeric($result['avg_completion_time']) ? round($result['avg_completion_time'], 1) : 0;
        error_log("Leaderboard.php - User ID {$player['id']} - Recalculated avg_completion_time: {$player['avg_completion_time']}");
    }
}
unset($player); // Nettoyer la référence après la boucle

// Inclure l'en-tête
$titre_page = "Classement des duels";
include '../includes/header.php';
?>

<main class="leaderboard-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Classement des duels</h1>
            <p class="section-description">Découvrez les meilleurs joueurs de duels</p>
            <div class="header-actions">
                <a href="index.php" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Retour aux duels
                </a>
            </div>
        </div>

        <div class="leaderboard-container">
            <div class="card">
                <div class="leaderboard-filters">
                    <div class="filter-group">
                        <label for="filter-period">Période</label>
                        <select id="filter-period" class="form-control">
                            <option value="all" <?= $period == 'all' ? 'selected' : '' ?>>Tout le temps</option>
                            <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Ce mois</option>
                            <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>Cette semaine</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter-type">Type de duel</label>
                        <select id="filter-type" class="form-control">
                            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>>Tous les types</option>
                            <option value="timed" <?= $type == 'timed' ? 'selected' : '' ?>>Contre la montre</option>
                            <option value="accuracy" <?= $type == 'accuracy' ? 'selected' : '' ?>>Précision</option>
                            <option value="mixed" <?= $type == 'mixed' ? 'selected' : '' ?>>Mixte</option>
                        </select>
                    </div>
                </div>

                <?php if ($user_rank): ?>
                    <div class="user-rank-card">
                        <div class="user-rank-info">
                            <div class="user-rank-position">
                                <span class="rank-number"><?= htmlspecialchars($user_rank['rank']) ?></span>
                                <span class="rank-label">Votre rang</span>
                            </div>
                            <div class="user-rank-stats">
                                <div class="stat-group">
                                    <span class="stat-value"><?= htmlspecialchars($user_rank['wins']) ?></span>
                                    <span class="stat-label">Victoires</span>
                                </div>
                                <div class="stat-group">
                                    <span class="stat-value"><?= htmlspecialchars($user_rank['win_percentage']) ?>%</span>
                                    <span class="stat-label">% Victoires</span>
                                </div>
                                <div class="stat-group">
                                    <span class="stat-value"><?= is_numeric($user_rank['avg_accuracy']) ? round($user_rank['avg_accuracy'], 1) . '%' : 'N/A' ?></span>
                                    <span class="stat-label">Précision</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="leaderboard-table-container">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th class="rank-col">Rang</th>
                                <th class="player-col">Joueur</th>
                                <th class="duels-col">Duels</th>
                                <th class="wins-col">Victoires</th>
                                <th class="losses-col">Défaites</th>
                                <th class="draws-col">Égalités</th>
                                <th class="winrate-col">% Victoires</th>
                                <th class="accuracy-col">Précision</th>
                                <th class="time-col">Temps moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leaderboard)): ?>
                                <tr>
                                    <td colspan="9" class="no-data">Aucune donnée disponible pour les filtres sélectionnés</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leaderboard as $index => $player): ?>
                                    <tr class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?> <?= $player['id'] == $user_id ? 'current-user' : '' ?>">
                                        <td class="rank-col">
                                            <?php if ($index == 0): ?>
                                                <i class="fas fa-trophy" style="color: #FFD700;"></i>
                                            <?php elseif ($index == 1): ?>
                                                <i class="fas fa-trophy" style="color: #C0C0C0;"></i>
                                            <?php elseif ($index == 2): ?>
                                                <i class="fas fa-trophy" style="color: #CD7F32;"></i>
                                            <?php else: ?>
                                                <?= $offset + $index + 1 ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="player-col">
                                            <a href="../profil.php?id=<?= htmlspecialchars($player['id']) ?>" class="player-link">
                                                <?= htmlspecialchars($player['nom']) ?>
                                                <?php if (isset($player['est_contributeur']) && $player['est_contributeur']): ?>
                                                    <i class="certified-icon fas fa-check-circle" title="Contributeur certifié"></i>
                                                <?php endif; ?>
                                            </a>
                                        </td>
                                        <td class="duels-col"><?= htmlspecialchars($player['total_duels']) ?></td>
                                        <td class="wins-col"><?= htmlspecialchars($player['wins']) ?></td>
                                        <td class="losses-col"><?= htmlspecialchars($player['losses']) ?></td>
                                        <td class="draws-col"><?= htmlspecialchars($player['draws']) ?></td>
                                        <td class="winrate-col"><?= htmlspecialchars($player['win_percentage']) ?>%</td>
                                        <td class="accuracy-col"><?= isset($player['avg_accuracy']) && is_numeric($player['avg_accuracy']) ? round($player['avg_accuracy'], 1) : 'N/A' ?>%</td>
                                        <td class="time-col"><?= isset($player['avg_completion_time']) && is_numeric($player['avg_completion_time']) ? round($player['avg_completion_time'], 1) . ' sec' : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&period=<?= htmlspecialchars($period) ?>&type=<?= htmlspecialchars($type) ?>" class="pagination-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="pagination-icon">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                </svg>
                                Précédent
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-pages">
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>&period=<?= htmlspecialchars($period) ?>&type=<?= htmlspecialchars($type) ?>" class="pagination-page <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&period=<?= htmlspecialchars($period) ?>&type=<?= htmlspecialchars($type) ?>" class="pagination-link">
                                Suivant
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="pagination-icon">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
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

.leaderboard-page {
    padding: 2rem 0 4rem;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 2rem;
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

/* Leaderboard Filters */
.leaderboard-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-muted);
}

.form-control {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background-color: var(--card-background);
    color: var(--text-color);
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

/* User Rank Card */
.user-rank-card {
    background-color: var(--primary-light);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(79, 70, 229, 0.2);
}

.user-rank-info {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.user-rank-position {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-right: 2rem;
    border-right: 1px solid rgba(79, 70, 229, 0.2);
}

.rank-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.rank-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.user-rank-stats {
    display: flex;
    gap: 2rem;
}

.stat-group {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-color);
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Leaderboard Table */
.leaderboard-table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.leaderboard-table th {
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-weight: 600;
    text-align: left;
    padding: 1rem;
    border-bottom: 2px solid var(--primary-color);
}

.leaderboard-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.leaderboard-table tbody tr {
    transition: var(--transition);
}

.leaderboard-table tbody tr:hover {
    background-color: var(--primary-light);
}

.leaderboard-table tbody tr.top-1 {
    background-color: rgba(255, 215, 0, 0.1);
    border-left: 4px solid #FFD700;
}

.leaderboard-table tbody tr.top-2 {
    background-color: rgba(192, 192, 192, 0.1);
    border-left: 4px solid #C0C0C0;
}

.leaderboard-table tbody tr.top-3 {
    background-color: rgba(205, 127, 50, 0.1);
    border-left: 4px solid #CD7F32;
}

.leaderboard-table .current-user {
    background-color: rgba(79, 70, 229, 0.05);
    font-weight: 500;
}

.rank-col {
    width: 60px;
    text-align: center;
}

.player-col {
    min-width: 200px;
}

.player-link {
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.player-link:hover {
    color: var(--primary-color);
}

.certified-icon {
    color: #4f46e5;
    font-size: 0.85em;
}

.duels-col, .wins-col, .losses-col, .draws-col {
    width: 80px;
    text-align: center;
}

.winrate-col, .accuracy-col, .time-col {
    width: 100px;
    text-align: center;
}

.no-data {
    text-align: center;
    color: var(--text-muted);
    padding: 2rem !important;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.pagination-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
}

.pagination-link:hover {
    background-color: var(--primary-color);
    color: white;
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
    background-color: var(--card-background);
    color: var(--text-color);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.pagination-page:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.pagination-page.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
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
@media (max-width: 992px) {
    .user-rank-info {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .user-rank-position {
        padding-right: 0;
        padding-bottom: 1rem;
        border-right: none;
        border-bottom: 1px solid rgba(79, 70, 229, 0.2);
        width: 100%;
        flex-direction: row;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .user-rank-stats {
        width: 100%;
        justify-content: space-around;
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.75rem;
    }
    
    .leaderboard-table th, 
    .leaderboard-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .rank-col {
        width: 40px;
    }
    
    .player-col {
        min-width: 120px;
    }
    
    .duels-col, .wins-col, .losses-col, .draws-col,
    .winrate-col, .accuracy-col, .time-col {
        width: auto;
    }
}

@media (max-width: 576px) {
    .leaderboard-filters {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .user-rank-stats {
        flex-wrap: wrap;
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtres
    const periodFilter = document.getElementById('filter-period');
    const typeFilter = document.getElementById('filter-type');
    
    // Appliquer les filtres
    function applyFilters() {
        const period = periodFilter.value;
        const type = typeFilter.value;
        window.location.href = `leaderboard.php?period=${period}&type=${type}`;
    }
    
    // Écouter les changements de filtres
    periodFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
});
</script>

<?php include '../includes/footer.php'; ?>