<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (!estConnecte()) {
    http_response_code(403);
    exit;
}
$database = new Database();
$db = $database->connect();
$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? 0;
if ($action === 'mark_read' && $id) {
    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND utilisateur_id = :utilisateur_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':utilisateur_id', $_SESSION['utilisateur_id'], PDO::PARAM_INT);
    $stmt->execute();
} elseif ($action === 'delete' && $id) {
    $query = "DELETE FROM notifications WHERE id = :id AND utilisateur_id = :utilisateur_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':utilisateur_id', $_SESSION['utilisateur_id'], PDO::PARAM_INT);
    $stmt->execute();
}
http_response_code(200);
?>
