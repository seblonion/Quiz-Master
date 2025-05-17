<?php
// Au début du fichier, après les includes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config.php'; // Remonte de admin/users/ à QuizMaster/, puis va dans includes/
require_once '../includes/functions.php'; // functions.php est dans admin/includes/

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Fonction pour obtenir la classe CSS en fonction du niveau de difficulté
function getDifficultyClass($difficultyId) {
    switch (intval($difficultyId)) {
        case 1:
            return 'success'; // Facile
        case 2:
            return 'warning'; // Moyen
        case 3:
            return 'danger';  // Difficile
        default:
            return 'secondary';
    }
}

// Le reste du code
$database = new Database();
$db = $database->connect();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $admin_comment = isset($_POST['admin_comment']) ? trim($_POST['admin_comment']) : '';
    
    if ($quiz_id > 0) {
        if ($action === 'approve') {
            // Approuver le quiz
            $query = "UPDATE user_quizzes SET status = 'approved', admin_comment = :admin_comment WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $quiz_id);
            $stmt->bindParam(':admin_comment', $admin_comment);
            
            if ($stmt->execute()) {
                // Récupérer l'ID de l'utilisateur qui a créé le quiz
                $query = "SELECT uq.utilisateur_id, uq.titre, u.est_contributeur 
                          FROM user_quizzes uq 
                          JOIN utilisateurs u ON uq.utilisateur_id = u.id 
                          WHERE uq.id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $quiz_id);
                $stmt->execute();
                $quiz_info = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($quiz_info) {
                    // Attribuer le rôle de contributeur à l'utilisateur s'il ne l'est pas déjà
                    if (!$quiz_info['est_contributeur']) {
                        $query = "UPDATE utilisateurs SET est_contributeur = 1 WHERE id = :utilisateur_id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':utilisateur_id', $quiz_info['utilisateur_id']);
                        $stmt->execute();
                        
                        // Message spécifique pour nouveau contributeur
                        $message = "Votre quiz \"" . htmlspecialchars($quiz_info['titre']) . "\" a été approuvé et est maintenant disponible. Félicitations ! Vous avez obtenu le statut de contributeur certifié !";
                    } else {
                        // Message pour contributeur existant
                        $message = "Votre quiz \"" . htmlspecialchars($quiz_info['titre']) . "\" a été approuvé et est maintenant disponible.";
                    }
                    
                    if (!empty($admin_comment)) {
                        $message .= " Commentaire de l'administrateur: " . htmlspecialchars($admin_comment);
                    }
                    
                    $query = "INSERT INTO notifications (utilisateur_id, type, message, related_id) 
                              VALUES (:utilisateur_id, 'quiz_approved', :message, :quiz_id)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':utilisateur_id', $quiz_info['utilisateur_id']);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindParam(':quiz_id', $quiz_id);
                    $stmt->execute();
                }
                
                mettreMessageFlash("Le quiz a été approuvé avec succès et l'utilisateur a reçu le statut de contributeur.", "success");
            } else {
                mettreMessageFlash("Une erreur est survenue lors de l'approbation du quiz.", "error");
            }
        } elseif ($action === 'reject') {
            // Rejeter le quiz
            if (empty($admin_comment)) {
                mettreMessageFlash("Veuillez fournir un commentaire expliquant la raison du rejet.", "error");
            } else {
                $query = "UPDATE user_quizzes SET status = 'rejected', admin_comment = :admin_comment WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $quiz_id);
                $stmt->bindParam(':admin_comment', $admin_comment);
                
                if ($stmt->execute()) {
                    // Récupérer l'ID de l'utilisateur qui a créé le quiz
                    $query = "SELECT utilisateur_id, titre FROM user_quizzes WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $quiz_id);
                    $stmt->execute();
                    $quiz_info = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($quiz_info) {
                        // Ajouter une notification
                        $message = "Votre quiz \"" . htmlspecialchars($quiz_info['titre']) . "\" a été rejeté. Raison: " . htmlspecialchars($admin_comment);
                        
                        $query = "INSERT INTO notifications (utilisateur_id, type, message, related_id) 
                                  VALUES (:utilisateur_id, 'quiz_rejected', :message, :quiz_id)";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':utilisateur_id', $quiz_info['utilisateur_id']);
                        $stmt->bindParam(':message', $message);
                        $stmt->bindParam(':quiz_id', $quiz_id);
                        $stmt->execute();
                    }
                    
                    mettreMessageFlash("Le quiz a été rejeté.", "success");
                } else {
                    mettreMessageFlash("Une erreur est survenue lors du rejet du quiz.", "error");
                }
            }
        } elseif ($action === 'edit') {
            // Éditer le quiz
            $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $categorie_id = isset($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : 0;
            $difficulte_id = isset($_POST['difficulte_id']) ? (int)$_POST['difficulte_id'] : 0;
            $questions = isset($_POST['questions']) ? json_decode($_POST['questions'], true) : [];

            if (empty($titre) || empty($description)) {
                mettreMessageFlash("Le titre et la description sont requis.", "error");
            } elseif ($categorie_id <= 0 || $difficulte_id <= 0) {
                mettreMessageFlash("Veuillez sélectionner une catégorie et une difficulté valides.", "error");
            } elseif (empty($questions)) {
                mettreMessageFlash("Le quiz doit contenir au moins une question.", "error");
            } else {
                try {
                    $db->beginTransaction();

                    // Mettre à jour les informations du quiz
                    $query = "UPDATE user_quizzes SET 
                              titre = :titre, 
                              description = :description, 
                              categorie_id = :categorie_id, 
                              difficulte_id = :difficulte_id 
                              WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':titre', $titre);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':categorie_id', $categorie_id);
                    $stmt->bindParam(':difficulte_id', $difficulte_id);
                    $stmt->bindParam(':id', $quiz_id);
                    $stmt->execute();

                    // Supprimer les anciennes questions et options
                    $query = "DELETE FROM user_quiz_options WHERE user_quiz_question_id IN (
                                SELECT id FROM user_quiz_questions WHERE user_quiz_id = :quiz_id
                              )";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':quiz_id', $quiz_id);
                    $stmt->execute();

                    $query = "DELETE FROM user_quiz_questions WHERE user_quiz_id = :quiz_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':quiz_id', $quiz_id);
                    $stmt->execute();

                    // Ajouter les nouvelles questions et options
                    foreach ($questions as $question) {
                        if (empty($question['texte']) || empty($question['options']) || count($question['options']) < 2) {
                            throw new Exception("Chaque question doit avoir un texte et au moins deux options.");
                        }

                        // Vérifier qu'il y a exactement une réponse correcte
                        $correct_count = 0;
                        foreach ($question['options'] as $option) {
                            if ($option['est_correcte']) {
                                $correct_count++;
                            }
                        }
                        if ($correct_count !== 1) {
                            throw new Exception("Chaque question doit avoir exactement une réponse correcte.");
                        }

                        // Insérer la question
                        $query = "INSERT INTO user_quiz_questions (user_quiz_id, question) 
                                  VALUES (:quiz_id, :question)";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':quiz_id', $quiz_id);
                        $stmt->bindParam(':question', $question['texte']);
                        $stmt->execute();
                        $question_id = $db->lastInsertId();

                        // Insérer les options
                        foreach ($question['options'] as $option) {
                            $query = "INSERT INTO user_quiz_options (user_quiz_question_id, texte, est_correcte) 
                                      VALUES (:question_id, :texte, :est_correcte)";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':question_id', $question_id);
                            $stmt->bindParam(':texte', $option['texte']);
                            $stmt->bindParam(':est_correcte', $option['est_correcte'], PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }

                    $db->commit();
                    mettreMessageFlash("Le quiz a été modifié avec succès.", "success");
                } catch (Exception $e) {
                    $db->rollBack();
                    mettreMessageFlash("Erreur lors de la modification du quiz : " . $e->getMessage(), "error");
                }
            }
        } elseif ($action === 'delete') {
            // Supprimer le quiz
            try {
                $db->beginTransaction();

                // Récupérer l'ID de l'utilisateur pour la notification
                $query = "SELECT utilisateur_id, titre FROM user_quizzes WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $quiz_id);
                $stmt->execute();
                $quiz_info = $stmt->fetch(PDO::FETCH_ASSOC);

                // Supprimer les options
                $query = "DELETE FROM user_quiz_options WHERE user_quiz_question_id IN (
                            SELECT id FROM user_quiz_questions WHERE user_quiz_id = :quiz_id
                          )";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->execute();

                // Supprimer les questions
                $query = "DELETE FROM user_quiz_questions WHERE user_quiz_id = :quiz_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->execute();

                // Supprimer le quiz
                $query = "DELETE FROM user_quizzes WHERE id = :quiz_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quiz_id', $quiz_id);
                $stmt->execute();

                if ($quiz_info) {
                    // Ajouter une notification
                    $message = "Votre quiz \"" . htmlspecialchars($quiz_info['titre']) . "\" a été supprimé par un administrateur.";
                    if (!empty($admin_comment)) {
                        $message .= " Commentaire: " . htmlspecialchars($admin_comment);
                    }
                    
                    $query = "INSERT INTO notifications (utilisateur_id, type, message, related_id) 
                              VALUES (:utilisateur_id, 'quiz_deleted', :message, :quiz_id)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':utilisateur_id', $quiz_info['utilisateur_id']);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindParam(':quiz_id', $quiz_id);
                    $stmt->execute();
                }

                $db->commit();
                mettreMessageFlash("Le quiz a été supprimé avec succès.", "success");
            } catch (Exception $e) {
                $db->rollBack();
                mettreMessageFlash("Erreur lors de la suppression du quiz.", "error");
            }
        }
    }
    
    // Rediriger pour éviter la resoumission du formulaire
    header('Location: ' . $_SERVER['PHP_SELF'] . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
    exit;
}

