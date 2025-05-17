<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    header('Location: ../connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Récupérer les catégories
$database = new Database();
$db = $database->connect();

$query = "SELECT * FROM categories WHERE active = 1 ORDER BY nom";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les difficultés
$query = "SELECT * FROM difficultes ORDER BY niveau";
$stmt = $db->prepare($query);
$stmt->execute();
$difficultes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les utilisateurs pour le défi
$query = "SELECT id, nom FROM utilisateurs 
          WHERE id != :user_id AND active = 1 
          ORDER BY nom";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opponent_id = isset($_POST['opponent_id']) ? (int)$_POST['opponent_id'] : 0;
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $categorie_id = isset($_POST['categorie_id']) && !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null;
    $difficulte_id = isset($_POST['difficulte_id']) && !empty($_POST['difficulte_id']) ? (int)$_POST['difficulte_id'] : null;
    $time_limit = isset($_POST['time_limit']) ? (int)$_POST['time_limit'] : 0;
    $question_count = isset($_POST['question_count']) ? (int)$_POST['question_count'] : 10;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation
    if ($opponent_id <= 0) {
        $errors[] = "Veuillez sélectionner un adversaire.";
    }
    
    if (!in_array($type, ['timed', 'accuracy', 'mixed'])) {
        $errors[] = "Type de duel invalide.";
    }
    
    if ($question_count < 5 || $question_count > 30) {
        $errors[] = "Le nombre de questions doit être entre 5 et 30.";
    }
    
    if (empty($errors)) {
        $result = createDuelChallenge(
            $user_id,
            $opponent_id,
            $type,
            $categorie_id,
            $difficulte_id,
            $time_limit,
            $question_count,
            $message
        );
        
        if ($result) {
            // Récupérer l'ID de l'invitation créée
            $query = "SELECT id FROM duel_invitations 
                      WHERE sender_id = :sender_id AND recipient_id = :recipient_id 
                      ORDER BY created_at DESC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':sender_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':recipient_id', $opponent_id, PDO::PARAM_INT);
            $stmt->execute();
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            $invitation_id = $invitation['id'] ?? null;

            if ($invitation_id) {
                // Créer une notification pour l'adversaire
                try {
                    $query = "INSERT INTO notifications (user_id, type, message, related_id, is_read, created_at)
                              VALUES (:user_id, :type, :message, :related_id, 0, NOW())";
                    $stmt = $db->prepare($query);
                    $notification_message = "Vous avez reçu une invitation à un duel de " . htmlspecialchars($_SESSION['utilisateur_nom']) . " !";
                    $stmt->bindParam(':user_id', $opponent_id, PDO::PARAM_INT);
                    $stmt->bindValue(':type', 'duel_invitation');
                    $stmt->bindParam(':message', $notification_message);
                    $stmt->bindParam(':related_id', $invitation_id, PDO::PARAM_INT);
                    $stmt->execute();
                    error_log("Notification créée pour invitation_id=$invitation_id, user_id=$opponent_id");
                } catch (PDOException $e) {
                    error_log("Erreur lors de la création de la notification pour invitation_id=$invitation_id: " . $e->getMessage());
                    // Ne pas bloquer le processus, mais signaler l'erreur
                    $errors[] = "Défi envoyé, mais la notification n'a pas pu être créée.";
                }
            } else {
                error_log("Aucune invitation trouvée pour sender_id=$user_id, recipient_id=$opponent_id");
            }

            $success = true;
            $_SESSION['message'] = "Défi envoyé avec succès !";
            $_SESSION['message_type'] = "success";
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Une erreur est survenue lors de l'envoi du défi. Veuillez réessayer.";
        }
    }
}

// Inclure l'en-tête
$titre_page = "Lancer un défi";
include '../includes/header.php';
?>

