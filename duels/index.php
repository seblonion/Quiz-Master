<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    header('Location: /quizmaster/connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Récupérer les invitations en attente
$pending_invitations = getPendingDuelInvitations($user_id);

// Récupérer les duels actifs
$active_duels = getActiveDuelsForUser($user_id);

// Récupérer les duels terminés récemment
$completed_duels = getCompletedDuelsForUser($user_id, 5);

// Récupérer les statistiques de l'utilisateur
$user_stats = getUserDuelStatistics($user_id);

// Recalculer avg_accuracy si nécessaire
if (!isset($user_stats['avg_accuracy']) || $user_stats['avg_accuracy'] == 0) {
    $database = new Database();
    $db = $database->connect();
    
    $stmt = $db->prepare("
        SELECT 
            dr.duel_id,
            dr.score,
            (SELECT COUNT(*) FROM duel_questions dq WHERE dq.duel_id = dr.duel_id) as total_questions
        FROM duel_results dr
        JOIN duels d ON d.id = dr.duel_id
        WHERE dr.user_id = :user_id
        AND d.status = 'completed'
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
    $user_stats['avg_accuracy'] = $duel_count > 0 ? round($total_accuracy / $duel_count, 1) : 0;
    
    error_log("Index.php - User ID {$user_id} - Recalculated avg_accuracy: {$user_stats['avg_accuracy']}");
}

// Récupérer le classement
$leaderboard = getDuelLeaderboard(5, 0);

// Récupérer les duels populaires (les plus joués récemment)
$popular_duels = getPopularDuels(3);

// Inclure l'en-tête
$titre_page = "Duels - Affrontez d'autres joueurs";
include '../includes/header.php';
?>

<main class="duels-page">
    <div class="container">
        <!-- Hero Section -->
        <section class="duels-hero">
            <div class="hero-content">
                <h1 class="hero-title">Duels de Quiz</h1>
                <p class="hero-description">Affrontez d'autres joueurs en temps réel et prouvez que vous êtes le meilleur !</p>
                <div class="hero-actions">
                    <a href="challenge.php" class="btn btn-primary btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Lancer un défi
                    </a>
                    <a href="leaderboard.php" class="btn btn-outline btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        Voir le classement
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="/QuizMaster/assets/images/vide.png" alt="Duels de Quiz" class="hero-img">
            </div>
        </section>

        <!-- Notifications Section -->
        <?php if (!empty($pending_invitations)): ?>
        <section class="duels-notifications">
            <div class="notification-card">
                <div class="notification-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                    </svg>
                </div>
                <div class="notification-content">
                    <h3 class="notification-title">Vous avez <?= count($pending_invitations) ?> invitation<?= count($pending_invitations) > 1 ? 's' : '' ?> en attente !</h3>
                    <p class="notification-message">Des joueurs vous ont défié. Acceptez leurs défis pour commencer à jouer.</p>
                </div>
                <div class="notification-action">
                    <a href="#invitations" class="btn btn-primary btn-sm">Voir les invitations</a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <div class="dashboard-grid">
            <!-- Left Column -->
            <div class="dashboard-column">
                <!-- User Stats Card -->
                <section class="dashboard-card stats-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                            </svg>
                            Mes statistiques
                        </h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?= $user_stats['total_duels'] ?? 0 ?></div>
                            <div class="stat-label">Duels</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user_stats['wins'] ?? 0 ?></div>
                            <div class="stat-label">Victoires</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user_stats['losses'] ?? 0 ?></div>
                            <div class="stat-label">Défaites</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user_stats['draws'] ?? 0 ?></div>
                            <div class="stat-label">Égalités</div>
                        </div>
                    </div>
                    
                    <?php if (isset($user_stats['total_duels']) && $user_stats['total_duels'] > 0): ?>
                        <div class="stats-details">
                            <div class="progress-stat">
                                <div class="progress-label">
                                    <span>Taux de victoire</span>
                                    <span><?= round(($user_stats['wins'] / $user_stats['total_duels']) * 100, 1) ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= round(($user_stats['wins'] / $user_stats['total_duels']) * 100, 1) ?>%"></div>
                                </div>
                            </div>
                            <div class="progress-stat">
                                <div class="progress-label">
                                    <span>Précision moyenne</span>
                                    <span><?= $user_stats['avg_accuracy'] ?? 0 ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $user_stats['avg_accuracy'] ?? 0 ?>%"></div>
                                </div>
                            </div>
                            <div class="progress-stat">
                                <div class="progress-label">
                                    <span>Temps moyen</span>
                                    <span><?= $user_stats['avg_completion_time'] ?? 0 ?> sec</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user_stats['achievements'])): ?>
                        <div class="achievements-section">
                            <h3 class="section-subtitle">Mes réalisations</h3>
                            <div class="achievements-list">
                                <?php foreach ($user_stats['achievements'] as $type => $count): ?>
                                    <div class="achievement-badge" title="<?= getAchievementTitle($type) ?>">
                                        <?= getAchievementIcon($type) ?>
                                        <span class="achievement-count"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-footer">
                        <a href="history.php" class="btn btn-link">Voir mon historique complet</a>
                    </div>
                </section>

                <!-- Active Duels -->
                <?php if (!empty($active_duels)): ?>
                <section class="dashboard-card active-duels-card" id="active-duels">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            Duels en cours
                            <span class="badge badge-primary"><?= count($active_duels) ?></span>
                        </h2>
                    </div>
                    <div class="active-duels-list">
                        <?php 
                        $duel_count = 0;
                        $has_more_duels = count($active_duels) > 5;
                        ?>
                        <!-- Afficher les 5 premiers duels -->
                        <?php foreach ($active_duels as $duel): ?>
                            <?php
                            $duel_count++;
                            // Vérifier si le duel a des questions disponibles
                            $questions = getDuelQuestions($duel['id']);
                            $has_questions = !empty($questions);
                            ?>
                            <?php if ($duel_count <= 5): ?>
                                <div class="duel-item">
                                    <div class="duel-details">
                                        <div class="duel-header">
                                            <h3 class="duel-title">
                                                <?= htmlspecialchars($duel['challenger_nom']) ?> vs <?= htmlspecialchars($duel['opponent_nom']) ?>
                                            </h3>
                                            <span class="duel-type badge badge-<?= $duel['type'] ?>">
                                                <?= getDuelTypeLabel($duel['type']) ?>
                                            </span>
                                        </div>
                                        <div class="duel-info">
                                            <?php if ($duel['categorie_nom']): ?>
                                                <span class="duel-category">Catégorie: <?= htmlspecialchars($duel['categorie_nom']) ?></span>
                                            <?php endif; ?>
                                            <?php if ($duel['difficulte_nom']): ?>
                                                <span class="duel-difficulty">Difficulté: <?= htmlspecialchars($duel['difficulte_nom']) ?></span>
                                            <?php endif; ?>
                                            <span class="duel-questions"><?= $duel['question_count'] ?> questions</span>
                                            <?php if ($duel['time_limit']): ?>
                                                <span class="duel-time">Limite: <?= $duel['time_limit'] ?> secondes</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="duel-started">
                                            Commencé: <?= date('d/m/Y H:i', strtotime($duel['started_at'])) ?>
                                        </div>
                                        <?php if (!$has_questions): ?>
                                            <div class="duel-error" style="color: var(--danger-color); font-size: 0.875rem; margin-top: 0.5rem;">
                                                Erreur : Ce duel n'a pas de questions disponibles. Veuillez contacter un administrateur.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="duel-actions">
                                        <?php if ($has_questions): ?>
                                            <a href="play.php?id=<?= $duel['id'] ?>" class="btn btn-primary btn-sm">Jouer</a>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-sm" disabled>Jouer</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!-- Conteneur pour les duels supplémentaires (caché par défaut) -->
                        <?php if ($has_more_duels): ?>
                            <div class="additional-duels" style="display: none;">
                                <?php 
                                $duel_count = 0;
                                foreach ($active_duels as $duel): 
                                    $duel_count++;
                                    if ($duel_count > 5):
                                        $questions = getDuelQuestions($duel['id']);
                                        $has_questions = !empty($questions);
                                ?>
                                        <div class="duel-item">
                                            <div class="duel-details">
                                                <div class="duel-header">
                                                    <h3 class="duel-title">
                                                        <?= htmlspecialchars($duel['challenger_nom']) ?> vs <?= htmlspecialchars($duel['opponent_nom']) ?>
                                                    </h3>
                                                    <span class="duel-type badge badge-<?= $duel['type'] ?>">
                                                        <?= getDuelTypeLabel($duel['type']) ?>
                                                    </span>
                                                </div>
                                                <div class="duel-info">
                                                    <?php if ($duel['categorie_nom']): ?>
                                                        <span class="duel-category">Catégorie: <?= htmlspecialchars($duel['categorie_nom']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($duel['difficulte_nom']): ?>
                                                        <span class="duel-difficulty">Difficulté: <?= htmlspecialchars($duel['difficulte_nom']) ?></span>
                                                    <?php endif; ?>
                                                    <span class="duel-questions"><?= $duel['question_count'] ?> questions</span>
                                                    <?php if ($duel['time_limit']): ?>
                                                        <span class="duel-time">Limite: <?= $duel['time_limit'] ?> secondes</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="duel-started">
                                                    Commencé: <?= date('d/m/Y H:i', strtotime($duel['started_at'])) ?>
                                                </div>
                                                <?php if (!$has_questions): ?>
                                                    <div class="duel-error" style="color: var(--danger-color); font-size: 0.875rem; margin-top: 0.5rem;">
                                                        Erreur : Ce duel n'a pas de questions disponibles. Veuillez contacter un administrateur.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="duel-actions">
                                                <?php if ($has_questions): ?>
                                                    <a href="play.php?id=<?= $duel['id'] ?>" class="btn btn-primary btn-sm">Jouer</a>
                                                <?php else: ?>
                                                    <button class="btn btn-primary btn-sm" disabled>Jouer</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer toggle-duels-footer">
                                <button id="toggle-duels-btn" class="btn btn-link">Voir plus de duels</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Pending Invitations -->
                <?php if (!empty($pending_invitations)): ?>
                <section class="dashboard-card invitations-card" id="invitations">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            Invitations en attente
                            <span class="badge badge-warning"><?= count($pending_invitations) ?></span>
                        </h2>
                    </div>
                    <div class="invitations-list">
                        <?php foreach ($pending_invitations as $invitation): ?>
                            <div class="invitation-item">
                                <div class="invitation-details">
                                    <div class="invitation-header">
                                        <h3 class="invitation-title">Défi de <?= htmlspecialchars($invitation['sender_nom']) ?></h3>
                                        <span class="invitation-type badge badge-<?= $invitation['type'] ?>">
                                            <?= getDuelTypeLabel($invitation['type']) ?>
                                        </span>
                                    </div>
                                    <div class="invitation-info">
                                        <?php if ($invitation['categorie_nom']): ?>
                                            <span class="invitation-category">Catégorie: <?= htmlspecialchars($invitation['categorie_nom']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($invitation['difficulte_nom']): ?>
                                            <span class="invitation-difficulty">Difficulté: <?= htmlspecialchars($invitation['difficulte_nom']) ?></span>
                                        <?php endif; ?>
                                        <span class="invitation-questions"><?= $invitation['question_count'] ?> questions</span>
                                        <?php if ($invitation['time_limit']): ?>
                                            <span class="invitation-time">Limite: <?= $invitation['time_limit'] ?> secondes</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($invitation['message']): ?>
                                        <div class="invitation-message">
                                            <p><?= htmlspecialchars($invitation['message']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="invitation-expires">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="icon-sm">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        Expire: <?= date('d/m/Y H:i', strtotime($invitation['expires_at'])) ?>
                                    </div>
                                </div>
                                <div class="invitation-actions">
                                    <form method="post" action="accept_invitation.php">
                                        <input type="hidden" name="invitation_id" value="<?= $invitation['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Accepter</button>
                                    </form>
                                    <form method="post" action="decline_invitation.php">
                                        <input type="hidden" name="invitation_id" value="<?= $invitation['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Décliner</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="dashboard-column">
                <!-- Duel Types Card -->
                <section class="dashboard-card duel-types-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                            </svg>
                            Types de duels
                        </h2>
                    </div>
                    <div class="duel-types-list">
                        <div class="duel-type-item">
                            <div class="duel-type-icon timed">⏱️</div>
                            <div class="duel-type-content">
                                <h3 class="duel-type-title">Contre la montre</h3>
                                <p class="duel-type-description">Le plus rapide à terminer avec au moins 50% de bonnes réponses gagne. La vitesse est votre atout !</p>
                            </div>
                        </div>
                        <div class="duel-type-item">
                            <div class="duel-type-icon accuracy">🎯</div>
                            <div class="duel-type-content">
                                <h3 class="duel-type-title">Précision</h3>
                                <p class="duel-type-description">Celui qui a le plus de bonnes réponses gagne. En cas d'égalité, le plus rapide l'emporte.</p>
                            </div>
                        </div>
                        <div class="duel-type-item">
                            <div class="duel-type-icon mixed">🔄</div>
                            <div class="duel-type-content">
                                <h3 class="duel-type-title">Mixte</h3>
                                <p class="duel-type-description">Combinaison de précision et de vitesse. Soyez à la fois rapide et précis pour gagner !</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Recent Duels -->
                <?php if (!empty($completed_duels)): ?>
                <section class="dashboard-card recent-duels-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Duels récents
                        </h2>
                    </div>
                    <div class="recent-duels-list">
                        <?php
                        // Connexion à la base de données pour les recalculs éventuels
                        $database = new Database();
                        $db = $database->connect();
                        ?>
                        <?php foreach ($completed_duels as $duel): ?>
                            <?php
                            // Récupérer les résultats de l'utilisateur connecté
                            $user_results = getDuelResults($duel['id'], $user_id);
                            $total_questions = count(getDuelQuestions($duel['id']));
                            $score = $user_results['score'] ?? 0;
                            $accuracy = $user_results['accuracy'] ?? 0;
                            $completion_time = $user_results['completion_time'] ?? 0;

                            // Log des données brutes pour débogage
                            error_log("Index.php - Duel ID {$duel['id']} - User ID {$user_id} - Raw Results: " . json_encode($user_results));

                            // Si le score est 0, essayer de recalculer à partir de duel_answers (comme dans results.php)
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
                                error_log("Index.php - Duel ID {$duel['id']} - Recalculated Score: $score");

                                // Recalculer la précision si nécessaire
                                $accuracy = $total_questions > 0 ? round(($score / $total_questions) * 100) : 0;
                            }
                            ?>
                            <div class="duel-item">
                                <div class="duel-details">
                                    <div class="duel-header">
                                        <h3 class="duel-title">
                                            <?= htmlspecialchars($duel['challenger_nom']) ?> vs <?= htmlspecialchars($duel['opponent_nom']) ?>
                                        </h3>
                                        <span class="duel-type badge badge-<?= $duel['type'] ?>">
                                            <?= getDuelTypeLabel($duel['type']) ?>
                                        </span>
                                    </div>
                                    <div class="duel-result">
                                        <?php if ($duel['winner_id'] == $user_id): ?>
                                            <span class="result-win">Victoire</span>
                                        <?php elseif ($duel['winner_id'] && $duel['winner_id'] != $user_id): ?>
                                            <span class="result-loss">Défaite</span>
                                        <?php else: ?>
                                            <span class="result-draw">Égalité</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="duel-score">
                                        Score: <?= $score ?>/<?= $total_questions ?> 
                                        (Précision: <?= $accuracy ?>%)
                                    </div>
                                    <div class="duel-time">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="icon-sm">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        <?= $completion_time ?> secondes
                                    </div>
                                </div>
                                <div class="duel-actions">
                                    <a href="results.php?id=<?= $duel['id'] ?>" class="btn btn-outline btn-sm">Détails</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="history.php" class="btn btn-link">Voir tous mes duels</a>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Leaderboard Preview -->
                <section class="dashboard-card leaderboard-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                            Classement
                        </h2>
                    </div>
                    <div class="leaderboard-preview">
                        <table class="leaderboard-table">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Joueur</th>
                                    <th>Victoires</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaderboard as $index => $player): ?>
                                    <tr class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?> <?= $player['id'] == $user_id ? 'current-user' : '' ?>">
                                        <td class="rank">
                                            <?php if ($index == 0): ?>
                                                <i class="fas fa-trophy" style="color: #FFD700;"></i>
                                            <?php elseif ($index == 1): ?>
                                                <i class="fas fa-trophy" style="color: #C0C0C0;"></i>
                                            <?php elseif ($index == 2): ?>
                                                <i class="fas fa-trophy" style="color: #CD7F32;"></i>
                                            <?php else: ?>
                                                <?= $index + 1 ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="player-name">
                                            <a href="../profil.php?id=<?= htmlspecialchars($player['id']) ?>" class="player-link">
                                                <?= htmlspecialchars($player['nom']) ?>
                                                <?php if (isset($player['est_contributeur']) && $player['est_contributeur']): ?>
                                                    <i class="certified-icon fas fa-check-circle" title="Contributeur certifié"></i>
                                                <?php endif; ?>
                                            </a>
                                        </td>
                                        <td class="wins-count"><?= $player['wins'] ?></td>
                                        <td class="win-percentage"><?= $player['win_percentage'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="leaderboard.php" class="btn btn-link">Voir le classement complet</a>
                    </div>
                </section>
            </div>
        </div>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <h2 class="section-title">Comment fonctionnent les duels ?</h2>
            <div class="steps-container">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                        </svg>
                    </div>
                    <h3 class="step-title">Lancez un défi</h3>
                    <p class="step-description">Choisissez un adversaire, un type de duel et des paramètres pour créer votre défi.</p>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <h3 class="step-title">Attendez l'acceptation</h3>
                    <p class="step-description">Votre adversaire recevra une invitation qu'il pourra accepter ou décliner.</p>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="step-title">Jouez le duel</h3>
                    <p class="step-description">Répondez aux questions le plus rapidement et précisément possible selon le type de duel.</p>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" />
                        </svg>
                    </div>
                    <h3 class="step-title">Consultez les résultats</h3>
                    <p class="step-description">Découvrez qui a gagné et analysez vos performances pour vous améliorer.</p>
                </div>
            </div>
        </section>

        <!-- Popular Duels Section -->
        <?php if (!empty($popular_duels)): ?>
        <section class="popular-duels">
            <h2 class="section-title">Duels populaires</h2>
            <div class="popular-duels-grid">
                <?php foreach ($popular_duels as $duel): ?>
                <div class="popular-duel-card">
                    <div class="popular-duel-header">
                        <h3 class="popular-duel-title"><?= htmlspecialchars($duel['title']) ?></h3>
                        <span class="badge badge-<?= $duel['type'] ?>"><?= getDuelTypeLabel($duel['type']) ?></span>
                    </div>
                    <div class="popular-duel-content">
                        <div class="popular-duel-info">
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="icon-sm">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                </svg>
                                <span><?= $duel['player_count'] ?> joueurs</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="icon-sm">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <span><?= $duel['avg_time'] ?> sec en moyenne</span>
                            </div>
                        </div>
                        <p class="popular-duel-description"><?= htmlspecialchars($duel['description']) ?></p>
                    </div>
                    <div class="popular-duel-footer">
                        <a href="challenge.php?template=<?= $duel['id'] ?>" class="btn btn-primary btn-sm">Lancer ce duel</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="cta-content">
                <h2 class="cta-title">Prêt à relever le défi ?</h2>
                <p class="cta-description">Montrez vos connaissances et affrontez d'autres joueurs dans des duels passionnants !</p>
                <a href="challenge.php" class="btn btn-primary btn-lg">Lancer un défi maintenant</a>
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

.duels-page {
    padding: 2rem 0 4rem;
}

/* Hero Section */
.duels-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, var(--primary-light), rgba(16, 185, 129, 0.1));
    border-radius: var(--border-radius);
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
}

