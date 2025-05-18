<?php
header('Content-Type: application/json; charset=UTF-8');

error_log("complete_duel.php : Début du script");

// Vérifier l'existence du fichier db.php
$db_path = __DIR__ . '/../includes/db.php';
error_log("complete_duel.php : Tentative d'inclusion de db.php depuis $db_path");

if (!file_exists($db_path)) {
    error_log("complete_duel.php : Fichier $db_path introuvable");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : fichier de configuration de la base de données introuvable.'
    ]);
    exit;
}

require_once $db_path;
error_log("complete_duel.php : db.php inclus");

require_once '../includes/functions.php';
error_log("complete_duel.php : functions.php inclus");

require_once '../includes/functions/duel_functions.php';
error_log("complete_duel.php : duel_functions.php inclus");

// Vérifier que $db est défini et est un objet PDO
if (!isset($db) || $db === null || !($db instanceof PDO)) {
    error_log("complete_duel.php : Variable \$db non définie, null ou non un objet PDO après inclusion initiale");
    // Forcer une réinitialisation de $db
    error_log("complete_duel.php : Tentative de réinitialisation de \$db");
    if (file_exists($db_path)) {
        include_once $db_path;
        error_log("complete_duel.php : db.php ré-inclus pour réinitialisation");
    }
    if (!isset($db) || $db === null || !($db instanceof PDO)) {
        error_log("complete_duel.php : Échec de la réinitialisation de \$db : toujours non défini ou null");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur serveur : connexion à la base de données non établie.'
        ]);
        exit;
    }
}

error_log("complete_duel.php : \$db est un objet PDO valide");

