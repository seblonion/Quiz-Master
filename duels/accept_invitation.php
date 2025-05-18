<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    error_log("User not connected, redirecting to connexion.php");
    header('Location: ../connexion.php');
    exit;
}

// Vérifier la clé de session
if (!isset($_SESSION['utilisateur_id'])) {
    error_log("Session utilisateur_id not set, redirecting to connexion.php");
    header('Location: ../connexion.php');
    exit;
}
$user_id = $_SESSION['utilisateur_id'];
error_log("User ID: $user_id");

// Vérifier si l'ID de l'invitation est présent
if (!isset($_POST['invitation_id']) || empty($_POST['invitation_id'])) {
    error_log("Missing or empty invitation_id in POST");
    $_SESSION['message'] = "ID d'invitation manquant.";
    $_SESSION['message_type'] = "error";
    header('Location: index.php');
    exit;
}

$invitation_id = (int)$_POST['invitation_id'];
error_log("Processing invitation_id: $invitation_id");

// Accepter l'invitation
$result = acceptDuelInvitation($invitation_id);
error_log("acceptDuelInvitation result: " . ($result ? 'success' : 'failure'));

if ($result) {
    // Connexion à la base de données
    $database = new Database();
    $db = $database->connect();

    // Vérifier si une notification existe pour cette invitation
    $query = "SELECT id FROM notifications WHERE related_id = :invitation_id AND utilisateur_id = :user_id AND type = 'duel_invitation' AND is_read = 0";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Marquer la notification comme lue
        $query = "UPDATE notifications SET is_read = 1 WHERE related_id = :invitation_id AND utilisateur_id = :user_id AND type = 'duel_invitation'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        error_log("Notification marked as read for invitation_id=$invitation_id, user_id=$user_id");
    } else {
        error_log("No unread notification found for invitation_id=$invitation_id, user_id=$user_id");
    }

    $_SESSION['message'] = "Invitation acceptée. Le duel va commencer!";
    $_SESSION['message_type'] = "success";
    
    // Récupérer l'ID du duel pour rediriger vers la page de jeu
    $query = "SELECT duel_id FROM duel_invitations WHERE id = :invitation_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || empty($result['duel_id'])) {
        error_log("No duel_id found for invitation_id=$invitation_id");
        $_SESSION['message'] = "Erreur : Duel introuvable pour cette invitation.";
        $_SESSION['message_type'] = "error";
        header('Location: index.php');
        exit;
    }
    
    $duel_id = $result['duel_id'];
    error_log("Redirecting to play.php with duel_id=$duel_id");
    header('Location: play.php?id=' . $duel_id);
} else {
    error_log("Failed to accept invitation_id=$invitation_id");
    $_SESSION['message'] = "Erreur lors de l'acceptation de l'invitation.";
    $_SESSION['message_type'] = "error";
    header('Location: index.php');
}
exit;
?>