<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../includes/functions.php';
require_once '../../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

if (!isset($_POST['duel_id'])) {
    echo json_encode(['error' => 'ID du duel manquant']);
    exit;
}

$duel_id = (int)$_POST['duel_id'];
$database = new Database();
$db = $database->connect();

// Récupérer les détails du duel
$stmt = $db->prepare("
    SELECT 
        d.id,
        d.type,
        d.status,
        d.question_count,
        d.time_limit,
        d.started_at,
        d.completed_at,
        c.nom AS categorie_nom,
        c.id AS categorie_id,
        u1.nom AS challenger_nom,
        u2.nom AS opponent_nom
    FROM duels d
    LEFT JOIN categories c ON d.categorie_id = c.id
    LEFT JOIN utilisateurs u1 ON d.challenger_id = u1.id
    LEFT JOIN utilisateurs u2 ON d.opponent_id = u2.id
    WHERE d.id = :duel_id
");
$stmt->execute([':duel_id' => $duel_id]);
$duel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$duel) {
    echo json_encode(['error' => 'Duel non trouvé']);
    exit;
}

// Récupérer les résultats
$stmt = $db->prepare("
    SELECT 
        u.nom AS user_nom,
        dr.score,
        dr.completion_time
    FROM duel_results dr
    JOIN utilisateurs u ON dr.user_id = u.id
    WHERE dr.duel_id = :duel_id
");
$stmt->execute([':duel_id' => $duel_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les questions
$stmt = $db->prepare("
    SELECT 
        q.question AS texte,
        (SELECT texte FROM options o WHERE o.question_id = q.id AND o.est_correcte = '1' LIMIT 1) AS correct_option
    FROM duel_questions dq
    JOIN questions q ON dq.question_id = q.id
    WHERE dq.duel_id = :duel_id
");
$stmt->execute([':duel_id' => $duel_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'id' => $duel['id'],
    'type' => $duel['type'],
    'status' => $duel['status'],
    'question_count' => $duel['question_count'],
    'time_limit' => $duel['time_limit'],
    'started_at' => $duel['started_at'],
    'completed_at' => $duel['completed_at'],
    'categorie_nom' => $duel['categorie_nom'],
    'categorie_id' => $duel['categorie_id'],
    'challenger_nom' => $duel['challenger_nom'],
    'opponent_nom' => $duel['opponent_nom'],
    'results' => $results,
    'questions' => $questions
]);
?>