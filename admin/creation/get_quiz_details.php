<?php
// Ensure no output before JSON
ob_start();

// Set content type header
header('Content-Type: application/json; charset=utf-8');

// Disable error display in production
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Check for included files
    $configPath = __DIR__ . '/../../includes/config.php';
    $functionsPath = __DIR__ . '/../includes/functions.php';
    if (!file_exists($configPath) || !file_exists($functionsPath)) {
        throw new Exception('Fichiers de configuration manquants');
    }

    // Include required files
    require_once $configPath;
    require_once $functionsPath;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Debug session
    error_log('Session ID: ' . session_id());
    error_log('Session contenu: ' . json_encode($_SESSION, JSON_PRETTY_PRINT));

    // Verify user is connected and admin
    if (!isset($_SESSION['utilisateur_id']) || empty($_SESSION['utilisateur_id'])) {
        throw new Exception('Utilisateur non connecté');
    }
    if (!isset($_SESSION['est_admin']) || $_SESSION['est_admin'] !== true) {
        throw new Exception('Accès non autorisé');
    }

    // Check if quiz ID is provided
    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID du quiz non spécifié ou invalide');
    }

    $quiz_id = (int)$_GET['id'];
    $database = new Database();
    $db = $database->connect();
    if (!$db) {
        throw new Exception('Échec de la connexion à la base de données');
    }

    // Get quiz information with contributor status
    $query = "SELECT uq.*, c.nom as categorie_nom, c.couleur as categorie_couleur, c.icone as categorie_icone, 
              u.nom as utilisateur_nom, u.est_contributeur, d.nom as difficulte_nom 
              FROM user_quizzes uq 
              LEFT JOIN categories c ON uq.categorie_id = c.id 
              LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id 
              LEFT JOIN difficultes d ON uq.difficulte_id = d.id
              WHERE uq.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $quiz_id);
    if (!$stmt->execute()) {
        throw new Exception('Erreur lors de la récupération du quiz: ' . implode(', ', $stmt->errorInfo()));
    }
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        throw new Exception('Quiz non trouvé');
    }

    // Get questions
    $query = "SELECT * FROM user_quiz_questions WHERE user_quiz_id = :quiz_id ORDER BY id ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':quiz_id', $quiz_id);
    if (!$stmt->execute()) {
        throw new Exception('Erreur lors de la récupération des questions: ' . implode(', ', $stmt->errorInfo()));
    }
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each question, get options
    foreach ($questions as &$question) {
        $query = "SELECT * FROM user_quiz_options WHERE user_quiz_question_id = :question_id ORDER BY id ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_id', $question['id']);
        if (!$stmt->execute()) {
            throw new Exception('Erreur lors de la récupération des options: ' . implode(', ', $stmt->errorInfo()));
        }
        $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add questions to quiz
    $quiz['questions'] = $questions;

    // Get additional statistics if quiz is approved
    if ($quiz['status'] === 'approved') {
        try {
            // Number of completions
            $query = "SELECT COUNT(*) as completions FROM quiz_completes WHERE quiz_id = :quiz_id AND type = 'community'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quiz_id', $quiz_id);
            if (!$stmt->execute()) {
                throw new Exception('Erreur lors de la récupération des statistiques');
            }
            $completions = $stmt->fetch(PDO::FETCH_ASSOC);
            $quiz['completions'] = $completions ? (int)$completions['completions'] : 0;

            // Average score
            $query = "SELECT AVG(score / total * 100) as avg_score FROM quiz_completes WHERE quiz_id = :quiz_id AND type = 'community'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quiz_id', $quiz_id);
            if (!$stmt->execute()) {
                throw new Exception('Erreur lors de la récupération du score moyen');
            }
            $avg_score = $stmt->fetch(PDO::FETCH_ASSOC);
            $quiz['avg_score'] = $avg_score && $avg_score['avg_score'] ? round($avg_score['avg_score'], 1) : 0;
        } catch (Exception $e) {
            error_log('Erreur dans les statistiques: ' . $e->getMessage());
            $quiz['completions'] = 0;
            $quiz['avg_score'] = 0;
        }
    }

    // Return success response
    echo json_encode(['success' => true, 'quiz' => $quiz], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    // Log error to file
    error_log('Erreur dans get_quiz_details.php: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
    // Clear any output that might have been generated
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

// Clear the output buffer and send the response
ob_end_flush();
?>