<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!estConnecte()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$categorie_id = (int)($_GET['categorie_id'] ?? 0);
$difficulte_id = (int)($_GET['difficulte_id'] ?? 0);

if ($categorie_id <= 0 || $difficulte_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$db = (new Database())->connect();
$query = "SELECT current_question_index, answers, time_elapsed
          FROM quiz_progress
          WHERE utilisateur_id = :utilisateur_id
          AND categorie_id = :categorie_id
          AND difficulte_id = :difficulte_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
$stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);
$stmt->execute();
$progress = $stmt->fetch(PDO::FETCH_ASSOC);

if ($progress) {
    echo json_encode([
        'success' => true,
        'progress' => [
            'current_question_index' => (int)$progress['current_question_index'],
            'answers' => json_decode($progress['answers'], true) ?: [],
            'time_elapsed' => (int)$progress['time_elapsed']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune progression trouvée']);
}
?>