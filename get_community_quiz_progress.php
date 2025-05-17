<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['quiz_id']) || !isset($_SESSION['utilisateur_id'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$quiz_id = (int)$_GET['quiz_id'];
$utilisateur_id = $_SESSION['utilisateur_id'];

$database = new Database();
$db = $database->connect();

// Vérifier si la table quiz_progress a les colonnes nécessaires
$query = "SHOW COLUMNS FROM quiz_progress LIKE 'type'";
$stmt = $db->prepare($query);
$stmt->execute();
$column_exists = $stmt->rowCount() > 0;

if (!$column_exists) {
    echo json_encode(['success' => false, 'message' => 'Structure de base de données incompatible']);
    exit;
}

$query = "SELECT progress_data FROM quiz_progress 
          WHERE utilisateur_id = :utilisateur_id AND quiz_id = :quiz_id AND type = 'community'";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
$stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode(['success' => true, 'progress' => json_decode($result['progress_data'])]);
} else {
    echo json_encode(['success' => false]);
}
?>
