<?php
// Set error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['est_admin']) || $_SESSION['est_admin'] !== true) {
    echo "Accès non autorisé";
    exit;
}

// Include necessary files
require_once '../../includes/config.php';
require_once '../includes/functions.php';

echo "<h1>Débogage des Quiz</h1>";

// Get quiz ID from GET parameter
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Connect to database
$database = new Database();
$db = $database->connect();

// If quiz ID is provided, show detailed information
if ($quiz_id > 0) {
    echo "<h2>Informations détaillées du Quiz #$quiz_id</h2>";
    
    try {
        // Get quiz information
        $query = "SELECT uq.*, c.nom as categorie_nom, u.nom as utilisateur_nom, d.nom as difficulte_nom 
                  FROM user_quizzes uq 
                  LEFT JOIN categories c ON uq.categorie_id = c.id 
                  LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id 
                  LEFT JOIN difficultes d ON uq.difficulte_id = d.id
                  WHERE uq.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $quiz_id);
        $stmt->execute();
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($quiz) {
            echo "<h3>Détails du quiz</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Champ</th><th>Valeur</th></tr>";
            
            foreach ($quiz as $key => $value) {
                echo "<tr><td>$key</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
            }
            
            echo "</table>";
            
            // Get questions
            $query = "SELECT * FROM user_quiz_questions WHERE user_quiz_id = :quiz_id ORDER BY id ASC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quiz_id', $quiz_id);
            $stmt->execute();
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Questions (" . count($questions) . ")</h3>";
            
            if (empty($questions)) {
                echo "<p style='color:red'>Ce quiz ne contient aucune question!</p>";
            } else {
                foreach ($questions as $index => $question) {
                    echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
                    echo "<h4>Question #" . ($index + 1) . " (ID: " . $question['id'] . ")</h4>";
                    echo "<p><strong>Texte:</strong> " . htmlspecialchars($question['question']) . "</p>";
                    
                    // Get options
                    $query = "SELECT * FROM user_quiz_options WHERE user_quiz_question_id = :question_id ORDER BY id ASC";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':question_id', $question['id']);
                    $stmt->execute();
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<p><strong>Options (" . count($options) . "):</strong></p>";
                    
                    if (empty($options)) {
                        echo "<p style='color:red'>Cette question n'a pas d'options!</p>";
                    } else {
                        echo "<ul>";
                        foreach ($options as $option) {
                            $correct = $option['est_correcte'] == 1 ? " ✓" : "";
                            echo "<li>" . htmlspecialchars($option['texte']) . " (ID: " . $option['id'] . ")" . $correct . "</li>";
                        }
                        echo "</ul>";
                    }
                    
                    echo "</div>";
                }
            }
            
            echo "<h3>Test de l'API</h3>";
            echo "<p>Résultat de l'appel à get_quiz_details.php?id=$quiz_id:</p>";
            
            // Test API call using file_get_contents instead of curl
            $api_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get_quiz_details.php?id=$quiz_id";
            echo "<p>URL: " . htmlspecialchars($api_url) . "</p>";
            
            // Create context with cookies to maintain session
            $options = [
                'http' => [
                    'header' => "Cookie: " . session_name() . "=" . session_id() . "\r\n"
                ]
            ];
            $context = stream_context_create($options);
            
            try {
                $response = file_get_contents($api_url, false, $context);
                if ($response === false) {
                    echo "<p style='color:red'>Erreur lors de l'appel à l'API</p>";
                } else {
                    echo "<pre>" . htmlspecialchars($response) . "</pre>";
                    
                    // Parse JSON response
                    $json = json_decode($response, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo "<p style='color:red'>Erreur de décodage JSON: " . json_last_error_msg() . "</p>";
                    } else {
                        echo "<p style='color:green'>JSON valide!</p>";
                        
                        // Display parsed JSON
                        echo "<h4>Contenu JSON décodé:</h4>";
                        echo "<pre>" . htmlspecialchars(print_r($json, true)) . "</pre>";
                    }
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
            }
            
            echo "<h3>Actions</h3>";
            echo "<p><a href='validate_create.php' class='btn btn-outline'>Retour à la validation</a></p>";
        } else {
            echo "<p style='color:red'>Quiz non trouvé!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
    }
} else {
    // Show list of quizzes
    echo "<h2>Liste des Quiz</h2>";
    
    try {
        $query = "SELECT uq.id, uq.titre, uq.status, c.nom as categorie_nom, u.nom as utilisateur_nom 
                  FROM user_quizzes uq 
                  LEFT JOIN categories c ON uq.categorie_id = c.id 
                  LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id 
                  ORDER BY uq.id DESC LIMIT 50";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($quizzes)) {
            echo "<p>Aucun quiz trouvé.</p>";
        } else {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Titre</th><th>Catégorie</th><th>Créateur</th><th>Statut</th><th>Actions</th></tr>";
            
            foreach ($quizzes as $quiz) {
                echo "<tr>";
                echo "<td>" . $quiz['id'] . "</td>";
                echo "<td>" . htmlspecialchars($quiz['titre']) . "</td>";
                echo "<td>" . htmlspecialchars($quiz['categorie_nom'] ?? 'Non catégorisé') . "</td>";
                echo "<td>" . htmlspecialchars($quiz['utilisateur_nom']) . "</td>";
                echo "<td>" . $quiz['status'] . "</td>";
                echo "<td><a href='?id=" . $quiz['id'] . "'>Déboguer</a></td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 20px;
}

h1, h2, h3, h4 {
    color: #333;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
}

th {
    background-color: #f2f2f2;
}

pre {
    background-color: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    overflow: auto;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4f46e5;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}

.btn-outline {
    background-color: transparent;
    color: #4f46e5;
    border: 1px solid #4f46e5;
}
</style>