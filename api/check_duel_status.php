<?php
header('Content-Type: application/json; charset=UTF-8');

error_log("check_duel_status.php : Début du script");

// Vérifier l'existence du fichier db.php
$db_path = __DIR__ . '/../includes/db.php';
error_log("check_duel_status.php : Tentative d'inclusion de db.php depuis $db_path");

if (!file_exists($db_path)) {
    error_log("check_duel_status.php : Fichier $db_path introuvable");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : fichier de configuration de la base de données introuvable.']);
    exit;
}

require_once $db_path;
error_log("check_duel_status.php : db.php inclus");

require_once '../includes/functions.php';
error_log("check_duel_status.php : functions.php inclus");

require_once '../includes/functions/duel_functions.php';
error_log("check_duel_status.php : duel_functions.php inclus");

// Vérifier que $db est défini et est un objet PDO
if (!isset($db) || $db === null || !($db instanceof PDO)) {
    error_log("check_duel_status.php : Variable \$db non définie, null ou non un objet PDO après inclusion initiale");
    // Forcer une réinitialisation de $db
    error_log("check_duel_status.php : Tentative de réinitialisation de \$db");
    if (file_exists($db_path)) {
        include_once $db_path;
        error_log("check_duel_status.php : db.php ré-inclus pour réinitialisation");
    }
    if (!isset($db) || $db === null || !($db instanceof PDO)) {
        error_log("check_duel_status.php : Échec de la réinitialisation de \$db : toujours non défini ou null");
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur : connexion à la base de données non établie.']);
        exit;
    }
}

error_log("check_duel_status.php : \$db est un objet PDO valide");

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    error_log("check_duel_status.php : Utilisateur non connecté");
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

// Vérifier si l'ID du duel est présent
if (!isset($_GET['duel_id']) || empty($_GET['duel_id'])) {
    error_log("check_duel_status.php : ID du duel manquant");
    echo json_encode(['error' => 'ID du duel manquant']);
    exit;
}

$duel_id = (int)$_GET['duel_id'];
$user_id = $_SESSION['utilisateur_id'];

// Récupérer les informations du duel
$duel = getDuelById($duel_id);

// Vérifier si le duel existe et si l'utilisateur est un participant
if (!$duel) {
    error_log("check_duel_status.php : Duel ID $duel_id non trouvé dans la base de données.");
    echo json_encode(['error' => 'Duel non trouvé']);
    exit;
}

if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
    error_log("check_duel_status.php : Utilisateur $user_id non autorisé pour le duel $duel_id.");
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Journaliser le statut actuel du duel
error_log("check_duel_status.php : Vérification du statut du duel $duel_id : statut actuel = " . $duel['status']);

// Vérifier si le duel est déjà terminé
if ($duel['status'] === 'completed') {
    error_log("check_duel_status.php : Duel $duel_id terminé, retourne " . json_encode(['status' => 'completed', 'winner_id' => $duel['winner_id']]));
    echo json_encode([
        'status' => 'completed',
        'winner_id' => $duel['winner_id']
    ]);
    exit;
}

// Identifier l'adversaire
$opponent_id = ($user_id === $duel['challenger_id']) ? $duel['opponent_id'] : $duel['challenger_id'];

// Vérifier les réponses de l'adversaire et de l'utilisateur
$opponent_answers = getDuelPlayerAnswers($duel_id, $opponent_id);
$user_answers = getDuelPlayerAnswers($duel_id, $user_id);

// Vérifier si l'utilisateur a terminé (au moins une réponse soumise)
$user_has_completed = !empty($user_answers);

// Si l'utilisateur n'a pas encore terminé, retourner "pending"
if (!$user_has_completed) {
    error_log("check_duel_status.php : Duel $duel_id - Utilisateur $user_id n'a pas terminé, retourne " . json_encode(['status' => 'pending']));
    echo json_encode(['status' => 'pending']);
    exit;
}

// Si l'utilisateur a terminé, vérifier si l'adversaire a abandonné
if ($user_has_completed) {
    // Vérifier le temps écoulé depuis la création du duel
    $duel_creation_time = strtotime($duel['date_created']);
    $current_time = time();
    $time_elapsed = $current_time - $duel_creation_time;

    // Si l'adversaire n'a pas répondu après 10 minutes, marquer le duel comme abandonné
    $abandonment_threshold = 600; // 10 minutes en secondes
    if (empty($opponent_answers) && $time_elapsed > $abandonment_threshold) {
        error_log("check_duel_status.php : Adversaire $opponent_id n'a pas répondu pour le duel $duel_id après $time_elapsed secondes. Marquage comme abandonné.");
        // Mettre à jour le statut du duel comme abandonné et déclarer l'utilisateur comme gagnant
        try {
            $stmt = $db->prepare("UPDATE duels SET status = 'abandoned', winner_id = ?, date_completed = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $duel_id]);
            error_log("check_duel_status.php : Duel $duel_id marqué comme abandonné, retourne " . json_encode(['status' => 'abandoned', 'winner_id' => $user_id]));
            echo json_encode([
                'status' => 'abandoned',
                'winner_id' => $user_id
            ]);
        } catch (PDOException $e) {
            error_log("check_duel_status.php : Erreur lors de la mise à jour du duel $duel_id comme abandonné : " . $e->getMessage());
            echo json_encode(['error' => 'Erreur serveur lors de la mise à jour du statut']);
        }
        exit;
    }
}

// Si l'adversaire a répondu ou si le délai n'est pas encore atteint, retourner "pending"
error_log("check_duel_status.php : Duel $duel_id - En attente de l'adversaire, retourne " . json_encode(['status' => 'pending']));
echo json_encode(['status' => 'pending']);
?>