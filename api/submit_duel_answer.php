<?php
header('Content-Type: application/json; charset=UTF-8');

error_log("submit_duel_answer.php : Début du script");

$db_path = __DIR__ . '/../includes/db.php';
error_log("submit_duel_answer.php : Tentative d'inclusion de db.php depuis $db_path");

if (!file_exists($db_path)) {
    error_log("submit_duel_answer.php : Fichier $db_path introuvable");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : fichier de configuration de la base de données introuvable.']);
    exit;
}

require_once $db_path;
error_log("submit_duel_answer.php : db.php inclus");

require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';
error_log("submit_duel_answer.php : functions.php et duel_functions.php inclus");

if (!isset($db) || !($db instanceof PDO)) {
    error_log("submit_duel_answer.php : Variable \$db non définie ou non un objet PDO");
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : connexion à la base de données non établie.']);
    exit;
}

error_log("submit_duel_answer.php : \$db est un objet PDO valide");

if (!estConnecte()) {
    error_log("submit_duel_answer.php : Utilisateur non connecté, session: " . json_encode($_SESSION));
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("submit_duel_answer.php : Méthode non autorisée, méthode reçue : " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['duel_id']) || !isset($data['user_id']) || !isset($data['question_id']) || !isset($data['answer_id']) || !isset($data['response_time'])) {
    error_log("submit_duel_answer.php : Données invalides - Reçu : " . ($json ? $json : 'Aucune donnée'));
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$duel_id = (int)$data['duel_id'];
$user_id = (int)$data['user_id'];
$question_id = (int)$data['question_id'];
$answer_id = (int)$data['answer_id'];
$response_time = (int)$data['response_time'];

error_log("submit_duel_answer.php : Tentative de soumission pour duel_id=$duel_id, user_id=$user_id, question_id=$question_id, answer_id=$answer_id, response_time=$response_time");

if ($_SESSION['utilisateur_id'] != $user_id) {
    error_log("submit_duel_answer.php : Utilisateur $user_id non autorisé (session utilisateur : " . ($_SESSION['utilisateur_id'] ?? 'non défini') . ")");
    echo json_encode(['error' => 'Utilisateur non autorisé']);
    exit;
}

$duel = getDuelById($duel_id);
if (!$duel) {
    error_log("submit_duel_answer.php : Duel $duel_id non trouvé");
    echo json_encode(['error' => 'Duel non trouvé']);
    exit;
}
error_log("submit_duel_answer.php : Duel $duel_id trouvé : " . json_encode($duel));

if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
    error_log("submit_duel_answer.php : Utilisateur $user_id n'est pas un participant du duel $duel_id");
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

if ($duel['status'] !== 'active') {
    error_log("submit_duel_answer.php : Duel $duel_id n'est pas actif, statut = " . $duel['status']);
    echo json_encode(['error' => "Le duel n'est pas actif"]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT id FROM duel_questions WHERE duel_id = ? AND question_id = ?");
    $stmt->execute([$duel_id, $question_id]);
    $question_exists = $stmt->fetch();
    error_log("submit_duel_answer.php : Vérification question duel_id=$duel_id, question_id=$question_id : " . ($question_exists ? 'Trouvée' : 'Non trouvée'));

    if (!$question_exists) {
        error_log("submit_duel_answer.php : Question $question_id n'existe pas ou ne fait pas partie du duel $duel_id");
        echo json_encode(['error' => 'Question invalide']);
        exit;
    }
} catch (PDOException $e) {
    error_log("submit_duel_answer.php : Erreur lors de la vérification de la question $question_id pour le duel $duel_id : " . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur lors de la vérification de la question']);
    exit;
}

try {
    // Changement : Utiliser la table `options` au lieu de `reponses`
    $stmt = $db->prepare("SELECT id, question_id FROM options WHERE id = ?");
    $stmt->execute([$answer_id]);
    $answer = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("submit_duel_answer.php : Vérification existence answer_id=$answer_id : " . ($answer ? 'Trouvé, question_id=' . $answer['question_id'] : 'Non trouvé'));

    if (!$answer) {
        error_log("submit_duel_answer.php : Réponse $answer_id n'existe pas dans la table options");
        echo json_encode(['error' => "Réponse $answer_id n'existe pas"]);
        exit;
    }

    if ($answer['question_id'] != $question_id) {
        error_log("submit_duel_answer.php : Réponse $answer_id est associée à question_id=" . $answer['question_id'] . ", mais question_id attendu=$question_id");
        echo json_encode(['error' => "Réponse $answer_id n'est pas associée à la question $question_id"]);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT id FROM duel_answers WHERE duel_id = ? AND user_id = ? AND question_id = ?");
        $stmt->execute([$duel_id, $user_id, $question_id]);
        $existing_answer = $stmt->fetch();
        error_log("submit_duel_answer.php : Vérification réponse existante duel_id=$duel_id, user_id=$user_id, question_id=$question_id : " . ($existing_answer ? 'Trouvée' : 'Non trouvée'));

        if ($existing_answer) {
            error_log("submit_duel_answer.php : Utilisateur $user_id a déjà répondu à la question $question_id pour le duel $duel_id");
            echo json_encode(['error' => 'Réponse déjà soumise']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("submit_duel_answer.php : Erreur lors de la vérification des réponses existantes : " . $e->getMessage());
        echo json_encode(['error' => 'Erreur serveur lors de la vérification des réponses existantes']);
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO duel_answers (duel_id, user_id, question_id, answer_id, response_time, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$duel_id, $user_id, $question_id, $answer_id, $response_time]);
        error_log("submit_duel_answer.php : Réponse soumise avec succès pour duel $duel_id, utilisateur $user_id, question $question_id");
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("submit_duel_answer.php : Échec de la soumission de la réponse : " . $e->getMessage());
        echo json_encode(['error' => 'Échec de la soumission de la réponse : ' . $e->getMessage()]);
        exit;
    }
} catch (PDOException $e) {
    error_log("submit_duel_answer.php : Erreur lors de la vérification de la réponse $answer_id : " . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur lors de la vérification de la réponse']);
    exit;
}
?>
