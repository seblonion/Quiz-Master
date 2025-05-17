<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    $_SESSION['message'] = 'Vous devez être connecté pour voir les résultats';
    $_SESSION['message_type'] = 'error';
    header('Location: ../connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Vérifier si l'ID du duel est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID de duel invalide';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$duel_id = (int)$_GET['id'];

// Récupérer les informations du duel
$duel = getDuelById($duel_id);
if (!$duel) {
    $_SESSION['message'] = 'Duel non trouvé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier que l'utilisateur est un participant
if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
    $_SESSION['message'] = 'Vous n\'êtes pas autorisé à voir ces résultats';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier que le duel est terminé
if ($duel['status'] != 'completed') {
    $_SESSION['message'] = 'Ce duel n\'est pas encore terminé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Connexion à la base de données
$database = new Database();
$db = $database->connect();

// Récupérer les résultats du duel depuis duel_results
$results = getDuelResults($duel_id);
if (!$results || !isset($results['challenger_score']) || !isset($results['opponent_score'])) {
    // Solution de secours : récupérer directement depuis duel_results
    $stmt = $db->prepare("
        SELECT user_id, score, completion_time
        FROM duel_results
        WHERE duel_id = :duel_id
    ");
    $stmt->execute(['duel_id' => $duel_id]);
    $duel_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [
        'challenger_score' => 0,
        'opponent_score' => 0,
        'challenger_time' => 0,
        'opponent_time' => 0
    ];

    foreach ($duel_results as $result) {
        if ($result['user_id'] == $duel['challenger_id']) {
            $results['challenger_score'] = (int)$result['score'];
            $results['challenger_time'] = (float)$result['completion_time'];
        } elseif ($result['user_id'] == $duel['opponent_id']) {
            $results['opponent_score'] = (int)$result['score'];
            $results['opponent_time'] = (float)$result['completion_time'];
        }
    }
    error_log("results.php : getDuelResults a échoué, scores récupérés directement depuis duel_results pour duel_id=$duel_id");
}

// Vérification supplémentaire : recalculer les scores depuis duel_answers et options si les scores sont 0
if ($results['challenger_score'] == 0 && $results['opponent_score'] == 0) {
    error_log("results.php : Scores à 0 dans duel_results, recalcul à partir de duel_answers pour duel_id=$duel_id");
    $stmt = $db->prepare("
        SELECT da.user_id, COUNT(*) as score
        FROM duel_answers da
        JOIN options o ON da.answer_id = o.id
        WHERE da.duel_id = :duel_id AND o.est_correcte = '1'
        GROUP BY da.user_id
    ");
    $stmt->execute(['duel_id' => $duel_id]);
    $recalculated_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recalculated_scores as $score) {
        if ($score['user_id'] == $duel['challenger_id']) {
            $results['challenger_score'] = (int)$score['score'];
        } elseif ($score['user_id'] == $duel['opponent_id']) {
            $results['opponent_score'] = (int)$score['score'];
        }
    }
    error_log("results.php : Scores recalculés - challenger_score={$results['challenger_score']}, opponent_score={$results['opponent_score']} pour duel_id=$duel_id");
}

// Récupérer les questions pour le nombre total
$questions = getDuelQuestions($duel_id) ?: [];
$total_questions = is_array($questions) ? count($questions) : 0;

// Calculer la précision
$results['challenger_accuracy'] = $total_questions > 0 ? round(($results['challenger_score'] / $total_questions) * 100, 1) : 0;
$results['opponent_accuracy'] = $total_questions > 0 ? round(($results['opponent_score'] / $total_questions) * 100, 1) : 0;

// Récupérer les détails des joueurs
$challenger = obtenirUtilisateur($duel['challenger_id']);
$opponent = obtenirUtilisateur($duel['opponent_id']);
if (!$challenger || !$opponent) {
    $_SESSION['message'] = 'Utilisateur non trouvé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Récupérer les réponses des joueurs
$challenger_answers = getDuelPlayerAnswers($duel_id, $duel['challenger_id']) ?: [];
$opponent_answers = getDuelPlayerAnswers($duel_id, $duel['opponent_id']) ?: [];

// Déterminer le gagnant en utilisant la fonction de duel_functions.php
$winner_id = determineWinner($results, $duel);

// Inclure l'en-tête
$titre_page = "Résultats du duel";
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre_page) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<main class="duel-results-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Résultats du duel</h1>
            <p class="section-description">
                <?= htmlspecialchars($challenger['nom']) ?> vs <?= htmlspecialchars($opponent['nom']) ?>
            </p>
            <div class="header-actions">
                <a href="/quizmaster/duels/index.php" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Retour aux duels
                </a>
                <?php if ($duel['challenger_id'] == $user_id): ?>
                    <a href="/quizmaster/duels/challenge.php?rematch=<?= $duel_id ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                        </svg>
                        Revanche
                    </a>
                <?php endif; ?>
                <?php if ($duel['opponent_id'] == $user_id): ?>
                    <a href="/quizmaster/duels/challenge.php?challenge=<?= $duel['challenger_id'] ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                        </svg>
                        Défier
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="results-container">
            <!-- Résumé du duel -->
            <div class="card duel-summary">
                <div class="duel-info">
                    <div class="duel-type">
                        <span class="info-label">Type de duel</span>
                        <span class="info-value">
                            <?php if ($duel['type'] == 'timed'): ?>
                                <span class="badge badge-timed">Contre la montre</span>
                            <?php elseif ($duel['type'] == 'accuracy'): ?>
                                <span class="badge badge-accuracy">Précision</span>
                            <?php else: ?>
                                <span class="badge badge-mixed">Mixte</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="duel-date">
                        <span class="info-label">Date</span>
                        <span class="info-value">
                            <?php
                            echo isset($duel['completed_at']) && !empty($duel['completed_at'])
                                ? date('d/m/Y H:i', strtotime($duel['completed_at']))
                                : 'Non disponible';
                            ?>
                        </span>
                    </div>
                    <div class="duel-category">
                        <span class="info-label">Catégorie</span>
                        <span class="info-value"><?= htmlspecialchars($duel['categorie_nom'] ?? 'Toutes') ?></span>
                    </div>
                    <div class="duel-difficulty">
                        <span class="info-label">Difficulté</span>
                        <span class="info-value"><?= htmlspecialchars($duel['difficulte_nom'] ?? 'Toutes') ?></span>
                    </div>
                    <div class="duel-questions">
                        <span class="info-label">Questions</span>
                        <span class="info-value"><?= $total_questions ?></span>
                    </div>
                </div>

                <div class="duel-result">
                    <?php if ($winner_id == 'draw'): ?>
                        <div class="result-badge result-draw">Match nul</div>
                    <?php elseif ($results['challenger_score'] == 0 && $results['opponent_score'] == 0): ?>
                        <div class="result-badge result-draw">Aucun gagnant (scores nuls)</div>
                    <?php else: ?>
                        <div class="result-badge result-winner">
                            Vainqueur: <?= htmlspecialchars($winner_id == $duel['challenger_id'] ? $challenger['nom'] : $opponent['nom']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comparaison des joueurs -->
            <div class="players-comparison">
                <div class="player-card <?= $winner_id == $duel['challenger_id'] ? 'winner' : '' ?>">
                    <div class="player-header">
                        <h3 class="player-name"><?= htmlspecialchars($challenger['nom']) ?></h3>
                        <?php if (!empty($challenger['est_contributeur'])): ?>
                            <span class="contributor-badge" title="Contributeur certifié">
                                <i class="certified-icon fas fa-check-circle"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="player-stats">
                        <div class="stat-item">
                            <div class="stat-label">Score</div>
                            <div class="stat-value"><?= (int)$results['challenger_score'] ?> / <?= $total_questions ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Précision</div>
                            <div class="stat-value"><?= round($results['challenger_accuracy'], 1) ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Temps total</div>
                            <div class="stat-value"><?= round($results['challenger_time'], 1) ?> sec</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Temps moyen</div>
                            <div class="stat-value"><?= $total_questions > 0 ? round($results['challenger_time'] / $total_questions, 1) : 0 ?> sec/q</div>
                        </div>
                    </div>
                </div>

                <div class="vs-badge">VS</div>

                <div class="player-card <?= $winner_id == $duel['opponent_id'] ? 'winner' : '' ?>">
                    <div class="player-header">
                        <h3 class="player-name"><?= htmlspecialchars($opponent['nom']) ?></h3>
                        <?php if (!empty($opponent['est_contributeur'])): ?>
                            <span class="contributor-badge" title="Contributeur certifié">
                                <i class="certified-icon fas fa-check-circle"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="player-stats">
                        <div class="stat-item">
                            <div class="stat-label">Score</div>
                            <div class="stat-value"><?= (int)$results['opponent_score'] ?> / <?= $total_questions ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Précision</div>
                            <div class="stat-value"><?= round($results['opponent_accuracy'], 1) ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Temps total</div>
                            <div class="stat-value"><?= round($results['opponent_time'], 1) ?> sec</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Temps moyen</div>
                            <div class="stat-value"><?= $total_questions > 0 ? round($results['opponent_time'] / $total_questions, 1) : 0 ?> sec/q</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails des questions -->
            <div class="card">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="questions">Questions et réponses</button>
                    <button class="tab-btn" data-tab="comparison">Comparaison détaillée</button>
                    <button class="tab-btn" data-tab="timeline">Chronologie</button>
                </div>

                <div class="tab-content active" id="questions-tab">
                    <div class="questions-list">
                        <?php if (empty($questions) || !is_array($questions)): ?>
                            <p>Aucune question trouvée pour ce duel.</p>
                        <?php else: ?>
                            <?php foreach ($questions as $index => $question): ?>
                                <?php
                                // Trouver la réponse correcte dans options
                                $correct_answer = 'Non disponible';
                                $query = "SELECT texte FROM options WHERE question_id = :question_id AND est_correcte = '1' LIMIT 1";
                                $stmt = $db->prepare($query);
                                $stmt->bindParam(':question_id', $question['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($result) {
                                    $correct_answer = $result['texte'];
                                } else {
                                    error_log("results.php : Aucune réponse correcte trouvée pour question_id={$question['id']} dans duel_id=$duel_id");
                                }
                                ?>
                                <div class="question-item">
                                    <div class="question-header">
                                        <div class="question-number">Question <?= $index + 1 ?></div>
                                        <div class="question-text">
                                            <?= isset($question['texte']) ? htmlspecialchars($question['texte']) : 'Question non disponible' ?>
                                        </div>
                                    </div>
                                    <div class="answers-comparison">
                                        <div class="player-answer <?= isset($challenger_answers[$question['id']]) && $challenger_answers[$question['id']]['is_correct'] ? 'correct' : 'incorrect' ?>">
                                            <div class="answer-header">
                                                <div class="player-name"><?= htmlspecialchars($challenger['nom']) ?></div>
                                                <div class="answer-time"><?= isset($challenger_answers[$question['id']]) ? round($challenger_answers[$question['id']]['time_taken'], 1) . ' sec' : 'Pas de réponse' ?></div>
                                            </div>
                                            <div class="answer-text">
                                                <?php if (isset($challenger_answers[$question['id']])): ?>
                                                    <?= htmlspecialchars($challenger_answers[$question['id']]['answer']) ?>
                                                <?php else: ?>
                                                    <span class="no-answer">Pas de réponse</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="player-answer <?= isset($opponent_answers[$question['id']]) && $opponent_answers[$question['id']]['is_correct'] ? 'correct' : 'incorrect' ?>">
                                            <div class="answer-header">
                                                <div class="player-name"><?= htmlspecialchars($opponent['nom']) ?></div>
                                                <div class="answer-time"><?= isset($opponent_answers[$question['id']]) ? round($opponent_answers[$question['id']]['time_taken'], 1) . ' sec' : 'Pas de réponse' ?></div>
                                            </div>
                                            <div class="answer-text">
                                                <?php if (isset($opponent_answers[$question['id']])): ?>
                                                    <?= htmlspecialchars($opponent_answers[$question['id']]['answer']) ?>
                                                <?php else: ?>
                                                    <span class="no-answer">Pas de réponse</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="correct-answer">
                                        <div class="answer-label">Réponse correcte:</div>
                                        <div class="answer-text">
                                            <?= htmlspecialchars($correct_answer) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-content" id="comparison-tab">
                    <div class="comparison-charts">
                        <div class="chart-container">
                            <h3 class="chart-title">Précision par question</h3>
                            <canvas id="accuracyChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h3 class="chart-title">Temps par question</h3>
                            <canvas id="timeChart"></canvas>
                        </div>
                    </div>
                    <div class="comparison-table-container">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th><?= htmlspecialchars($challenger['nom']) ?> - Réponse</th>
                                    <th><?= htmlspecialchars($challenger['nom']) ?> - Temps</th>
                                    <th><?= htmlspecialchars($opponent['nom']) ?> - Réponse</th>
                                    <th><?= htmlspecialchars($opponent['nom']) ?> - Temps</th>
                                    <th>Différence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($questions) || !is_array($questions)): ?>
                                    <tr>
                                        <td colspan="6">Aucune question disponible.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($questions as $index => $question): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td class="<?= isset($challenger_answers[$question['id']]) && $challenger_answers[$question['id']]['is_correct'] ? 'correct' : 'incorrect' ?>">
                                                <?= isset($challenger_answers[$question['id']]) ? ($challenger_answers[$question['id']]['is_correct'] ? 'Correct' : 'Incorrect') : 'Pas de réponse' ?>
                                            </td>
                                            <td><?= isset($challenger_answers[$question['id']]) ? round($challenger_answers[$question['id']]['time_taken'], 1) . ' sec' : '-' ?></td>
                                            <td class="<?= isset($opponent_answers[$question['id']]) && $opponent_answers[$question['id']]['is_correct'] ? 'correct' : 'incorrect' ?>">
                                                <?= isset($opponent_answers[$question['id']]) ? ($opponent_answers[$question['id']]['is_correct'] ? 'Correct' : 'Incorrect') : 'Pas de réponse' ?>
                                            </td>
                                            <td><?= isset($opponent_answers[$question['id']]) ? round($opponent_answers[$question['id']]['time_taken'], 1) . ' sec' : '-' ?></td>
                                            <td>
                                                <?php
                                                $challenger_time = isset($challenger_answers[$question['id']]) ? $challenger_answers[$question['id']]['time_taken'] : null;
                                                $opponent_time = isset($opponent_answers[$question['id']]) ? $opponent_answers[$question['id']]['time_taken'] : null;
                                                
                                                if ($challenger_time !== null && $opponent_time !== null) {
                                                    $diff = $challenger_time - $opponent_time;
                                                    $faster = $diff < 0 ? $challenger['nom'] : $opponent['nom'];
                                                    echo abs(round($diff, 1)) . ' sec (' . htmlspecialchars($faster) . ' plus rapide)';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="timeline-tab">
                    <div class="timeline-container">
                        <div class="timeline-header">
                            <div class="timeline-player"><?= htmlspecialchars($challenger['nom']) ?></div>
                            <div class="timeline-player"><?= htmlspecialchars($opponent['nom']) ?></div>
                        </div>
                        <div class="timeline">
                            <div class="timeline-scale">
                                <?php
                                $max_time = max(
                                    $results['challenger_time'] ?? 0,
                                    $results['opponent_time'] ?? 0
                                );
                                $scale_steps = 10;
                                $step_size = $max_time > 0 ? ceil($max_time / $scale_steps) : 1;
                                
                                for ($i = 0; $i <= $scale_steps; $i++) {
                                    $time = $i * $step_size;
                                    echo '<div class="timeline-marker" style="left: ' . ($max_time > 0 ? ($time / $max_time * 100) : ($i * (100 / $scale_steps))) . '%">' . $time . 's</div>';
                                }
                                ?>
                            </div>
                            
                            <div class="timeline-tracks">
                                <div class="timeline-track">
                                    <?php
                                    $cumulative_time = 0;
                                    foreach ($questions as $index => $question) {
                                        if (isset($challenger_answers[$question['id']])) {
                                            $answer = $challenger_answers[$question['id']];
                                            $start_pos = $max_time > 0 ? ($cumulative_time / $max_time * 100) : 0;
                                            $width = $max_time > 0 && isset($answer['time_taken']) ? ($answer['time_taken'] / $max_time * 100) : 0;
                                            $class = $answer['is_correct'] ? 'correct' : 'incorrect';
                                            
                                            echo '<div class="timeline-event ' . $class . '" style="left: ' . $start_pos . '%; width: ' . $width . '%;" title="Question ' . ($index + 1) . ': ' . (isset($answer['time_taken']) ? round($answer['time_taken'], 1) : 0) . ' sec">';
                                            echo '<span class="event-label">Q' . ($index + 1) . '</span>';
                                            echo '</div>';
                                            
                                            $cumulative_time += $answer['time_taken'] ?? 0;
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="timeline-track">
                                    <?php
                                    $cumulative_time = 0;
                                    foreach ($questions as $index => $question) {
                                        if (isset($opponent_answers[$question['id']])) {
                                            $answer = $opponent_answers[$question['id']];
                                            $start_pos = $max_time > 0 ? ($cumulative_time / $max_time * 100) : 0;
                                            $width = $max_time > 0 && isset($answer['time_taken']) ? ($answer['time_taken'] / $max_time * 100) : 0;
                                            $class = $answer['is_correct'] ? 'correct' : 'incorrect';
                                            
                                            echo '<div class="timeline-event ' . $class . '" style="left: ' . $start_pos . '%; width: ' . $width . '%;" title="Question ' . ($index + 1) . ': ' . (isset($answer['time_taken']) ? round($answer['time_taken'], 1) : 0) . ' sec">';
                                            echo '<span class="event-label">Q' . ($index + 1) . '</span>';
                                            echo '</div>';
                                            
                                            $cumulative_time += $answer['time_taken'] ?? 0;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

.duel-results-page {
    padding: 2rem 0 4rem;
}

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

.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.duel-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.duel-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.duel-type, .duel-date, .duel-category, .duel-difficulty, .duel-questions {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.info-value {
    font-weight: 500;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-timed {
    background-color: rgba(79, 70, 229, 0.1);
    color: var(--primary-color);
}

.badge-accuracy {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--secondary-color);
}

.badge-mixed {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
}

.duel-result {
    text-align: center;
}

.result-badge {
    font-size: 1.25rem;
    font-weight: 600;
    padding: 0.5rem 1.5rem;
    border-radius: 9999px;
}

.result-winner {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.result-draw {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.players-comparison {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.player-card {
    flex: 1;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: var(--transition);
    border: 2px solid transparent;
}

.player-card.winner {
    border-color: var(--success-color);
}

.player-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.player-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.contributor-badge {
    margin-left: 0.5rem;
    color: #1DA1F2;
}

.player-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1rem;
    font-weight: 600;
}

.vs-badge {
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: var(--shadow);
}

.tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1.5rem;
    overflow-x: auto;
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}

.tab-btn:hover {
    color: var(--primary-color);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.question-item {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.question-header {
    background-color: var(--primary-light);
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.question-number {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.question-text {
    font-size: 1.125rem;
}

.answers-comparison {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background-color: var(--border-color);
}

.player-answer {
    background-color: var(--card-background);
    padding: 1rem;
}

.player-answer.correct {
    background-color: rgba(16, 185, 129, 0.05);
}

.player-answer.incorrect {
    background-color: rgba(239, 68, 68, 0.05);
}

.answer-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.answer-time {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.answer-text {
    font-weight: 500;
}

.no-answer {
    color: var(--text-muted);
    font-style: italic;
}

.correct-answer {
    background-color: rgba(16, 185, 129, 0.05);
    padding: 1rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
}

.answer-label {
    font-weight: 600;
    margin-right: 0.5rem;
    color: var(--success-color);
}

.comparison-charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.chart-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    padding: 1rem;
    box-shadow: var(--shadow-sm);
}

.chart-title {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0;
    margin-bottom: 1rem;
    text-align: center;
}

.comparison-table-container {
    overflow-x: auto;
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
}

.comparison-table th,
.comparison-table td {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
}

.comparison-table th {
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-weight: 600;
    text-align: left;
}

.comparison-table td.correct {
    color: var(--success-color);
}

.comparison-table td.incorrect {
    color: var(--danger-color);
}

.timeline-container {
    margin-top: 2rem;
}

.timeline-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.timeline-player {
    font-weight: 600;
    text-align: center;
}

.timeline {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-scale {
    position: relative;
    height: 20px;
    margin-bottom: 1rem;
}

.timeline-marker {
    position: absolute;
    transform: translateX(-50%);
    font-size: 0.75rem;
    color: var(--text-muted);
}

.timeline-tracks {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.timeline-track {
    position: relative;
    height: 40px;
    background-color: var(--background-color);
    border-radius: 4px;
}

.timeline-event {
    position: absolute;
    height: 100%;
    top: 0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    min-width: 30px;
}

.timeline-event.correct {
    background-color: rgba(16, 185, 129, 0.2);
}

.timeline-event.incorrect {
    background-color: rgba(239, 68, 68, 0.2);
}

.event-label {
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

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

@media (max-width: 992px) {
    .section-title {
        font-size: 1.75rem;
    }
    
    .players-comparison {
        flex-direction: column;
    }
    
    .vs-badge {
        margin: -1rem 0;
        z-index: 1;
    }
    
    .comparison-charts {
        grid-template-columns: 1fr;
    }
    
    .player-stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .duel-info {
        grid-template-columns: 1fr 1fr;
    }
    
    .answers-comparison {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .duel-summary {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .duel-info {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1;
        text-align: center;
    }
    
    .timeline-tracks {
        grid-template-columns: 1fr;
    }
    
    .timeline-header {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            const tabId = btn.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    if (document.getElementById('accuracyChart')) {
        const ctx1 = document.getElementById('accuracyChart').getContext('2d');
        const ctx2 = document.getElementById('timeChart').getContext('2d');
        
        const questions = <?= json_encode(array_map(function($q, $i) { return 'Q' . ($i + 1); }, $questions, array_keys($questions))) ?> || [];
        
        const challengerAccuracy = <?= json_encode(array_map(function($q) use ($challenger_answers) {
            return isset($challenger_answers[$q['id']]) && $challenger_answers[$q['id']]['is_correct'] ? 100 : 0;
        }, $questions)) ?> || [];
        
        const opponentAccuracy = <?= json_encode(array_map(function($q) use ($opponent_answers) {
            return isset($opponent_answers[$q['id']]) && $opponent_answers[$q['id']]['is_correct'] ? 100 : 0;
        }, $questions)) ?> || [];
        
        const challengerTime = <?= json_encode(array_map(function($q) use ($challenger_answers) {
            return isset($challenger_answers[$q['id']]) && isset($challenger_answers[$q['id']]['time_taken']) ? round($challenger_answers[$q['id']]['time_taken'], 1) : 0;
        }, $questions)) ?> || [];
        
        const opponentTime = <?= json_encode(array_map(function($q) use ($opponent_answers) {
            return isset($opponent_answers[$q['id']]) && isset($opponent_answers[$q['id']]['time_taken']) ? round($opponent_answers[$q['id']]['time_taken'], 1) : 0;
        }, $questions)) ?> || [];
        
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: questions,
                datasets: [
                    {
                        label: '<?= htmlspecialchars($challenger['nom']) ?>',
                        data: challengerAccuracy,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1
                    },
                    {
                        label: '<?= htmlspecialchars($opponent['nom']) ?>',
                        data: opponentAccuracy,
                        backgroundColor: 'rgba(245, 158, 11, 0.6)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Précision (%)'
                        }
                    }
                }
            }
        });
        
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: questions,
                datasets: [
                    {
                        label: '<?= htmlspecialchars($challenger['nom']) ?>',
                        data: challengerTime,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: '<?= htmlspecialchars($opponent['nom']) ?>',
                        data: opponentTime,
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Temps (secondes)'
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>