try {
    error_log("complete_duel.php : Début du traitement de la requête");

    // Vérifier si la requête est en POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée. Utilisez POST.");
    }

    // Lire les données JSON
    error_log("complete_duel.php : Lecture des données JSON");
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erreur de format JSON : " . json_last_error_msg());
    }

    // Vérifier les champs requis
    error_log("complete_duel.php : Vérification des champs requis");
    if (!isset($input['duel_id']) || !isset($input['user_id']) || !isset($input['completion_time'])) {
        throw new Exception("Données manquantes : duel_id, user_id ou completion_time requis.");
    }

    $duel_id = (int)$input['duel_id'];
    $user_id = (int)$input['user_id'];
    $completion_time = (int)$input['completion_time'];

    error_log("complete_duel.php : Tentative de complétion du duel $duel_id par l'utilisateur $user_id avec completion_time $completion_time");

    // Vérifier si l'utilisateur est connecté et autorisé
    error_log("complete_duel.php : Vérification de la session utilisateur");
    if (!estConnecte() || $_SESSION['utilisateur_id'] != $user_id) {
        throw new Exception("Utilisateur non autorisé ou non connecté.");
    }

    // Récupérer les informations du duel
    error_log("complete_duel.php : Appel de getDuelById pour duel_id=$duel_id");
    $duel = getDuelById($duel_id);
    if (!$duel) {
        throw new Exception("Duel ID $duel_id introuvable.");
    }

    error_log("complete_duel.php : Vérification du statut du duel");
    if ($duel['status'] !== 'active') {
        throw new Exception("Le duel n'est pas actif. Statut actuel : " . $duel['status']);
    }

    if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
        throw new Exception("L'utilisateur $user_id n'est pas participant de ce duel.");
    }

    // Vérifier si l'utilisateur a déjà complété le duel
    error_log("complete_duel.php : Vérification si le duel est déjà complété par l'utilisateur $user_id");
    $stmt = $db->prepare("SELECT * FROM duel_results WHERE duel_id = ? AND user_id = ?");
    $stmt->execute([$duel_id, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception("L'utilisateur a déjà complété ce duel.");
    }

    // Compter le nombre total de questions
    error_log("complete_duel.php : Comptage des questions pour duel_id=$duel_id");
    $stmt = $db->prepare("SELECT COUNT(*) as total_questions FROM duel_questions WHERE duel_id = ?");
    $stmt->execute([$duel_id]);
    $total_questions = $stmt->fetchColumn();

    if ($total_questions == 0) {
        throw new Exception("Aucune question trouvée pour ce duel.");
    }

    // Calculer le score (nombre de réponses correctes)
    error_log("complete_duel.php : Calcul du score pour l'utilisateur $user_id dans le duel $duel_id");
    $stmt = $db->prepare("
        SELECT COUNT(*) as score
        FROM duel_answers da
        JOIN options r ON da.answer_id = r.id
        WHERE da.duel_id = ? AND da.user_id = ? AND r.est_correcte = '1'
    ");
    $stmt->execute([$duel_id, $user_id]);
    $score = $stmt->fetchColumn();

    error_log("complete_duel.php : Score calculé pour l'utilisateur $user_id dans le duel $duel_id : $score / $total_questions");

    // Enregistrer le résultat de l'utilisateur
    error_log("complete_duel.php : Enregistrement du résultat pour l'utilisateur $user_id");
    $stmt = $db->prepare("
        INSERT INTO duel_results (duel_id, user_id, score, completion_time, completed_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$duel_id, $user_id, $score, $completion_time]);

    // Vérifier si les deux joueurs ont complété
    error_log("complete_duel.php : Vérification si le duel est complété par les deux joueurs");
    $stmt = $db->prepare("SELECT COUNT(*) FROM duel_results WHERE duel_id = ?");
    $stmt->execute([$duel_id]);
    $results_count = $stmt->fetchColumn();

    $duel_completed = ($results_count >= 2);
    $winner_id = null;

    if ($duel_completed) {
        // Récupérer les scores des deux joueurs
        error_log("complete_duel.php : Récupération des scores pour déterminer le gagnant");
        $stmt = $db->prepare("SELECT user_id, score, completion_time FROM duel_results WHERE duel_id = ?");
        $stmt->execute([$duel_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $player1 = $results[0];
        $player2 = $results[1];

        // Déterminer le gagnant en fonction du type de duel
        error_log("complete_duel.php : Détermination du gagnant pour duel type={$duel['type']}");
        if ($duel['type'] === 'timed') {
            if ($player1['score'] == $player2['score']) {
                $winner_id = ($player1['completion_time'] < $player2['completion_time']) ? $player1['user_id'] : $player2['user_id'];
            } else {
                $winner_id = ($player1['score'] > $player2['score']) ? $player1['user_id'] : $player2['user_id'];
            }
        } else {
            // 'accuracy' ou 'mixed'
            if ($player1['score'] == $player2['score']) {
                $winner_id = null; // Match nul
            } else {
                $winner_id = ($player1['score'] > $player2['score']) ? $player1['user_id'] : $player2['user_id'];
            }
        }

        // Mettre à jour le statut du duel
        error_log("complete_duel.php : Mise à jour du statut du duel $duel_id avec winner_id=" . ($winner_id ?? 'null'));
        $stmt = $db->prepare("UPDATE duels SET status = 'completed', winner_id = ?, completed_at = NOW() WHERE id = ?");
        $stmt->execute([$winner_id, $duel_id]);

        error_log("complete_duel.php : Duel $duel_id marqué comme complété avec winner_id = " . ($winner_id ?? 'null'));
    }

    error_log("complete_duel.php : Réponse JSON préparée pour duel_id=$duel_id, duel_completed=$duel_completed, winner_id=" . ($winner_id ?? 'null'));
    echo json_encode([
        'success' => true,
        'duel_completed' => $duel_completed,
        'winner_id' => $winner_id
    ]);

} catch (Exception $e) {
    error_log("complete_duel.php : Erreur lors de la complétion du duel $duel_id : " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log("complete_duel.php : Erreur inattendue lors de la complétion du duel $duel_id : " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Erreur serveur interne. Veuillez réessayer plus tard."
    ]);
}
?>