// Récupérer les filtres
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$categorie_id = isset($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : 0;
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$par_page = 10;
$offset = ($page - 1) * $par_page;

// Construire la requête SQL
$sql = "SELECT uq.*, c.nom as categorie_nom, c.couleur as categorie_couleur, c.icone as categorie_icone, 
        u.nom as utilisateur_nom, u.est_contributeur, d.nom as difficulte_nom
        FROM user_quizzes uq 
        LEFT JOIN categories c ON uq.categorie_id = c.id 
        LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id 
        LEFT JOIN difficultes d ON uq.difficulte_id = d.id
        WHERE 1=1";

$count_sql = "SELECT COUNT(*) as total FROM user_quizzes uq 
              LEFT JOIN categories c ON uq.categorie_id = c.id 
              LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id 
              WHERE 1=1";

$params = [];

if ($status !== 'all') {
    $sql .= " AND uq.status = :status";
    $count_sql .= " AND uq.status = :status";
    $params[':status'] = $status;
}

if ($categorie_id > 0) {
    $sql .= " AND uq.categorie_id = :categorie_id";
    $count_sql .= " AND uq.categorie_id = :categorie_id";
    $params[':categorie_id'] = $categorie_id;
}

if (!empty($recherche)) {
    $sql .= " AND (uq.titre LIKE :recherche OR uq.description LIKE :recherche)";
    $count_sql .= " AND (uq.titre LIKE :recherche OR uq.description LIKE :recherche)";
    $params[':recherche'] = "%$recherche%";
}

$sql .= " ORDER BY uq.created_at DESC LIMIT :offset, :limit";

// Exécuter la requête de comptage
$stmt = $db->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_quizzes = $total_result['total'];
$total_pages = ceil($total_quizzes / $par_page);

// Exécuter la requête principale
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $par_page, PDO::PARAM_INT);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories et difficultés pour les filtres et l'édition
$categories = $db->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$difficultes = $db->query("SELECT * FROM difficultes ORDER BY id")->fetchAll();

$titre_page = "Validation des Quiz";
include '../includes/header.php';
?>

