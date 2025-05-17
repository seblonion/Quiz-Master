<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID manquant']);
    exit;
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$quiz_id = (int)$data['quiz_id'];

$database = new Database();
$db = $database->connect();

$query = "DELETE FROM quiz_progress WHERE utilisateur_id = :utilisateur_id AND quiz_id = :quiz_id AND type = 'community'";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['success' => true]);
?>
