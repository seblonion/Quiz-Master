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

if ($categorie_id <= 0 || $difficulte_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$db = (new Database())->connect();
$query = "DELETE FROM quiz_progress
          WHERE utilisateur_id = :utilisateur_id
          AND categorie_id = :categorie_id
          AND difficulte_id = :difficulte_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
$stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    // Réinitialiser les questions utilisées pour permettre un nouveau quiz
    $_SESSION['questions_utilisees'] = [];
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
}
?>