<main class="challenge-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Lancer un défi</h1>
            <p class="section-description">Défiez un autre joueur dans un duel de quiz</p>
            <div class="header-actions">
                <a href="index.php" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Retour aux duels
                </a>
            </div>
        </div>

        <div class="challenge-form-container">
            <div class="card">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="error-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="challenge-form">
                    <div class="form-section">
                        <h2 class="form-section-title">Adversaire</h2>
                        <div class="form-group">
                            <label for="opponent_id">Choisir un adversaire</label>
                            <select id="opponent_id" name="opponent_id" class="form-control" required>
                                <option value="">Sélectionner un joueur</option>
                                <?php foreach ($utilisateurs as $utilisateur): ?>
                                    <option value="<?= $utilisateur['id'] ?>" <?= isset($_POST['opponent_id']) && $_POST['opponent_id'] == $utilisateur['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($utilisateur['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="form-section-title">Type de duel</h2>
                        <div class="duel-types">
                            <div class="duel-type-option">
                                <input type="radio" id="type-timed" name="type" value="timed" <?= (!isset($_POST['type']) || $_POST['type'] == 'timed') ? 'checked' : '' ?>>
                                <label for="type-timed" class="duel-type-label">
                                    <div class="duel-type-icon">⏱️</div>
                                    <div class="duel-type-info">
                                        <h3>Contre la montre</h3>
                                        <p>Le plus rapide à terminer avec au moins 50% de bonnes réponses gagne</p>
                                    </div>
                                </label>
                            </div>
                            <div class="duel-type-option">
                                <input type="radio" id="type-accuracy" name="type" value="accuracy" <?= (isset($_POST['type']) && $_POST['type'] == 'accuracy') ? 'checked' : '' ?>>
                                <label for="type-accuracy" class="duel-type-label">
                                    <div class="duel-type-icon">🎯</div>
                                    <div class="duel-type-info">
                                        <h3>Précision</h3>
                                        <p>Celui qui a le plus de bonnes réponses gagne, le temps départage en cas d'égalité</p>
                                    </div>
                                </label>
                            </div>
                            <div class="duel-type-option">
                                <input type="radio" id="type-mixed" name="type" value="mixed" <?= (isset($_POST['type']) && $_POST['type'] == 'mixed') ? 'checked' : '' ?>>
                                <label for="type-mixed" class="duel-type-label">
                                    <div class="duel-type-icon">🔄</div>
                                    <div class="duel-type-info">
                                        <h3>Mixte</h3>
                                        <p>Combinaison de précision et de vitesse pour déterminer le vainqueur</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="form-section-title">Paramètres du quiz</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categorie_id">Catégorie (optionnel)</label>
                                <select id="categorie_id" name="categorie_id" class="form-control">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $categorie): ?>
                                        <option value="<?= $categorie['id'] ?>" <?= isset($_POST['categorie_id']) && $_POST['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categorie['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="difficulte_id">Difficulté (optionnel)</label>
                                <select id="difficulte_id" name="difficulte_id" class="form-control">
                                    <option value="">Toutes les difficultés</option>
                                    <?php foreach ($difficultes as $difficulte): ?>
                                        <option value="<?= $difficulte['id'] ?>" <?= isset($_POST['difficulte_id']) && $_POST['difficulte_id'] == $difficulte['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($difficulte['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="question_count">Nombre de questions</label>
                                <input type="number" id="question_count" name="question_count" class="form-control" min="5" max="30" value="<?= isset($_POST['question_count']) ? $_POST['question_count'] : 10 ?>" required>
                                <small class="form-text">Entre 5 et 30 questions</small>
                            </div>
                            <div class="form-group">
                                <label for="time_limit">Limite de temps (secondes, 0 = pas de limite)</label>
                                <input type="number" id="time_limit" name="time_limit" class="form-control" min="0" max="600" value="<?= isset($_POST['time_limit']) ? $_POST['time_limit'] : 0 ?>">
                                <small class="form-text">Temps maximum pour terminer le quiz (0 = pas de limite)</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="form-section-title">Message (optionnel)</h2>
                        <div class="form-group">
                            <textarea id="message" name="message" class="form-control" rows="3" placeholder="Envoyez un message à votre adversaire..."><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                                <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z" />
                            </svg>
                            Envoyer le défi
                        </button>
                        <a href="index.php" class="btn btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
    --danger-color: #ef4444;
    --text-color: #1f2937;
    --text-muted: #6b7280;
    --background-color: #f9fafb;
    --card-background: #ffffff;
    --border-color: #e5e7eb;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
    --font-sans: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Base Styles */
body {
    font-family: var(--font-sans);
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.challenge-page {
    padding: 2rem 0 4rem;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.section-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Card Styles */
.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

/* Form Styles */
.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.form-section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--text-color);
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    flex: 1;
    min-width: 250px;
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-light);
}

.form-text {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
}

/* Duel Types */
.duel-types {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.duel-type-option {
    position: relative;
}

.duel-type-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.duel-type-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.duel-type-option input[type="radio"]:checked + .duel-type-label {
    border-color: var(--primary-color);
    background-color: var(--primary-light);
}

.duel-type-option input[type="radio"]:focus + .duel-type-label {
    box-shadow: 0 0 0 2px var(--primary-light);
}

.duel-type-icon {
    font-size: 2rem;
    min-width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.duel-type-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.duel-type-info p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* Alert Styles */
.alert {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--danger-color);
}

.error-list {
    margin: 0;
    padding-left: 1.5rem;
}

.error-list li {
    margin-bottom: 0.25rem;
}

.error-list li:last-child {
    margin-bottom: 0;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: var(--transition);
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-outline {
    background-color: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.challenge-form');
    
    form.addEventListener('submit', function(e) {
        let valid = true;
        const opponent = document.getElementById('opponent_id');
        const questionCount = document.getElementById('question_count');
        
        if (!opponent.value) {
            valid = false;
            opponent.classList.add('error');
        } else {
            opponent.classList.remove('error');
        }
        
        if (questionCount.value < 5 || questionCount.value > 30) {
            valid = false;
            questionCount.classList.add('error');
        } else {
            questionCount.classList.remove('error');
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>