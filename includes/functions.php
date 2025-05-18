<?php
require_once 'db.php';
// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}
// Fonction pour rediriger vers une page
function rediriger($page) {
    header("Location: $page");
    exit;
}
// Fonction pour sécuriser les données
function securiser($donnee) {
    $donnee = trim($donnee);
    $donnee = stripslashes($donnee);
    $donnee = htmlspecialchars($donnee);
    return $donnee;
}
// Fonction pour obtenir toutes les catégories
function obtenirCategories() {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT * FROM categories ORDER BY nom";
    $stmt = $db->prepare($query);
    $stmt->execute();
   
    return $stmt->fetchAll();
}
// Fonction pour obtenir une catégorie par son ID
function obtenirCategorie($id) {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
   
    return $stmt->fetch();
}
// Fonction pour obtenir tous les niveaux de difficulté
function obtenirDifficultes() {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT * FROM difficultes ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
   
    return $stmt->fetchAll();
}
// Fonction pour obtenir une difficulté par son ID
function obtenirDifficulte($id) {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT * FROM difficultes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
   
    return $stmt->fetch();
}
// Fonction pour obtenir les questions d'un quiz
function obtenirQuestions($categorie_id, $difficulte_id, $limite = 10) {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT q.*, c.nom as categorie_nom, d.nom as difficulte_nom
              FROM questions q
              JOIN categories c ON q.categorie_id = c.id
              JOIN difficultes d ON q.difficulte_id = d.id
              WHERE q.categorie_id = :categorie_id AND q.difficulte_id = :difficulte_id
              ORDER BY RAND()
              LIMIT :limite";
   
    $stmt = $db->prepare($query);
    $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
    $stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
   
    $questions = $stmt->fetchAll();
   
    // Pour chaque question, récupérer les options
    foreach ($questions as &$question) {
        $query = "SELECT * FROM options WHERE question_id = :question_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_id', $question['id']);
        $stmt->execute();
        $question['options'] = $stmt->fetchAll();
    }
   
    return $questions;
}
// Fonction pour enregistrer un quiz complété
function enregistrerQuizComplete($utilisateur_id, $categorie_id, $difficulte_id, $score, $total, $reponses) {
    $database = new Database();
    $db = $database->connect();
   
    try {
        $db->beginTransaction();
       
        // Insérer le quiz complété
        $query = "INSERT INTO quiz_completes (utilisateur_id, categorie_id, difficulte_id, score, total)
                  VALUES (:utilisateur_id, :categorie_id, :difficulte_id, :score, :total)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':difficulte_id', $difficulte_id);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':total', $total);
        $stmt->execute();
       
        $quiz_complete_id = $db->lastInsertId();
       
        // Insérer les réponses de l'utilisateur
        foreach ($reponses as $question_id => $option_id) {
            $query = "INSERT INTO reponses_utilisateurs (quiz_complete_id, question_id, option_id)
                      VALUES (:quiz_complete_id, :question_id, :option_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quiz_complete_id', $quiz_complete_id);
            $stmt->bindParam(':question_id', $question_id);
            $stmt->bindParam(':option_id', $option_id);
            $stmt->execute();
        }
       
        // Vérifier si l'utilisateur a obtenu de nouveaux badges
        verifierBadges($utilisateur_id);
       
        $db->commit();
        return $quiz_complete_id;
    } catch (Exception $e) {
        $db->rollBack();
        echo "Erreur: " . $e->getMessage();
        return false;
    }
}
// Fonction pour vérifier si l'utilisateur a obtenu de nouveaux badges
function verifierBadges($utilisateur_id) {
    $database = new Database();
    $db = $database->connect();
   
    // Récupérer tous les badges
    $query = "SELECT * FROM badges";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $badges = $stmt->fetchAll();
   
    foreach ($badges as $badge) {
        // Vérifier si l'utilisateur a déjà ce badge
        $query = "SELECT * FROM badges_utilisateurs WHERE utilisateur_id = :utilisateur_id AND badge_id = :badge_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->bindParam(':badge_id', $badge['id']);
        $stmt->execute();
       
        if ($stmt->rowCount() == 0) {
            // L'utilisateur n'a pas encore ce badge, vérifier s'il remplit les conditions
            $conditions_remplies = true;
           
            // Condition sur la catégorie
            if ($badge['categorie_id'] !== null) {
                $query = "SELECT COUNT(*) as total FROM quiz_completes
                          WHERE utilisateur_id = :utilisateur_id AND categorie_id = :categorie_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $utilisateur_id);
                $stmt->bindParam(':categorie_id', $badge['categorie_id']);
                $stmt->execute();
                $result = $stmt->fetch();
               
                if ($result['total'] < ($badge['condition_nombre_quiz'] ?? 1)) {
                    $conditions_remplies = false;
                }
            }
           
            // Condition sur la difficulté
            if ($conditions_remplies && $badge['difficulte_id'] !== null) {
                $query = "SELECT COUNT(*) as total FROM quiz_completes
                          WHERE utilisateur_id = :utilisateur_id AND difficulte_id = :difficulte_id";
                if ($badge['categorie_id'] !== null) {
                    $query .= " AND categorie_id = :categorie_id";
                }
               
                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $utilisateur_id);
                $stmt->bindParam(':difficulte_id', $badge['difficulte_id']);
               
                if ($badge['categorie_id'] !== null) {
                    $stmt->bindParam(':categorie_id', $badge['categorie_id']);
                }
               
                $stmt->execute();
                $result = $stmt->fetch();
               
                if ($result['total'] < ($badge['condition_nombre_quiz'] ?? 1)) {
                    $conditions_remplies = false;
                }
            }
           
            // Condition sur le score
            if ($conditions_remplies && $badge['condition_score'] !== null) {
                $query = "SELECT COUNT(*) as total FROM quiz_completes
                          WHERE utilisateur_id = :utilisateur_id AND (score / total * 100) >= :score";
               
                if ($badge['categorie_id'] !== null) {
                    $query .= " AND categorie_id = :categorie_id";
                }
               
                if ($badge['difficulte_id'] !== null) {
                    $query .= " AND difficulte_id = :difficulte_id";
                }
               
                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $utilisateur_id);
                $stmt->bindParam(':score', $badge['condition_score']);
               
                if ($badge['categorie_id'] !== null) {
                    $stmt->bindParam(':categorie_id', $badge['categorie_id']);
                }
               
                if ($badge['difficulte_id'] !== null) {
                    $stmt->bindParam(':difficulte_id', $badge['difficulte_id']);
                }
               
                $stmt->execute();
                $result = $stmt->fetch();
               
                if ($result['total'] < ($badge['condition_nombre_quiz'] ?? 1)) {
                    $conditions_remplies = false;
                }
            }
           
            // Si toutes les conditions sont remplies, attribuer le badge
            if ($conditions_remplies) {
                $query = "INSERT INTO badges_utilisateurs (utilisateur_id, badge_id)
                          VALUES (:utilisateur_id, :badge_id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $utilisateur_id);
                $stmt->bindParam(':badge_id', $badge['id']);
                $stmt->execute();
            }
        }
    }
}
// Fonction pour obtenir les statistiques d'un utilisateur
function obtenirStatistiquesUtilisateur($utilisateur_id) {
    $database = new Database();
    $db = $database->connect();
   
    $stats = [];
   
    // Nombre total de quiz complétés
    $query = "SELECT COUNT(*) as total FROM quiz_completes WHERE utilisateur_id = :utilisateur_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['total_quiz'] = $stmt->fetch()['total'];
   
    // Score moyen
    $query = "SELECT AVG(score / total * 100) as moyenne FROM quiz_completes WHERE utilisateur_id = :utilisateur_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['score_moyen'] = round($stmt->fetch()['moyenne'] ?? 0);
   
    // Statistiques par catégorie
    $query = "SELECT c.id, c.nom, c.couleur, COUNT(*) as total_quiz,
              AVG(qc.score / qc.total * 100) as score_moyen
              FROM quiz_completes qc
              JOIN categories c ON qc.categorie_id = c.id
              WHERE qc.utilisateur_id = :utilisateur_id
              GROUP BY c.id
              ORDER BY c.nom";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['categories'] = $stmt->fetchAll();
   
    // Statistiques par difficulté
    $query = "SELECT d.id, d.nom, COUNT(*) as total_quiz
              FROM quiz_completes qc
              JOIN difficultes d ON qc.difficulte_id = d.id
              WHERE qc.utilisateur_id = :utilisateur_id
              GROUP BY d.id
              ORDER BY d.id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['difficultes'] = $stmt->fetchAll();
   
    // Progression mensuelle
    $query = "SELECT DATE_FORMAT(date_completion, '%Y-%m') as mois,
              COUNT(*) as total_quiz,
              AVG(score / total * 100) as score_moyen
              FROM quiz_completes
              WHERE utilisateur_id = :utilisateur_id
              GROUP BY DATE_FORMAT(date_completion, '%Y-%m')
              ORDER BY mois DESC
              LIMIT 6";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['progression_mensuelle'] = array_reverse($stmt->fetchAll());
   
    // Badges obtenus
    $query = "SELECT b.*, bu.date_obtention, c.nom as categorie_nom, c.couleur as categorie_couleur
              FROM badges_utilisateurs bu
              JOIN badges b ON bu.badge_id = b.id
              LEFT JOIN categories c ON b.categorie_id = c.id
              WHERE bu.utilisateur_id = :utilisateur_id
              ORDER BY bu.date_obtention DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['badges'] = $stmt->fetchAll();
   
    // Quiz récents
    $query = "SELECT qc.*, c.nom as categorie_nom, d.nom as difficulte_nom
              FROM quiz_completes qc
              JOIN categories c ON qc.categorie_id = c.id
              JOIN difficultes d ON qc.difficulte_id = d.id
              WHERE qc.utilisateur_id = :utilisateur_id
              ORDER BY qc.date_completion DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt->execute();
    $stats['quiz_recents'] = $stmt->fetchAll();
   
    return $stats;
}

function countUnreadNotifications($utilisateur_id) {
    $database = new Database();
    $db = $database->connect();
   
    $query = "SELECT COUNT(*) as count FROM notifications WHERE utilisateur_id = :utilisateur_id AND is_read = 0";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
    $stmt->execute();
   
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['count'];
}

function obtenirUtilisateur($user_id) {
    global $db;
    try {
        $stmt = $db->prepare("SELECT id, nom, est_contributeur FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        return $utilisateur ?: false;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de l'utilisateur $user_id : " . $e->getMessage());
        return false;
    }
}

?>