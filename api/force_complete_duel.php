<?php
header('Content-Type: application/json; charset=UTF-8');

error_log("force_complete_duel.php : Début du script");

// Vérifier l'existence du fichier db.php
$db_path = __DIR__ . '/../includes/db.php';
error_log("force_complete_duel.php : Tentative d'inclusion de db.php depuis $db_path");

if (!file_exists($db_path)) {
    error_log("force_complete_duel.php : Fichier $db_path introuvable");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : fichier de configuration de la base de données introuvable.']);
    exit;
}

require_once $db_path;
error_log("force_complete_duel.php : db.php inclus");

require_once '../includes/functions.php';
error_log("force_complete_duel.php : functions.php inclus");

require_once '../includes/functions/duel_functions.php';
error_log("force_complete_duel.php : duel_functions.php inclus");

// Vérifier que $db est défini et est un objet PDO
if (!isset($db) || $db === null || !($db instanceof PDO)) {
    error_log("force_complete_duel.php : Variable \$db non définie, null ou non un objet PDO après inclusion initiale");
    // Forcer une réinitialisation de $db
    error_log("force_complete_duel.php : Tentative de réinitialisation de \$db");
    if (file_exists($db_path)) {
        include_once $db_path;
        error_log("force_complete_duel.php : db.php ré-inclus pour réinitialisation");
    }
    if (!isset($db) || $db === null || !($db instanceof PDO)) {
        error_log("force_complete_duel.php : Échec de la réinitialisation de \$db : toujours non défini ou null");
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur : connexion à la base de données non établie.']);
        exit;
    }
}

error_log("force_complete_duel.php : \$db est un objet PDO valide");

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    error_log("force_complete_duel.php : Utilisateur non connecté");
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$duel_id = isset($input['duel_id']) ? (int)$input['duel_id'] : 0;
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if (!$duel_id || !$user_id) {
    error_log("force_complete_duel.php : Données invalides - " . json_encode($input));
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Vérifier que l'utilisateur est autorisé
if ($_SESSION['utilisateur_id'] != $user_id) {
    error_log("force_complete_duel.php : Utilisateur $user_id non autorisé (session utilisateur : " . $_SESSION['utilisateur_id'] . ")");
    echo json_encode(['error' => 'Utilisateur non autorisé']);
    exit;
}

$duel = getDuelById($duel_id);
if (!$duel) {
    error_log("force_complete_duel.php : Duel $duel_id non trouvé");
    echo json_encode(['error' => 'Duel non trouvé']);
    exit;
}

// Vérifier que l'utilisateur est un participant
if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
    error_log("force_complete_duel.php : Utilisateur $user_id n'est pas un participant du duel $duel_id");
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Vérifier les réponses des joueurs
$challenger_answers = getDuelPlayerAnswers($duel_id, $duel['challenger_id']);
$opponent_answers = getDuelPlayerAnswers($duel_id, $duel['opponent_id']);

// Si l'adversaire n'a pas répondu, déclarer l'utilisateur comme gagnant
if (empty($opponent_answers) && $user_id === $duel['challenger_id']) {
    $winner_id = $user_id;
} elseif (empty($challenger_answers) && $user_id === $duel['opponent_id']) {
    $winner_id = $user_id;
} else {
    $winner_id = 'draw'; // Cas rare où les deux ont répondu partiellement
}

// Mettre à jour le statut du duel
try {
    $stmt = $db->prepare("UPDATE duels SET status = 'completed', winner_id = ?, date_completed = NOW() WHERE id = ?");
    $stmt->execute([$winner_id === 'draw' ? null : $winner_id, $duel_id]);
    error_log("force_complete_duel.php : Duel $duel_id forcé à terminé. Vainqueur : " . ($winner_id === 'draw' ? 'Match nul' : $winner_id));
    echo json_encode([
        'status' => 'completed',
        'winner_id' => $winner_id
    ]);
} catch (Exception $e) {
    error_log("force_complete_duel.php : Échec de la mise à jour du duel $duel_id - Erreur : " . $e->getMessage());
    echo json_encode(['error' => 'Échec de la mise à jour du duel']);
}
?>