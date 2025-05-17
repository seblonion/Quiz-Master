<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !estConnecte()) {
    $_SESSION['message'] = "Vous devez être connecté pour soumettre un quiz.";
    $_SESSION['message_type'] = "error";
    rediriger('register.php');
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$categorie_id = !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null;
$time_limit = !empty($_POST['time_limit']) ? (int)$_POST['time_limit'] : null;
$questions = $_POST['questions'] ?? [];

// Server-side validation
$errors = [];
if (empty($titre) || strlen($titre) > 100) {
    $errors[] = "Le titre est requis et doit contenir moins de 100 caractères.";
}
if (empty($description)) {
    $errors[] = "La description est requise.";
}
if ($time_limit && ($time_limit < 5 || $time_limit > 60)) {
    $errors[] = "Le temps par question doit être entre 5 et 60 secondes.";
}
if (empty($questions) || count($questions) < 1) {
    $errors[] = "Au moins une question est requise.";
}
foreach ($questions as $index => $question) {
    if (empty($question['text'])) {
        $errors[] = "La question " . ($index + 1) . " est vide.";
    }
    if (count($question['options']) !== 4) {
        $errors[] = "La question " . ($index + 1) . " doit avoir exactement 4 options.";
    }
    $option_texts = array_map(function($opt) { return trim($opt['text']); }, $question['options']);
    if (count(array_unique($option_texts)) !== 4) {
        $errors[] = "Les options de la question " . ($index + 1) . " doivent être uniques.";
    }
    if (!isset($question['correct_option']) || !in_array($question['correct_option'], ['0', '1', '2', '3'])) {
        $errors[] = "Une réponse correcte doit être sélectionnée pour la question " . ($index + 1) . ".";
    }
}

if (!empty($errors)) {
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['message_type'] = "error";
    rediriger('create_quiz.php');
}

// Save quiz
$db = (new Database())->connect();
$db->beginTransaction();
try {
    // Insert quiz
    $query = "INSERT INTO user_quizzes (utilisateur_id, titre, description, categorie_id, time_limit_per_question)
              VALUES (:utilisateur_id, :titre, :description, :categorie_id, :time_limit)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
    $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
    $stmt->bindParam(':time_limit', $time_limit, PDO::PARAM_INT);
    $stmt->execute();
    $quiz_id = $db->lastInsertId();

    // Insert questions and options
    foreach ($questions as $question) {
        $query = "INSERT INTO user_quiz_questions (user_quiz_id, question) VALUES (:quiz_id, :question)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
        $stmt->bindParam(':question', $question['text'], PDO::PARAM_STR);
        $stmt->execute();
        $question_id = $db->lastInsertId();

        foreach ($question['options'] as $index => $option) {
            $est_correcte = ($index == $question['correct_option']) ? 1 : 0;
            $query = "INSERT INTO user_quiz_options (user_quiz_question_id, texte, est_correcte)
                      VALUES (:question_id, :texte, :est_correcte)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
            $stmt->bindParam(':texte', $option['text'], PDO::PARAM_STR);
            $stmt->bindParam(':est_correcte', $est_correcte, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    $db->commit();
    $_SESSION['message'] = "Quiz soumis avec succès. Il est en attente de validation.";
    $_SESSION['message_type'] = "success";
    rediriger('profil.php');
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['message'] = "Erreur lors de la soumission du quiz : " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    rediriger('create_quiz.php');
}
?>