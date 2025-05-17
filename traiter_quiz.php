<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

// Extraire les données
$categorie_id = (int)($data['categorie_id'] ?? 0);
$difficulte_id = (int)($data['difficulte_id'] ?? 0);
$score = (int)($data['score'] ?? 0);
$total = (int)($data['total'] ?? 0);
$reponses = $data['reponses'] ?? [];

// Vérifier les données
if ($categorie_id <= 0 || $difficulte_id <= 0 || $total <= 0 || empty($reponses)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    exit;
}

// Ajouter un log pour chaque soumission
$log_message = date('Y-m-d H:i:s') . " - Quiz soumis: utilisateur_id=" . ($_SESSION['utilisateur_id'] ?? 'non_connecte') . 
               ", categorie_id=$categorie_id, difficulte_id=$difficulte_id, score=$score, total=$total\n";
file_put_contents('quiz_log.txt', $log_message, FILE_APPEND);

// Si l'utilisateur est connecté, enregistrer le résultat
if (estConnecte()) {
    $utilisateur_id = $_SESSION['utilisateur_id'];
    
    // Vérifier si un quiz identique a été soumis récemment (dernières 5 secondes)
    $db = (new Database())->connect();
    $query = "SELECT COUNT(*) FROM quiz_completes 
              WHERE utilisateur_id = :utilisateur_id 
              AND categorie_id = :categorie_id 
              AND difficulte_id = :difficulte_id 
              AND score = :score 
              AND total = :total 
              AND date_completion > NOW() - INTERVAL 5 SECOND";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->bindParam(':categorie_id', $categorie_id);
    $stmt->bindParam(':difficulte_id', $difficulte_id);
    $stmt->bindParam(':score', $score);
    $stmt->bindParam(':total', $total);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        // Loguer qu’un doublon a été détecté
        file_put_contents('quiz_log.txt', date('Y-m-d H:i:s') . " - Doublon détecté et bloqué pour utilisateur_id=$utilisateur_id\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Quiz déjà enregistré récemment']);
        exit;
    }
    
    // Enregistrer le quiz
    $quiz_id = enregistrerQuizComplete($utilisateur_id, $categorie_id, $difficulte_id, $score, $total, $reponses);
    
    if ($quiz_id) {
        // Loguer le succès
        file_put_contents('quiz_log.txt', date('Y-m-d H:i:s') . " - Quiz enregistré avec succès, quiz_id=$quiz_id\n", FILE_APPEND);
        echo json_encode([
            'success' => true, 
            'message' => 'Quiz enregistré avec succès',
            'quiz_id' => $quiz_id
        ]);
    } else {
        // Loguer l’échec
        file_put_contents('quiz_log.txt', date('Y-m-d H:i:s') . " - Erreur lors de l’enregistrement du quiz pour utilisateur_id=$utilisateur_id\n", FILE_APPEND);
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l’enregistrement du quiz']);
    }
} else {
    // Si l'utilisateur n'est pas connecté, renvoyer un succès sans enregistrer
    file_put_contents('quiz_log.txt', date('Y-m-d H:i:s') . " - Quiz terminé mais non enregistré (utilisateur non connecté)\n", FILE_APPEND);
    echo json_encode([
        'success' => true, 
        'message' => 'Quiz terminé, mais non enregistré (utilisateur non connecté)',
        'non_connecte' => true
    ]);
}
?>