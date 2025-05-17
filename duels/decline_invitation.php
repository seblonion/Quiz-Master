<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    header('Location: ../connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'ID de l'invitation est présent
if (!isset($_POST['invitation_id']) || empty($_POST['invitation_id'])) {
    $_SESSION['message'] = "ID d'invitation manquant.";
    $_SESSION['message_type'] = "error";
    header('Location: index.php');
    exit;
}

$invitation_id = (int)$_POST['invitation_id'];

// Décliner l'invitation
$result = declineDuelInvitation($invitation_id);

$database = new Database();
$db = $database->connect();

if ($result) {
    // Vérifier si une notification existe pour cette invitation
    $query = "SELECT id FROM notifications WHERE related_id = :invitation_id AND user_id = :user_id AND type = 'duel_invitation' AND is_read = 0";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Marquer la notification comme lue
        $query = "UPDATE notifications SET is_read = 1 WHERE related_id = :invitation_id AND user_id = :user_id AND type = 'duel_invitation'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $_SESSION['message'] = "Invitation déclinée.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Erreur lors du refus de l'invitation.";
    $_SESSION['message_type'] = "error";
}

header('Location: index.php');
exit;
?>