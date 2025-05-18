<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !estConnecte()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$utilisateur_id = $_SESSION['utilisateur_id'];
$categorie_id = (int)($data['categorie_id'] ?? 0);
$difficulte_id = (int)($data['difficulte_id'] ?? 0);
$current_question_index = (int)($data['current_question_index'] ?? 0);
$answers = json_encode($data['answers'] ?? []);
$time_elapsed = (int)($data['time_elapsed'] ?? 0);

if ($categorie_id <= 0 || $difficulte_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$db = (new Database())->connect();
$query = "INSERT INTO quiz_progress (utilisateur_id, categorie_id, difficulte_id, current_question_index, answers, time_elapsed)
          VALUES (:utilisateur_id, :categorie_id, :difficulte_id, :current_question_index, :answers, :time_elapsed)
          ON DUPLICATE KEY UPDATE
          current_question_index = :current_question_index,
          answers = :answers,
          time_elapsed = :time_elapsed,
          updated_at = NOW()";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
$stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);
$stmt->bindParam(':current_question_index', $current_question_index, PDO::PARAM_INT);
$stmt->bindParam(':answers', $answers, PDO::PARAM_STR);
$stmt->bindParam(':time_elapsed', $time_elapsed, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
}
?>