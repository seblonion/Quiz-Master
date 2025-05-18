<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
header('Content-Type: application/json');
if (!estConnecte()) {
    echo json_encode(['notifications' => [], 'count' => 0]);
    exit;
}
$database = new Database();
$db = $database->connect();
$query = "SELECT id, type, message, related_id, is_read, created_at
          FROM notifications
          WHERE utilisateur_id = :utilisateur_id
          ORDER BY created_at DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':utilisateur_id', $_SESSION['utilisateur_id'], PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Compter les notifications non lues
$count_query = "SELECT COUNT(*) as count FROM notifications WHERE utilisateur_id = :utilisateur_id AND is_read = 0";
$count_stmt = $db->prepare($count_query);
$count_stmt->bindParam(':utilisateur_id', $_SESSION['utilisateur_id'], PDO::PARAM_INT);
$count_stmt->execute();
$count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo json_encode([
    'notifications' => $notifications,
    'count' => $count
]);
?>