<div class="content-header">
    <h1>Validation des Quiz</h1>
    <div class="actions">
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?>">
        <?= $_SESSION['message'] ?>
        <button type="button" class="close-alert" onclick="this.parentElement.style.display='none';">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="content-body">
    <div class="card">
        <div class="card-header">
            <h2>Filtres</h2>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="filters-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>En attente</option>
                            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approuvé</option>
                            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tous</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="categorie_id">Catégorie</label>
                        <select id="categorie_id" name="categorie_id" class="form-control">
                            <option value="0">Toutes les catégories</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?= $categorie['id'] ?>" <?= $categorie_id === (int)$categorie['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="recherche">Recherche</label>
                        <input type="text" id="recherche" name="recherche" class="form-control" value="<?= htmlspecialchars($recherche) ?>" placeholder="Rechercher un quiz...">
                    </div>
                    
                    <div class="form-group">
                        <label> </label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Liste des Quiz (<?= $total_quizzes ?>)</h2>
        </div>
        <div class="card-body">
            <?php if (empty($quizzes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun quiz trouvé correspondant à vos critères.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Difficulté</th>
                                <th>Créateur</th>
                                <th>Date de création</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $quiz): ?>
                                <tr>
                                    <td><?= $quiz['id'] ?></td>
                                    <td><?= htmlspecialchars($quiz['titre']) ?></td>
                                    <td>
                                        <?php if ($quiz['categorie_id']): ?>
                                            <span class="badge" style="background-color: <?= $quiz['categorie_couleur'] ?>">
                                                <?= htmlspecialchars($quiz['categorie_nom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Non catégorisé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($quiz['difficulte_id']): ?>
                                            <span class="badge badge-<?= getDifficultyClass($quiz['difficulte_id']) ?>">
                                                <?= htmlspecialchars($quiz['difficulte_nom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($quiz['utilisateur_nom']) ?>
                                        <?php if ($quiz['est_contributeur']): ?>
                                            <i class="fas fa-check-circle certified-icon" title="Contributeur certifié"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($quiz['created_at'])) ?></td>
                                    <td>
                                        <?php if ($quiz['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">En attente</span>
                                        <?php elseif ($quiz['status'] === 'approved'): ?>
                                            <span class="badge badge-success">Approuvé</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Rejeté</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info view-quiz" data-id="<?= $quiz['id'] ?>" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary try-quiz" data-id="<?= $quiz['id'] ?>" title="Essayer le quiz">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning edit-quiz" data-id="<?= $quiz['id'] ?>" title="Éditer">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-quiz" data-id="<?= $quiz['id'] ?>" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php if ($quiz['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-success approve-quiz" data-id="<?= $quiz['id'] ?>" title="Approuver">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger reject-quiz" data-id="<?= $quiz['id'] ?>" title="Rejeter">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&categorie_id=<?= $categorie_id ?>&recherche=<?= urlencode($recherche) ?>" class="btn btn-outline">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>&status=<?= $status ?>&categorie_id=<?= $categorie_id ?>&recherche=<?= urlencode($recherche) ?>" 
                               class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&categorie_id=<?= $categorie_id ?>&recherche=<?= urlencode($recherche) ?>" class="btn btn-outline">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de visualisation du quiz -->
<div id="view-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Détails du Quiz</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="quiz-details"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline close-modal">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal d'essai du quiz -->
<div id="try-modal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="try-modal-title">Essayer le Quiz</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="quiz-container">
                <!-- Écran de démarrage -->
                <div id="quiz-start-screen">
                    <div class="quiz-start-content">
                        <h3>Prêt à essayer ce quiz?</h3>
                        <p>Vous allez pouvoir tester ce quiz avant de l'approuver.</p>
                        <div class="quiz-meta">
                            <div class="quiz-meta-item">
                                <i class="fas fa-question-circle"></i>
                                <span id="quiz-question-count">0 questions</span>
                            </div>
                            <div class="quiz-meta-item">
                                <i class="fas fa-folder"></i>
                                <span id="quiz-category">Catégorie</span>
                            </div>
                            <div class="quiz-meta-item">
                                <i class="fas fa-signal"></i>
                                <span id="quiz-difficulty">Difficulté</span>
                            </div>
                        </div>
                        <button id="start-quiz-btn" class="btn btn-primary">
                            <i class="fas fa-play"></i> Commencer le quiz
                        </button>
                    </div>
                </div>
                
                <!-- Écran du quiz -->
                <div id="quiz-content" style="display: none;">
                    <div class="quiz-progress-bar">
                        <div class="progress-bar-inner"></div>
                    </div>
                    
                    <div class="quiz-question-counter">
                        Question <span id="current-question">1</span> sur <span id="total-questions">10</span>
                    </div>
                    
                    <div class="quiz-question">
                        <h3 id="question-text"></h3>
                    </div>
                    
                    <div class="quiz-options">
                        <!-- Les options seront générées dynamiquement -->
                    </div>
                    
                    <div class="quiz-navigation">
                        <button id="prev-question" class="btn btn-outline" disabled>
                            <i class="fas fa-chevron-left"></i> Précédent
                        </button>
                        <button id="next-question" class="btn btn-primary" disabled>
                            Suivant <i class="fas fa-chevron-right"></i>
                        </button>
                        <button id="finish-quiz" class="btn btn-success" style="display: none;">
                            Terminer <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Écran des résultats -->
                <div id="quiz-results" style="display: none;">
                    <div class="results-header">
                        <h3>Résultats du Quiz</h3>
                        <div class="results-score">
                            <div class="score-circle">
                                <div class="score-number">
                                    <span id="correct-answers">0</span>/<span id="total-questions-results">0</span>
                                </div>
                            </div>
                            <div class="score-text">
                                <div class="score-percentage"><span id="score-percentage">0</span>%</div>
                                <div class="score-label">Réponses correctes</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="results-actions">
                        <button id="show-answers" class="btn btn-outline">
                            <i class="fas fa-search"></i> Voir les réponses
                        </button>
                        <button id="retry-quiz" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Réessayer
                        </button>
                        <button id="close-quiz" class="btn btn-outline close-modal">
                            <i class="fas fa-times"></i> Fermer
                        </button>
                    </div>
                </div>
                
                <!-- Écran de révision -->
                <div id="quiz-review" style="display: none;">
                    <div class="review-header">
                        <h3>Révision des réponses</h3>
                    </div>
                    
                    <div class="review-questions">
                        <!-- Les questions et réponses seront générées dynamiquement -->
                    </div>
                    
                    <div class="review-actions">
                        <button id="back-to-results" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Retour aux résultats
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'approbation du quiz -->
<div id="approve-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Approuver le Quiz</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="approve-form" action="" method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="quiz_id" id="approve-quiz-id" value="">
                
                <div class="form-group">
                    <label for="approve-comment">Commentaire (optionnel)</label>
                    <textarea id="approve-comment" name="admin_comment" rows="4" class="form-control" placeholder="Ajouter un commentaire pour l'auteur du quiz..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmer l'approbation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet du quiz -->
<div id="reject-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Rejeter le Quiz</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="reject-form" action="" method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="quiz_id" id="reject-quiz-id" value="">
                
                <div class="form-group">
                    <label for="reject-comment">Raison du rejet <span class="required">*</span></label>
                    <textarea id="reject-comment" name="admin_comment" rows="4" class="form-control" placeholder="Expliquez pourquoi ce quiz est rejeté..." required></textarea>
                    <div class="form-hint">Cette raison sera communiquée à l'auteur du quiz.</div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition du quiz -->
<div id="edit-modal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2>Éditer le Quiz</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="edit-form" action="" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="quiz_id" id="edit-quiz-id" value="">
                
                <div class="form-group">
                    <label for="edit-titre">Titre <span class="required">*</span></label>
                    <input type="text" id="edit-titre" name="titre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description <span class="required">*</span></label>
                    <textarea id="edit-description" name="description" rows="4" class="form-control" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-categorie">Catégorie <span class="required">*</span></label>
                    <select id="edit-categorie" name="categorie_id" class="form-control" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-difficulte">Difficulté <span class="required">*</span></label>
                    <select id="edit-difficulte" name="difficulte_id" class="form-control" required>
                        <option value="">Sélectionner une difficulté</option>
                        <?php foreach ($difficultes as $difficulte): ?>
                            <option value="<?= $difficulte['id'] ?>"><?= htmlspecialchars($difficulte['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Questions <span class="required">*</span></label>
                    <div id="edit-questions-container">
                        <!-- Les questions seront générées dynamiquement -->
                    </div>
                    <button type="button" id="add-question-btn" class="btn btn-outline">
                        <i class="fas fa-plus"></i> Ajouter une question
                    </button>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmer la suppression</h2>
            <button type="button" class="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="delete-form" action="" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="quiz_id" id="delete-quiz-id" value="">
                
                <p>Êtes-vous sûr de vouloir supprimer ce quiz ? Cette action est irréversible.</p>
                
                <div class="form-group">
                    <label for="delete-comment">Commentaire (optionnel)</label>
                    <textarea id="delete-comment" name="admin_comment" rows="4" class="form-control" placeholder="Ajouter un commentaire pour l'auteur du quiz..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables pour le quiz
    let quizData = null;
    let currentQuestionIndex = 0;
    let userAnswers = {};
    
    // Fermer les modals
    const modals = document.querySelectorAll('.modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = '';
        });
    });
    
    // Fermer les modals en cliquant en dehors
    window.addEventListener('click', function(e) {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });
    
    // Ouvrir la modal de visualisation
    const viewBtns = document.querySelectorAll('.view-quiz');
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            fetchQuizDetails(quizId);
        });
    });
    
    // Ouvrir la modal d'essai du quiz
    const tryBtns = document.querySelectorAll('.try-quiz');
    tryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            fetchQuizForTry(quizId);
        });
    });
    
    // Ouvrir la modal d'approbation
    const approveBtns = document.querySelectorAll('.approve-quiz');
    approveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            document.getElementById('approve-quiz-id').value = quizId;
            document.getElementById('approve-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Ouvrir la modal de rejet
    const rejectBtns = document.querySelectorAll('.reject-quiz');
    rejectBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            document.getElementById('reject-quiz-id').value = quizId;
            document.getElementById('reject-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Ouvrir la modal d'édition
    const editBtns = document.querySelectorAll('.edit-quiz');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            fetchQuizForEdit(quizId);
        });
    });
    
    // Ouvrir la modal de suppression
    const deleteBtns = document.querySelectorAll('.delete-quiz');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-id');
            document.getElementById('delete-quiz-id').value = quizId;
            document.getElementById('delete-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Fonction pour récupérer les détails d'un quiz
    function fetchQuizDetails(quizId) {
        document.getElementById('quiz-details').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Chargement des détails...</div>';
        document.getElementById('view-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        const apiUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))}/get_quiz_details.php?id=${quizId}`;
        
        fetch(apiUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayQuizDetails(data.quiz);
            } else {
                document.getElementById('quiz-details').innerHTML = `
                    <div class="alert alert-error">
                        ${data.message || 'Une erreur est survenue lors de la récupération des détails du quiz.'}
                    </div>`;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('quiz-details').innerHTML = `
                <div class="alert alert-error">
                    Une erreur est survenue lors de la récupération des détails du quiz: ${error.message}
                </div>`;
        });
    }
    
    // Fonction pour récupérer un quiz pour l'édition
    function fetchQuizForEdit(quizId) {
        document.getElementById('edit-questions-container').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Chargement du quiz...</div>';
        document.getElementById('edit-quiz-id').value = quizId;
        document.getElementById('edit-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        const apiUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))}/get_quiz_details.php?id=${quizId}`;
        
        fetch(apiUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateEditForm(data.quiz);
            } else {
                document.getElementById('edit-questions-container').innerHTML = `
                    <div class="alert alert-error">
                        ${data.message || 'Une erreur est survenue lors de la récupération du quiz.'}
                    </div>`;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('edit-questions-container').innerHTML = `
                <div class="alert alert-error">
                    Une erreur est survenue lors de la récupération du quiz: ${error.message}
                </div>`;
        });
    }
    
    // Fonction pour peupler le formulaire d'édition
    function populateEditForm(quiz) {
        document.getElementById('edit-titre').value = quiz.titre;
        document.getElementById('edit-description').value = quiz.description;
        document.getElementById('edit-categorie').value = quiz.categorie_id || '';
        document.getElementById('edit-difficulte').value = quiz.difficulte_id || '';
        
        const questionsContainer = document.getElementById('edit-questions-container');
        questionsContainer.innerHTML = '';
        
        quiz.questions.forEach((question, qIndex) => {
            addQuestionField(question, qIndex);
        });
    }
    
    // Fonction pour ajouter un champ de question
    function addQuestionField(question = null, qIndex = null) {
        const questionsContainer = document.getElementById('edit-questions-container');
        const questionCount = questionsContainer.querySelectorAll('.question-field').length;
        const questionId = `question-${questionCount}`;
        
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-field';
        questionDiv.dataset.index = questionCount;
        
        let questionText = question ? question.question : '';
        let options = question ? question.options : [];
        
        questionDiv.innerHTML = `
            <div class="question-header">
                <label>Question ${questionCount + 1}</label>
                <button type="button" class="btn btn-sm btn-danger remove-question">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="form-group">
                <input type="text" class="form-control question-text" value="${questionText}" placeholder="Texte de la question" required>
            </div>
            <div class="options-container">
                ${options.map((option, oIndex) => `
                    <div class="option-field">
                        <div class="form-group option-row">
                            <input type="text" class="form-control option-text" value="${option.texte || ''}" placeholder="Texte de l'option" required>
                            <label class="option-correct">
                                <input type="radio" name="correct-${questionId}" ${option.est_correcte ? 'checked' : ''} required>
                                Correcte
                            </label>
                            <button type="button" class="btn btn-sm btn-danger remove-option">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
            <button type="button" class="btn btn-outline add-option">
                <i class="fas fa-plus"></i> Ajouter une option
            </button>
        `;
        
        questionsContainer.appendChild(questionDiv);
        
        // Ajouter des écouteurs pour les boutons
        questionDiv.querySelector('.add-option').addEventListener('click', function() {
            addOptionField(questionDiv.querySelector('.options-container'), questionId);
        });
        
        questionDiv.querySelector('.remove-question').addEventListener('click', function() {
            questionDiv.remove();
            updateQuestionNumbers();
        });
        
        questionDiv.querySelectorAll('.remove-option').forEach(btn => {
            btn.addEventListener('click', function() {
                const optionField = btn.closest('.option-field');
                if (questionDiv.querySelectorAll('.option-field').length > 2) {
                    optionField.remove();
                } else {
                    alert('Chaque question doit avoir au moins deux options.');
                }
            });
        });
    }
    
    // Fonction pour ajouter un champ d'option
    function addOptionField(optionsContainer, questionId) {
        const optionCount = optionsContainer.querySelectorAll('.option-field').length;
        if (optionCount >= 4) {
            alert('Maximum 4 options par question.');
            return;
        }
        
        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-field';
        optionDiv.innerHTML = `
            <div class="form-group option-row">
                <input type="text" class="form-control option-text" placeholder="Texte de l'option" required>
                <label class="option-correct">
                    <input type="radio" name="correct-${questionId}" required>
                    Correcte
                </label>
                <button type="button" class="btn btn-sm btn-danger remove-option">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        optionsContainer.appendChild(optionDiv);
        
        optionDiv.querySelector('.remove-option').addEventListener('click', function() {
            if (optionsContainer.querySelectorAll('.option-field').length > 2) {
                optionDiv.remove();
            } else {
                alert('Chaque question doit avoir au moins deux options.');
            }
        });
    }
    
    // Fonction pour mettre à jour les numéros des questions
    function updateQuestionNumbers() {
        const questions = document.querySelectorAll('.question-field');
        questions.forEach((q, index) => {
            q.dataset.index = index;
            q.querySelector('.question-header label').textContent = `Question ${index + 1}`;
            const radios = q.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.name = `correct-question-${index}`;
            });
        });
    }
    
    // Ajouter une nouvelle question
    document.getElementById('add-question-btn').addEventListener('click', function() {
        addQuestionField();
    });
    
    // Valider et soumettre le formulaire d'édition
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const questions = [];
        const questionFields = document.querySelectorAll('.question-field');
        
        for (const q of questionFields) {
            const questionText = q.querySelector('.question-text').value.trim();
            if (!questionText) {
                alert('Toutes les questions doivent avoir un texte.');
                return;
            }
            
            const options = [];
            const optionFields = q.querySelectorAll('.option-field');
            let hasCorrect = false;
            
            for (const o of optionFields) {
                const optionText = o.querySelector('.option-text').value.trim();
                if (!optionText) {
                    alert('Toutes les options doivent avoir un texte.');
                    return;
                }
                
                const isCorrect = o.querySelector('input[type="radio"]').checked;
                if (isCorrect) {
                    hasCorrect = true;
                }
                
                options.push({
                    texte: optionText,
                    est_correcte: isCorrect ? 1 : 0
                });
            }
            
            if (!hasCorrect) {
                alert('Chaque question doit avoir une réponse correcte.');
                return;
            }
            
            if (options.length < 2) {
                alert('Chaque question doit avoir au moins deux options.');
                return;
            }
            
            questions.push({
                texte: questionText,
                options: options
            });
        }
        
        if (questions.length === 0) {
            alert('Le quiz doit contenir au moins une question.');
            return;
        }
        
        // Ajouter les questions comme champ caché
        const questionsInput = document.createElement('input');
        questionsInput.type = 'hidden';
        questionsInput.name = 'questions';
        questionsInput.value = JSON.stringify(questions);
        this.appendChild(questionsInput);
        
        this.submit();
    });
    
    // Fonction pour récupérer un quiz pour l'essai
    function fetchQuizForTry(quizId) {
        currentQuestionIndex = 0;
        userAnswers = {};
        
        document.getElementById('quiz-start-screen').style.display = 'block';
        document.getElementById('quiz-content').style.display = 'none';
        document.getElementById('quiz-results').style.display = 'none';
        document.getElementById('quiz-review').style.display = 'none';
        
        document.getElementById('try-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        const apiUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))}/get_quiz_details.php?id=${quizId}`;
        
        fetch(apiUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                quizData = data.quiz;
                
                if (!quizData.questions || quizData.questions.length === 0) {
                    alert('Ce quiz ne contient aucune question.');
                    return;
                }
                
                document.getElementById('try-modal-title').textContent = `Essayer le Quiz: ${quizData.titre}`;
                document.getElementById('quiz-question-count').textContent = `${quizData.questions.length} questions`;
                document.getElementById('quiz-category').textContent = quizData.categorie_nom || 'Non catégorisé';
                document.getElementById('quiz-difficulty').textContent = quizData.difficulte_nom || 'Non défini';
                
                document.getElementById('total-questions').textContent = quizData.questions.length;
                document.getElementById('total-questions-results').textContent = quizData.questions.length;
            } else {
                alert('Erreur lors du chargement du quiz: ' + (data.message || 'Une erreur inconnue est survenue.'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert(`Une erreur est survenue lors du chargement du quiz: ${error.message}`);
        });
    }
    
    // Fonction pour afficher les détails d'un quiz
    function displayQuizDetails(quiz) {
        document.getElementById('modal-title').textContent = `Détails du Quiz: ${quiz.titre}`;
        
        let html = `
            <div class="quiz-details">
                <div class="quiz-details-header">
                    <h3 class="quiz-details-title">${quiz.titre}</h3>
                    <p class="quiz-details-description">${quiz.description}</p>
                    
                    <div class="quiz-details-meta">
                        <div class="quiz-details-meta-item">
                            <i class="fas fa-folder"></i>
                            <span>Catégorie: ${quiz.categorie_nom || 'Non catégorisé'}</span>
                        </div>
                        
                        <div class="quiz-details-meta-item">
                            <i class="fas fa-signal"></i>
                            <span>Difficulté: ${quiz.difficulte_nom || 'Non définie'}</span>
                        </div>
                        
                        <div class="quiz-details-meta-item">
                            <i class="fas fa-user"></i>
                            <span>Créateur: ${quiz.utilisateur_nom} ${quiz.est_contributeur ? '<i class="fas fa-check-circle certified-icon" title="Contributeur certifié"></i>' : ''}</span>
                        </div>
                        
                        <div class="quiz-details-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Date de création: ${new Date(quiz.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        
                        <div class="quiz-details-meta-item">
                            <i class="fas fa-question-circle"></i>
                            <span>Nombre de questions: ${quiz.questions.length}</span>
                        </div>
                    </div>
                </div>
        `;
        
        html += `
            <div class="quiz-details-status">
                <div class="quiz-details-status-item">
                    <strong>Statut:</strong>
                    <span class="badge badge-${quiz.status === 'pending' ? 'warning' : (quiz.status === 'approved' ? 'success' : 'danger')}">
                        ${quiz.status === 'pending' ? 'En attente' : (quiz.status === 'approved' ? 'Approuvé' : 'Rejeté')}
                    </span>
                </div>
        `;
        
        if (quiz.admin_comment) {
            html += `
                <div class="quiz-details-status-item">
                    <strong>Commentaire de l'administrateur:</strong>
                    <p>${quiz.admin_comment}</p>
                </div>
            `;
        }
        
        html += `</div>`;
        
        html += `
            <div class="quiz-details-questions">
                <h4>Questions et réponses</h4>
        `;
        
        if (quiz.questions.length > 0) {
            quiz.questions.forEach((question, index) => {
                html += `
                    <div class="quiz-details-question">
                        <div class="quiz-details-question-header">
                            <div class="quiz-details-question-number">Question ${index + 1}</div>
                        </div>
                        <div class="quiz-details-question-text">${question.question}</div>
                        
                        <div class="quiz-details-options">
                `;
                
                const letters = ['A', 'B', 'C', 'D'];
                question.options.forEach((option, optIndex) => {
                    html += `
                        <div class="quiz-details-option ${option.est_correcte == 1 ? 'correct' : ''}">
                            <div class="quiz-details-option-letter">${letters[optIndex]}</div>
                            <div class="quiz-details-option-text">${option.texte}</div>
                            ${option.est_correcte == 1 ? '<div class="quiz-details-option-correct"><i class="fas fa-check"></i> Réponse correcte</div>' : ''}
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            });
        } else {
            html += `<div class="alert alert-info">Ce quiz ne contient aucune question.</div>`;
        }
        
        html += `
                </div>
            </div>
        `;
        
        document.getElementById('quiz-details').innerHTML = html;
    }
    
    // Événements pour l'essai du quiz
    document.getElementById('start-quiz-btn').addEventListener('click', startQuiz);
    document.getElementById('prev-question').addEventListener('click', goToPreviousQuestion);
    document.getElementById('next-question').addEventListener('click', goToNextQuestion);
    document.getElementById('finish-quiz').addEventListener('click', finishQuiz);
    document.getElementById('show-answers').addEventListener('click', showAnswers);
    document.getElementById('back-to-results').addEventListener('click', backToResults);
    document.getElementById('retry-quiz').addEventListener('click', retryQuiz);
    
    // Fonction pour démarrer le quiz
    function startQuiz() {
        document.getElementById('quiz-start-screen').style.display = 'none';
        document.getElementById('quiz-content').style.display = 'block';
        
        displayQuestion(0);
    }
    
    // Fonction pour afficher une question
    function displayQuestion(index) {
        const question = quizData.questions[index];
        
        document.getElementById('question-text').textContent = question.question;
        
        document.getElementById('current-question').textContent = index + 1;
        
        const progress = ((index + 1) / quizData.questions.length) * 100;
        document.querySelector('.progress-bar-inner').style.width = `${progress}%`;
        
        const optionsContainer = document.querySelector('.quiz-options');
        optionsContainer.innerHTML = '';
        
        const letters = ['A', 'B', 'C', 'D'];
        question.options.forEach((option, optionIndex) => {
            const optionElement = document.createElement('div');
            optionElement.className = 'quiz-option';
            
            if (userAnswers[question.id] === option.id) {
                optionElement.classList.add('selected');
            }
            
            optionElement.innerHTML = `
                <div class="option-letter">${letters[optionIndex]}</div>
                <div class="option-text">${option.texte}</div>
            `;
            
            optionElement.addEventListener('click', () => selectOption(question.id, option.id, optionElement));
            optionsContainer.appendChild(optionElement);
        });
        
        document.getElementById('prev-question').disabled = index === 0;
        document.getElementById('next-question').disabled = !userAnswers[question.id];
        
        if (index === quizData.questions.length - 1) {
            document.getElementById('next-question').style.display = 'none';
            document.getElementById('finish-quiz').style.display = 'inline-flex';
            document.getElementById('finish-quiz').disabled = !userAnswers[question.id];
        } else {
            document.getElementById('next-question').style.display = 'inline-flex';
            document.getElementById('finish-quiz').style.display = 'none';
        }
        
        currentQuestionIndex = index;
    }
    
    // Fonction pour sélectionner une option
    function selectOption(questionId, optionId, optionElement) {
        const options = document.querySelectorAll('.quiz-option');
        options.forEach(option => option.classList.remove('selected'));
        
        optionElement.classList.add('selected');
        
        userAnswers[questionId] = optionId;
        
        document.getElementById('next-question').disabled = false;
        
        if (currentQuestionIndex === quizData.questions.length - 1) {
            document.getElementById('finish-quiz').disabled = false;
        }
    }
    
    // Fonction pour aller à la question précédente
    function goToPreviousQuestion() {
        if (currentQuestionIndex > 0) {
            displayQuestion(currentQuestionIndex - 1);
        }
    }
    
    // Fonction pour aller à la question suivante
    function goToNextQuestion() {
        if (currentQuestionIndex < quizData.questions.length - 1) {
            displayQuestion(currentQuestionIndex + 1);
        }
    }
    
    // Fonction pour terminer le quiz
    function finishQuiz() {
        let correctAnswers = 0;
        
        quizData.questions.forEach(question => {
            const userAnswer = userAnswers[question.id];
            
            if (userAnswer) {
                const correctOption = question.options.find(option => option.est_correcte == 1);
                
                if (correctOption && userAnswer === correctOption.id) {
                    correctAnswers++;
                }
            }
        });
        
        document.getElementById('correct-answers').textContent = correctAnswers;
        const percentage = Math.round((correctAnswers / quizData.questions.length) * 100);
        document.getElementById('score-percentage').textContent = percentage;
        
        document.getElementById('quiz-content').style.display = 'none';
        document.getElementById('quiz-results').style.display = 'block';
    }
    
    // Fonction pour afficher les réponses
    function showAnswers() {
        document.getElementById('quiz-results').style.display = 'none';
        document.getElementById('quiz-review').style.display = 'block';
        
        const reviewQuestionsContainer = document.querySelector('.review-questions');
        reviewQuestionsContainer.innerHTML = '';
        
        quizData.questions.forEach((question, index) => {
            const userAnswer = userAnswers[question.id];
            const correctOption = question.options.find(option => option.est_correcte == 1);
            const isCorrect = userAnswer && correctOption && userAnswer === correctOption.id;
            
            const questionElement = document.createElement('div');
            questionElement.className = `review-question ${isCorrect ? 'correct' : 'incorrect'}`;
            
            let optionsHTML = '';
            const letters = ['A', 'B', 'C', 'D'];
            
            question.options.forEach((option, optionIndex) => {
                const isUserAnswer = userAnswer === option.id;
                const isCorrectAnswer = option.est_correcte == 1;
                
                let optionClass = '';
                if (isUserAnswer && isCorrectAnswer) {
                    optionClass = 'correct-answer';
                } else if (isUserAnswer && !isCorrectAnswer) {
                    optionClass = 'incorrect-answer';
                } else if (!isUserAnswer && isCorrectAnswer) {
                    optionClass = 'missed-answer';
                }
                
                optionsHTML += `
                    <div class="review-option ${optionClass}">
                        <div class="option-letter">${letters[optionIndex]}</div>
                        <div class="option-text">${option.texte}</div>
                        ${isUserAnswer ? '<div class="user-answer-icon"><i class="fas fa-user"></i></div>' : ''}
                        ${isCorrectAnswer ? '<div class="correct-answer-icon"><i class="fas fa-check"></i></div>' : ''}
                    </div>
                `;
            });
            
            questionElement.innerHTML = `
                <div class="review-question-header">
                    <div class="question-number">Question ${index + 1}</div>
                    <div class="question-result ${isCorrect ? 'correct' : 'incorrect'}">
                        ${isCorrect ? '<i class="fas fa-check"></i> Correct' : '<i class="fas fa-times"></i> Incorrect'}
                    </div>
                </div>
                <div class="review-question-text">${question.question}</div>
                <div class="review-options">
                    ${optionsHTML}
                </div>
            `;
            
            reviewQuestionsContainer.appendChild(questionElement);
        });
    }
    
    // Fonction pour retourner aux résultats
    function backToResults() {
        document.getElementById('quiz-review').style.display = 'none';
        document.getElementById('quiz-results').style.display = 'block';
    }
    
    // Fonction pour réessayer le quiz
    function retryQuiz() {
        currentQuestionIndex = 0;
        userAnswers = {};
        
        document.getElementById('quiz-results').style.display = 'none';
        document.getElementById('quiz-review').style.display = 'none';
        document.getElementById('quiz-content').style.display = 'block';
        
        displayQuestion(0);
    }
    
    // Validation du formulaire de rejet
    document.getElementById('reject-form').addEventListener('submit', function(e) {
        const comment = document.getElementById('reject-comment').value.trim();
        if (!comment) {
            e.preventDefault();
            alert('Veuillez fournir une raison pour le rejet du quiz.');
        }
    });
});

// Fonction pour obtenir la classe CSS en fonction du niveau de difficulté
function getDifficultyClass(difficultyId) {
    switch (parseInt(difficultyId)) {
        case 1:
            return 'success'; // Facile
        case 2:
            return 'warning'; // Moyen
        case 3:
            return 'danger';  // Difficile
        default:
            return 'secondary';
    }
}
</script>

<style>
/* Styles pour les modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    overflow-y: auto;
}

.modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    max-width: 800px;
    width: 90%;
    margin: 30px auto;
    animation: modalFadeIn 0.3s ease;
}

.modal-content.modal-lg {
    max-width: 1000px;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.close-modal {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.close-modal:hover {
    color: #1f2937;
    background-color: #f3f4f6;
}

.close-modal i {
    font-size: 1.25rem;
}

.modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Styles pour l'édition des questions */
.question-field {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.question-header label {
    font-weight: 600;
}

.options-container {
    margin-bottom: 10px;
}

.option-field {
    margin-bottom: 10px;
}

.option-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.option-correct {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.remove-question, .remove-option {
    margin-left: auto;
}

/* Styles pour les détails du quiz */
.quiz-details {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.quiz-details-header {
    margin-bottom: 20px;
}

.quiz-details-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 10px 0;
}

.quiz-details-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 15px;
}

.quiz-details-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.quiz-details-meta-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.quiz-details-meta-item i {
    margin-right: 5px;
    color: #4f46e5;
}

.quiz-details-status {
    background-color: #f9fafb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.quiz-details-status-item {
    margin-bottom: 10px;
}

.quiz-details-status-item:last-child {
    margin-bottom: 0;
}

.quiz-details-status-item strong {
    display: block;
    margin-bottom: 5px;
}

.quiz-details-questions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.quiz-details-questions h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 15px 0;
}

.quiz-details-question {
    background-color: #f9fafb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e5e7eb;
}

.quiz-details-question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.quiz-details-question-number {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
}

.quiz-details-question-text {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 15px;
}

.quiz-details-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quiz-details-option {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background-color: #fff;
    position: relative;
}

.quiz-details-option.correct {
    border-color: #10b981;
    background-color: rgba(16, 185, 129, 0.1);
}

.quiz-details-option-letter {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
    border-radius: 50%;
    font-size: 0.875rem;
    font-weight: 600;
    margin-right: 10px;
}

.quiz-details-option.correct .quiz-details-option-letter {
    background-color: #10b981;
    color: white;
}

.quiz-details-option-text {
    flex: 1;
}

.quiz-details-option-correct {
    color: #10b981;
    font-weight: 500;
    font-size: 0.875rem;
    margin-left: 10px;
}

.quiz-details-option-correct i {
    margin-right: 5px;
}

/* Styles pour l'essai du quiz */
#quiz-container {
    width: 100%;
}

#quiz-start-screen {
    text-align: center;
    padding: 2rem 0;
}

.quiz-start-content {
    max-width: 600px;
    margin: 0 auto;
}

.quiz-start-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.quiz-start-content p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.quiz-meta {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.quiz-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.quiz-meta-item i {
    color: #4f46e5;
}

.quiz-progress-bar {
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.progress-bar-inner {
    height: 100%;
    background-color: #4f46e5;
    width: 0;
    transition: width 0.3s ease;
}

.quiz-question-counter {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.quiz-question {
    margin-bottom: 1.5rem;
}

.quiz-question h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.quiz-option {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quiz-option:hover {
    border-color: #4f46e5;
    background-color: rgba(79, 70, 229, 0.05);
}

.quiz-option.selected {
    border-color: #4f46e5;
    background-color: rgba(79, 70, 229, 0.1);
}

.option-letter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #e5e7eb;
    color: #4b5563;
    border-radius: 50%;
    font-weight: 600;
    margin-right: 0.75rem;
    transition: all 0.2s ease;
}

.quiz-option.selected .option-letter {
    background-color: #4f46e5;
    color: white;
}

.option-text {
    flex: 1;
}

.quiz-navigation {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
}

/* Styles pour les résultats du quiz */
.results-header {
    text-align: center;
    margin-bottom: 2rem;
}

.results-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.results-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.score-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 8px solid #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.score-number {
    font-size: 2rem;
    font-weight: 700;
}

.score-text {
    text-align: center;
}

.score-percentage {
    font-size: 1.25rem;
    font-weight: 700;
    color: #4f46e5;
}

.score-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.results-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 2rem;
}

/* Styles pour la révision des réponses */
.review-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.review-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
}

.review-questions {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.review-question {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.review-question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.question-number {
    font-weight: 600;
}

.question-result {
    font-size: 0.875rem;
    font-weight: 500;
}

.question-result.correct {
    color: #10b981;
}

.question-result.incorrect {
    color: #ef4444;
}

.review-question-text {
    padding: 0.75rem;
    font-size: 1rem;
    font-weight: 500;
    border-bottom: 1px solid #e5e7eb;
}

.review-options {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.review-option {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    position: relative;
}

.review-option.correct-answer {
    border-color: #10b981;
    background-color: rgba(16, 185, 129, 0.1);
}

.review-option.incorrect-answer {
    border-color: #ef4444;
    background-color: rgba(239, 68, 68, 0.1);
}

.review-option.missed-answer {
    border-color: #10b981;
    border-style: dashed;
}

.user-answer-icon, .correct-answer-icon {
    position: absolute;
    right: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.user-answer-icon {
    right: 3rem;
    background-color: #6b7280;
    color: white;
}

.correct-answer-icon {
    background-color: #10b981;
    color: white;
}

.review-actions {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

/* Styles pour les formulaires */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-group .required {
    color: #ef4444;
}

.form-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 5px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

/* Styles pour les badges */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.badge-success {
    background-color: #10b981;
}

.badge-warning {
    background-color: #f59e0b;
}

.badge-danger {
    background-color: #ef4444;
}

.badge-secondary {
    background-color: #6b7280;
}

/* Styles pour les boutons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
}

.btn i {
    margin-right: 5px;
}

.btn i:last-child:not(:only-child) {
    margin-right: 0;
    margin-left: 5px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 0.75rem;
}

.btn-primary {
    background-color: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background-color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-primary:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline:hover {
    background-color: rgba(79, 70, 229, 0.1);
    transform: translateY(-2px);
}

.btn-outline:disabled {
    border-color: #9ca3af;
    color: #9ca3af;
    cursor: not-allowed;
    transform: none;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-info {
    background-color: #3b82f6;
    color: white;
}

.btn-info:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-warning {
    background-color: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.btn-group {
    display: flex;
    gap: 5px;
}

/* Styles pour les alertes */
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    position: relative;
}

.alert i {
    margin-right: 10px;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.close-alert {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    color: currentColor;
    opacity: 0.7;
}

.close-alert:hover {
    opacity: 1;
}

/* Styles pour le chargement */
.loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    color: #6b7280;
}

.loading i {
    margin-right: 10px;
    font-size: 1.5rem;
}

/* Style pour l'icône de contributeur certifié */
.certified-icon {
    color: #1DA1F2; /* Couleur bleue similaire à Twitter */
    margin-left: 5px;
    font-size: 0.85em;
    vertical-align: middle;
    animation: certifiedPulse 2s infinite;
}

@keyframes certifiedPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Styles responsifs */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .quiz-details-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
    
    .quiz-navigation {
        flex-direction: column;
    }
    
    .quiz-navigation .btn {
        width: 100%;
    }
    
    .results-actions {
        flex-direction: column;
    }
    
    .results-actions .btn {
        width: 100%;
    }
    
    .option-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .option-correct {
        margin-top: 10px;
    }
}

@media (max-width: 576px) {
    .btn-group {
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        width: calc(50% - 5px);
    }
}
</style>

<?php include '../includes/footer.php'; ?>