.hero-content {
    flex: 1;
    padding-right: 2rem;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.hero-description {
    font-size: 1.25rem;
    color: var(--text-color);
    margin-bottom: 2rem;
    max-width: 600px;
}

.hero-actions {
    display: flex;
    gap: 1rem;
}

.hero-image {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-img {
    max-width: 100%;
    height: auto;
    max-height: 300px;
}

/* Notification Section */
.duels-notifications {
    margin-bottom: 2rem;
}

.notification-card {
    display: flex;
    align-items: center;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--warning-color);
}

.notification-icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    background-color: rgba(245, 158, 11, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
}

.notification-icon svg {
    width: 1.5rem;
    height: 1.5rem;
    color: var(--warning-color);
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.notification-message {
    color: var(--text-muted);
    margin: 0;
}

.notification-action {
    margin-left: 1.5rem;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.dashboard-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Dashboard Card */
.dashboard-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.card-title {
    display: flex;
    align-items: center;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.card-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    color: var(--primary-color);
}

.card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.toggle-duels-footer {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Stats Card */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    padding: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background-color: var(--primary-light);
    border-radius: var(--border-radius);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.stats-details {
    padding: 0 1.5rem 1.5rem;
}

.progress-stat {
    margin-bottom: 1rem;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.progress-bar {
    height: 0.5rem;
    background-color: var(--border-color);
    border-radius: 9999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 9999px;
}

/* Achievements Section */
.achievements-section {
    padding: 0 1.5rem 1.5rem;
}

.section-subtitle {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
}

.achievements-list {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.achievement-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background-color: var(--primary-light);
    border-radius: 50%;
    position: relative;
    color: var(--primary-color);
    font-size: 1.5rem;
}

.achievement-count {
    position: absolute;
    bottom: -0.25rem;
    right: -0.25rem;
    background-color: var(--primary-color);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

/* Duel Types Card */
.duel-types-list {
    padding: 1.5rem;
}

.duel-type-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.duel-type-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.duel-type-icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.5rem;
}

.duel-type-icon.timed {
    background-color: rgba(59, 130, 246, 0.1);
}

.duel-type-icon.accuracy {
    background-color: rgba(16, 185, 129, 0.1);
}

.duel-type-icon.mixed {
    background-color: rgba(245, 158, 11, 0.1);
}

.duel-type-content {
    flex: 1;
}

.duel-type-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.duel-type-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Active Duels & Invitations */
.active-duels-list, .invitations-list, .recent-duels-list {
    padding: 1.5rem;
}

.duel-item, .invitation-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.25rem;
    background-color: var(--background-color);
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.duel-item:last-child, .invitation-item:last-child {
    margin-bottom: 0;
}

.duel-details, .invitation-details {
    flex: 1;
}

.duel-header, .invitation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.duel-title, .invitation-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.duel-info, .invitation-info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.duel-started, .invitation-expires, .duel-result, .duel-score, .duel-time {
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.invitation-message {
    padding: 0.75rem;
    background-color: var(--primary-light);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.invitation-message p {
    margin: 0;
}

.duel-actions, .invitation-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.result-win {
    color: var(--success-color);
    font-weight: 600;
}

.result-loss {
    color: var(--danger-color);
    font-weight: 600;
}

.result-draw {
    color: var(--warning-color);
    font-weight: 600;
}

/* Leaderboard */
.leaderboard-preview {
    padding: 1.5rem;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
}

.leaderboard-table th, .leaderboard-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.leaderboard-table th {
    font-weight: 600;
    color: var(--text-color);
    background-color: var(--primary-light);
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
    background-color: var(--primary-light);
    font-weight: 600;
}

.rank {
    font-weight: 700;
    text-align: center;
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

.wins-count, .win-percentage {
    text-align: center;
}

/* How It Works Section */
.how-it-works {
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 2rem;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.step-item {
    text-align: center;
    position: relative;
}

.step-number {
    position: absolute;
    top: -0.75rem;
    left: 50%;
    transform: translateX(-50%);
    width: 1.5rem;
    height: 1.5rem;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}

.step-icon {
    width: 4rem;
    height: 4rem;
    background-color: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.step-icon svg {
    width: 2rem;
    height: 2rem;
    color: var(--primary-color);
}

.step-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.step-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Popular Duels Section */
.popular-duels {
    margin-bottom: 3rem;
}

.popular-duels-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.popular-duel-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
}

.popular-duel-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.popular-duel-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.popular-duel-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
}

.popular-duel-content {
    padding: 1.5rem;
}

.popular-duel-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.icon-sm {
    width: 1rem;
    height: 1rem;
}

.popular-duel-description {
    font-size: 0.875rem;
    color: var(--text-color);
    margin: 0;
}

.popular-duel-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    text-align: center;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--primary-color), #6366f1);
    border-radius: var(--border-radius);
    padding: 3rem 2rem;
    text-align: center;
    color: white;
    box-shadow: var(--shadow-lg);
}

.cta-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-description {
    font-size: 1.125rem;
    margin-bottom: 2rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-primary {
    background-color: rgba(79, 70, 229, 0.1);
    color: var(--primary-color);
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

.badge-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
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

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1rem;
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

.btn-success {
    background-color: var(--success-color);
    color: white;
}

.btn-success:hover {
    background-color: #0d9488;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-danger {
    background-color: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
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

.btn-link {
    background-color: transparent;
    color: var(--primary-color);
    padding: 0.5rem;
    text-decoration: underline;
}

.btn-link:hover {
    text-decoration: none;
}

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .steps-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .popular-duels-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .duels-hero {
        flex-direction: column;
        text-align: center;
        padding: 2rem 1.5rem;
    }
    
    .hero-content {
        padding-right: 0;
        margin-bottom: 2rem;
    }
    
    .hero-actions {
        justify-content: center;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .notification-card {
        flex-direction: column;
        text-align: center;
    }
    
    .notification-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .notification-action {
        margin-left: 0;
        margin-top: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .duel-item, .invitation-item {
        flex-direction: column;
    }
    
    .duel-actions, .invitation-actions {
        flex-direction: row;
        margin-top: 1rem;
    }
    
    .popular-duels-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .steps-container {
        grid-template-columns: 1fr;
    }
    
    .hero-actions {
        flex-direction: column;
    }
    
    .hero-actions .btn {
        width: 100%;
    }
    
    .invitation-actions, .duel-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .invitation-actions .btn, .duel-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper functions for duel types and achievements
    function getDuelTypeLabel(type) {
        switch(type) {
            case 'timed': return 'Contre la montre';
            case 'accuracy': return 'Précision';
            case 'mixed': return 'Mixte';
            default: return type;
        }
    }
    
    function getAchievementTitle(type) {
        switch(type) {
            case 'first_win': return 'Première victoire';
            case 'win_streak': return 'Série de victoires';
            case 'perfect_score': return 'Score parfait';
            case 'speed_demon': return 'Vitesse éclair';
            case 'comeback_king': return 'Roi du retour';
            case 'duel_master': return 'Maître des duels';
            default: return type;
        }
    }
    
    function getAchievementIcon(type) {
        switch(type) {
            case 'first_win': return '🏆';
            case 'win_streak': return '🔥';
            case 'perfect_score': return '💯';
            case 'speed_demon': return '⚡';
            case 'comeback_king': return '👑';
            case 'duel_master': return '🌟';
            default: return '🎯';
        }
    }

    // Toggle additional duels
    const toggleButton = document.getElementById('toggle-duels-btn');
    const additionalDuels = document.querySelector('.additional-duels');

    if (toggleButton && additionalDuels) {
        toggleButton.addEventListener('click', function() {
            if (additionalDuels.style.display === 'none' || additionalDuels.style.display === '') {
                additionalDuels.style.display = 'block';
                toggleButton.textContent = 'Replier';
            } else {
                additionalDuels.style.display = 'none';
                toggleButton.textContent = 'Voir plus de duels';
            }
        });
    }
});
</script>

<?php
// Helper functions for duel types and achievements
function getDuelTypeLabel($type) {
    switch($type) {
        case 'timed': return 'Contre la montre';
        case 'accuracy': return 'Précision';
        case 'mixed': return 'Mixte';
        default: return $type;
    }
}

function getAchievementTitle($type) {
    switch($type) {
        case 'first_win': return 'Première victoire';
        case 'win_streak': return 'Série de victoires';
        case 'perfect_score': return 'Score parfait';
        case 'speed_demon': return 'Vitesse éclair';
        case 'comeback_king': return 'Roi du retour';
        case 'duel_master': return 'Maître des duels';
        default: return $type;
    }
}

function getAchievementIcon($type) {
    switch($type) {
        case 'first_win': return '🏆';
        case 'win_streak': return '🔥';
        case 'perfect_score': return '💯';
        case 'speed_demon': return '⚡';
        case 'comeback_king': return '👑';
        case 'duel_master': return '🌟';
        default: return '🎯';
    }
}

include '../includes/footer.php';
?>