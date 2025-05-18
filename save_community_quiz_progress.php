<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['quiz_id']) || !isset($data['current_question_index']) || !isset($data['answers'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$quiz_id = (int)$data['quiz_id'];
$progress_data = json_encode([
    'current_question_index' => $data['current_question_index'],
    'answers' => $data['answers'],
    'time_elapsed' => $data['time_elapsed']
]);

$database = new Database();
$db = $database->connect();

// Vérifier si la table quiz_progress a les colonnes nécessaires
$query = "SHOW COLUMNS FROM quiz_progress LIKE 'type'";
$stmt = $db->prepare($query);
$stmt->execute();
$column_exists = $stmt->rowCount() > 0;

if (!$column_exists) {
    // Ajouter les colonnes manquantes
    $query = "ALTER TABLE quiz_progress 
              ADD COLUMN type VARCHAR(20) DEFAULT 'standard' AFTER difficulte_id,
              ADD COLUMN quiz_id INT NULL AFTER type,
              ADD COLUMN progress_data TEXT NULL AFTER quiz_id,
              DROP INDEX user_quiz_unique,
              ADD UNIQUE KEY user_quiz_unique (utilisateur_id, categorie_id, difficulte_id, type, quiz_id)";
    $stmt = $db->prepare($query);
    $stmt->execute();
}

// Insérer ou mettre à jour la progression
$query = "INSERT INTO quiz_progress (utilisateur_id, categorie_id, difficulte_id, type, quiz_id, progress_data) 
          VALUES (:utilisateur_id, 0, 0, 'community', :quiz_id, :progress_data) 
          ON DUPLICATE KEY UPDATE progress_data = :progress_data";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
$stmt->bindParam(':progress_data', $progress_data, PDO::PARAM_STR);
$stmt->execute();

echo json_encode(['success' => true]);
?>
