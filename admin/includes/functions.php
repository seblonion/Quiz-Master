<?php
require_once __DIR__ . '/../../includes/db.php';

// Vérifier si l'utilisateur est connecté
function estConnecte() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['utilisateur_id']) && !empty($_SESSION['utilisateur_id']);
}

// Vérifier si l'utilisateur est connecté et est un admin
function verifierAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['est_admin']) || $_SESSION['est_admin'] !== true) {
        header('Location: /quizmaster/admin/login.php');
        exit;
    }
}

// Fonction pour se connecter en tant qu'admin
function loginAdmin($email, $mot_de_passe) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT id, nom, email, mot_de_passe, est_admin FROM utilisateurs WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier le mot de passe
        if (password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Vérifier si c'est un admin
            if ($utilisateur['est_admin'] == 1) {
                // Démarrer la session et y stocker les infos utilisateur
                session_start();
                $_SESSION['utilisateur_id'] = $utilisateur['id'];
                $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
                $_SESSION['utilisateur_email'] = $utilisateur['email'];
                $_SESSION['est_admin'] = true;
                
                return true;
            }
        }
    }
    
    return false;
}

// Fonction pour se déconnecter
function logoutAdmin() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fonction pour obtenir la liste des utilisateurs
function obtenirUtilisateurs() {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT id, nom, email, date_inscription, est_admin FROM utilisateurs ORDER BY date_inscription DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir un utilisateur par son ID
function obtenirUtilisateur($id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT id, nom, email, date_inscription, est_admin FROM utilisateurs WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour mettre à jour un utilisateur
function mettreAJourUtilisateur($id, $nom, $email, $est_admin) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "UPDATE utilisateurs SET nom = :nom, email = :email, est_admin = :est_admin WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':est_admin', $est_admin);
    
    return $stmt->execute();
}

// Fonction pour mettre à jour le mot de passe d'un utilisateur
function mettreAJourMotDePasse($id, $mot_de_passe) {
    $database = new Database();
    $db = $database->connect();
    
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    $query = "UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
    
    return $stmt->execute();
}

// Fonction pour ajouter un utilisateur
function ajouterUtilisateur($nom, $email, $mot_de_passe, $est_admin = 0) {
    $database = new Database();
    $db = $database->connect();
    
    // Vérifier si l'email existe déjà
    $query = "SELECT id FROM utilisateurs WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return false; // Email déjà utilisé
    }
    
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe, est_admin) 
              VALUES (:nom, :email, :mot_de_passe, :est_admin)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
    $stmt->bindParam(':est_admin', $est_admin);
    
    if ($stmt->execute()) {
        return $db->lastInsertId();
    }
    
    return false;
}

// Fonction pour supprimer un utilisateur
function supprimerUtilisateur($id) {
    $database = new Database();
    $db = $database->connect();
    
    // Vérifier si c'est le dernier admin
    $query = "SELECT COUNT(*) as count FROM utilisateurs WHERE est_admin = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] <= 1) {
        // Vérifier si l'utilisateur à supprimer est un admin
        $query = "SELECT est_admin FROM utilisateurs WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utilisateur['est_admin'] == 1) {
            return false; // Ne pas supprimer le dernier admin
        }
    }
    
    try {
        $db->beginTransaction();
        
        // Supprimer les quiz complétés par l'utilisateur
        $query = "DELETE FROM quiz_completes WHERE utilisateur_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Supprimer les badges de l'utilisateur
        $query = "DELETE FROM badges_utilisateurs WHERE utilisateur_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Supprimer l'utilisateur
        $query = "DELETE FROM utilisateurs WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Fonctions pour gérer les questions
function obtenirToutesLesQuestions($page = 1, $par_page = 20, $filtre = []) {
    $database = new Database();
    $db = $database->connect();
    
    $offset = ($page - 1) * $par_page;
    
    $query = "SELECT q.id, q.question, q.categorie_id, q.difficulte_id, 
                     c.nom as categorie_nom, d.nom as difficulte_nom
              FROM questions q
              JOIN categories c ON q.categorie_id = c.id
              JOIN difficultes d ON q.difficulte_id = d.id";
    
    $where_clauses = [];
    $params = [];
    
    // Ajouter des filtres si nécessaire
    if (!empty($filtre['categorie_id'])) {
        $where_clauses[] = "q.categorie_id = :categorie_id";
        $params[':categorie_id'] = $filtre['categorie_id'];
    }
    
    if (!empty($filtre['difficulte_id'])) {
        $where_clauses[] = "q.difficulte_id = :difficulte_id";
        $params[':difficulte_id'] = $filtre['difficulte_id'];
    }
    
    if (!empty($filtre['recherche'])) {
        $where_clauses[] = "q.question LIKE :recherche";
        $params[':recherche'] = '%' . $filtre['recherche'] . '%';
    }
    
    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(' AND ', $where_clauses);
    }
    
    $query .= " ORDER BY q.id DESC LIMIT :offset, :par_page";
    
    $stmt = $db->prepare($query);
    
    // Lier les paramètres de filtre
    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':par_page', $par_page, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre total de questions avec filtres
function obtenirNombreTotalQuestions($filtre = []) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT COUNT(*) as total FROM questions q";
    
    $where_clauses = [];
    $params = [];
    
    // Ajouter des filtres si nécessaire
    if (!empty($filtre['categorie_id'])) {
        $where_clauses[] = "q.categorie_id = :categorie_id";
        $params[':categorie_id'] = $filtre['categorie_id'];
    }
    
    if (!empty($filtre['difficulte_id'])) {
        $where_clauses[] = "q.difficulte_id = :difficulte_id";
        $params[':difficulte_id'] = $filtre['difficulte_id'];
    }
    
    if (!empty($filtre['recherche'])) {
        $where_clauses[] = "q.question LIKE :recherche";
        $params[':recherche'] = '%' . $filtre['recherche'] . '%';
    }
    
    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(' AND ', $where_clauses);
    }
    
    $stmt = $db->prepare($query);
    
    // Lier les paramètres de filtre
    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Fonction pour obtenir une question et ses options
function obtenirQuestion($id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT q.*, c.nom as categorie_nom, d.nom as difficulte_nom 
              FROM questions q
              JOIN categories c ON q.categorie_id = c.id
              JOIN difficultes d ON q.difficulte_id = d.id
              WHERE q.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($question) {
        // Récupérer les options
        $query = "SELECT * FROM options WHERE question_id = :question_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_id', $id);
        $stmt->execute();
        
        $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $question;
}

// Fonction pour ajouter une question
function ajouterQuestion($question, $categorie_id, $difficulte_id, $options) {
    $database = new Database();
    $db = $database->connect();
    
    try {
        $db->beginTransaction();
        
        // Insérer la question
        $query = "INSERT INTO questions (question, categorie_id, difficulte_id) 
                  VALUES (:question, :categorie_id, :difficulte_id)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':difficulte_id', $difficulte_id);
        $stmt->execute();
        
        $question_id = $db->lastInsertId();
        
        // Vérifier qu'une et une seule option est correcte
        $correct_count = 0;
        foreach ($options as $option) {
            if ($option['est_correcte']) {
                $correct_count++;
            }
        }
        
        if ($correct_count !== 1) {
            $db->rollBack();
            return false;
        }
        
        // Insérer les options
        foreach ($options as $option) {
            $texte = $option['texte'];
            $est_correcte = $option['est_correcte'] ? 1 : 0;
            
            $query = "INSERT INTO options (question_id, texte, est_correcte) 
                      VALUES (:question_id, :texte, :est_correcte)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':question_id', $question_id);
            $stmt->bindParam(':texte', $texte);
            $stmt->bindParam(':est_correcte', $est_correcte);
            $stmt->execute();
        }
        
        $db->commit();
        return $question_id;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Fonction pour mettre à jour une question
function mettreAJourQuestion($id, $question, $categorie_id, $difficulte_id, $options) {
    $database = new Database();
    $db = $database->connect();
    
    try {
        $db->beginTransaction();
        
        // Mettre à jour la question
        $query = "UPDATE questions SET 
                  question = :question, 
                  categorie_id = :categorie_id, 
                  difficulte_id = :difficulte_id 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':difficulte_id', $difficulte_id);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Vérifier qu'une et une seule option est correcte
        $correct_count = 0;
        foreach ($options as $option) {
            if ($option['est_correcte']) {
                $correct_count++;
            }
        }
        
        if ($correct_count !== 1) {
            $db->rollBack();
            return false;
        }
        
        // Supprimer les anciennes options
        $query = "DELETE FROM options WHERE question_id = :question_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_id', $id);
        $stmt->execute();
        
        // Insérer les nouvelles options
        foreach ($options as $option) {
            $texte = $option['texte'];
            $est_correcte = $option['est_correcte'] ? 1 : 0;
            
            $query = "INSERT INTO options (question_id, texte, est_correcte) 
                      VALUES (:question_id, :texte, :est_correcte)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':question_id', $id);
            $stmt->bindParam(':texte', $texte);
            $stmt->bindParam(':est_correcte', $est_correcte);
            $stmt->execute();
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Fonction pour supprimer une question
function supprimerQuestion($id) {
    $database = new Database();
    $db = $database->connect();
    
    try {
        $db->beginTransaction();
        
        // Supprimer les options
        $query = "DELETE FROM options WHERE question_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Supprimer la question
        $query = "DELETE FROM questions WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Fonctions pour gérer les catégories
function obtenirToutesLesCategories() {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT * FROM categories ORDER BY nom";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir une catégorie par son ID
function obtenirCategorieAdmin($id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre de questions par catégorie
function obtenirNombreQuestionsParCategorie($categorie_id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT COUNT(*) as total FROM questions WHERE categorie_id = :categorie_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':categorie_id', $categorie_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function ajouterCategorie($nom, $description, $icone, $couleur) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "INSERT INTO categories (nom, description, icone, couleur) 
              VALUES (:nom, :description, :icone, :couleur)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':icone', $icone);
    $stmt->bindParam(':couleur', $couleur);
    
    return $stmt->execute() ? $db->lastInsertId() : false;
}

function mettreAJourCategorie($id, $nom, $description, $icone, $couleur) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "UPDATE categories SET 
              nom = :nom, 
              description = :description, 
              icone = :icone, 
              couleur = :couleur 
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':icone', $icone);
    $stmt->bindParam(':couleur', $couleur);
    
    return $stmt->execute();
}

function supprimerCategorie($id) {
    $database = new Database();
    $db = $database->connect();
    
    // Vérifier si des questions utilisent cette catégorie
    $query = "SELECT COUNT(*) as count FROM questions WHERE categorie_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        // Des questions utilisent cette catégorie, ne pas supprimer
        return false;
    }
    
    // Supprimer la catégorie
    $query = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

// Fonctions pour gérer les difficultés
function obtenirToutesLesDifficultes() {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT * FROM difficultes ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir une difficulté par son ID
function obtenirDifficulteAdmin($id) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT * FROM difficultes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonctions pour les statistiques
function obtenirStatistiquesGenerales() {
    $database = new Database();
    $db = $database->connect();
    
    $stats = [];
    
    // Nombre total d'utilisateurs
    $query = "SELECT COUNT(*) as total FROM utilisateurs";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['utilisateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre total de questions
    $query = "SELECT COUNT(*) as total FROM questions";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['questions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre total de quiz complétés
    $query = "SELECT COUNT(*) as total FROM quiz_completes";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['quiz_completes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Score moyen global
    $query = "SELECT AVG(score / total * 100) as moyenne FROM quiz_completes";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['score_moyen'] = round($stmt->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0, 2);
    
    // Nombre de quiz par catégorie
    $query = "SELECT c.nom, COUNT(qc.id) as total 
              FROM categories c
              LEFT JOIN quiz_completes qc ON c.id = qc.categorie_id
              GROUP BY c.id
              ORDER BY total DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['quiz_par_categorie'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Nombre de quiz par difficulté
    $query = "SELECT d.nom, COUNT(qc.id) as total 
              FROM difficultes d
              LEFT JOIN quiz_completes qc ON d.id = qc.difficulte_id
              GROUP BY d.id
              ORDER BY d.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['quiz_par_difficulte'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Évolution par mois
    $query = "SELECT DATE_FORMAT(date_completion, '%Y-%m') as mois, 
                     COUNT(*) as total_quiz,
                     AVG(score / total * 100) as score_moyen
              FROM quiz_completes
              GROUP BY DATE_FORMAT(date_completion, '%Y-%m')
              ORDER BY mois ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['evolution_mensuelle'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $stats;
}

// Fonction pour obtenir les utilisateurs les plus actifs
function obtenirUtilisateursActifs($limite = 10) {
    $database = new Database();
    $db = $database->connect();
    
    $query = "SELECT u.id, u.nom, COUNT(qc.id) as total_quiz, 
                     AVG(qc.score / qc.total * 100) as score_moyen
              FROM utilisateurs u
              JOIN quiz_completes qc ON u.id = qc.utilisateur_id
              GROUP BY u.id
              ORDER BY total_quiz DESC
              LIMIT :limite";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour formater un message flash
function mettreMessageFlash($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}
?>