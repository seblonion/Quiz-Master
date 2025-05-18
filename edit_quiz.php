<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!estConnecte()) {
    $_SESSION['message'] = "Vous devez être connecté pour modifier un quiz.";
    $_SESSION['message_type'] = "error";
    rediriger('register.php');
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$database = new Database();
$db = $database->connect();

// Vérifier si quiz_id est fourni
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['message'] = "Quiz non spécifié.";
    $_SESSION['message_type'] = "error";
    rediriger('profil.php');
}

// Récupérer les détails du quiz
$query = "
    SELECT uq.id, uq.titre, uq.description, uq.categorie_id, uq.difficulte_id, uq.status
    FROM user_quizzes uq
    WHERE uq.id = :quiz_id AND uq.utilisateur_id = :utilisateur_id
";
$stmt = $db->prepare($query);
$stmt->bindParam(':quiz_id', $quiz_id);
$stmt->bindParam(':utilisateur_id', $utilisateur_id);
$stmt->execute();
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    $_SESSION['message'] = "Quiz non trouvé ou vous n'avez pas l'autorisation de le modifier.";
    $_SESSION['message_type'] = "error";
    rediriger('profil.php');
}

// Récupérer les questions et options
$query_questions = "
    SELECT q.id, q.question
    FROM user_quiz_questions q
    WHERE q.user_quiz_id = :quiz_id
    ORDER BY q.id
";
$stmt_questions = $db->prepare($query_questions);
$stmt_questions->bindParam(':quiz_id', $quiz_id);
$stmt_questions->execute();
$questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

$options = [];
foreach ($questions as &$question) {
    $query_options = "
        SELECT o.id, o.texte, o.est_correcte
        FROM user_quiz_options o
        WHERE o.user_quiz_question_id = :question_id
        ORDER BY o.id
    ";
    $stmt_options = $db->prepare($query_options);
    $stmt_options->bindParam(':question_id', $question['id']);
    $stmt_options->execute();
    $question['options'] = $stmt_options->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les catégories et difficultés
$categories = $db->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$difficultes = $db->query("SELECT id, nom FROM difficultes ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

$titre_page = "Modifier le Quiz";
?>

<main class="create-quiz-page">
    <div class="quiz-container">
        <div class="page-header">
            <h1 class="page-title">Modifier le Quiz</h1>
            <p class="page-description">Modifiez votre quiz pour le rendre encore meilleur</p>
        </div>

        <div class="quiz-form-container">
            <form id="edit-quiz-form" action="submit_edit_quiz.php" method="POST" class="quiz-form">
                <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                <div class="form-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="card-icon">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Informations générales
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="titre">Titre du Quiz <span class="required">*</span></label>
                            <input type="text" id="titre" name="titre" required maxlength="100" placeholder="Donnez un titre accrocheur à votre quiz" value="<?= htmlspecialchars($quiz['titre']) ?>">
                            <div class="form-hint">Maximum 100 caractères</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" required rows="4" placeholder="Décrivez brièvement le contenu de votre quiz"><?= htmlspecialchars($quiz['description']) ?></textarea>
                            <div class="form-hint">Une description claire aidera les utilisateurs à comprendre votre quiz</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie_id">Catégorie</label>
                            <div class="select-wrapper">
                                <select id="categorie_id" name="categorie_id">
                                    <option value="">Sélectionnez une catégorie</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $quiz['categorie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-icon">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            <div class="form-hint">Choisir une catégorie aide à classer votre quiz</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="difficulte">Niveau de difficulté <span class="required">*</span></label>
                            <div class="difficulty-selector">
                                <?php foreach ($difficultes as $difficulte): ?>
                                    <div class="difficulty-option">
                                        <input type="radio" id="difficulte-<?= $difficulte['id'] ?>" name="difficulte" value="<?= $difficulte['id'] ?>" <?= $difficulte['id'] == $quiz['difficulte_id'] ? 'checked' : '' ?> required>
                                        <label for="difficulte-<?= $difficulte['id'] ?>" class="difficulty-label <?= strtolower($difficulte['nom']) ?>">
                                            <span class="difficulty-dot"></span>
                                            <span><?= htmlspecialchars($difficulte['nom']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="card-icon">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Questions
                        </h2>
                    </div>
                    <div class="card-body">
                        <div id="questions-container">
                            <?php foreach ($questions as $q_index => $question): ?>
                                <div class="question-block" data-question-index="<?= $q_index ?>">
                                    <div class="question-header">
                                        <h3 class="question-title">Question <?= $q_index + 1 ?></h3>
                                        <button type="button" class="btn-icon remove-question" title="Supprimer cette question" <?= count($questions) <= 1 ? 'disabled' : '' ?>>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="question-<?= $q_index ?>">Texte de la question <span class="required">*</span></label>
                                        <input type="text" id="question-<?= $q_index ?>" name="questions[<?= $q_index ?>][text]" required placeholder="Saisissez votre question" value="<?= htmlspecialchars($question['question']) ?>">
                                    </div>
                                    
                                    <div class="options-container">
                                        <label class="options-label">Options <span class="required">*</span> <span class="options-hint">(Sélectionnez la réponse correcte)</span></label>
                                        
                                        <?php foreach ($question['options'] as $o_index => $option): ?>
                                            <div class="option-block">
                                                <div class="option-input">
                                                    <input type="text" name="questions[<?= $q_index ?>][options][<?= $o_index ?>][text]" placeholder="Option <?= $o_index + 1 ?>" required value="<?= htmlspecialchars($option['texte']) ?>">
                                                </div>
                                                <div class="option-correct">
                                                    <input type="radio" id="q<?= $q_index ?>-option<?= $o_index ?>" name="questions[<?= $q_index ?>][correct_option]" value="<?= $o_index ?>" <?= $option['est_correcte'] ? 'checked' : '' ?> required>
                                                    <label for="q<?= $q_index ?>-option<?= $o_index ?>" class="radio-label">Correcte</label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" class="btn btn-outline" id="add-question">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Ajouter une question
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="preview-quiz">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Prévisualiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Modal de prévisualisation -->
<div id="preview-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="preview-title">Prévisualisation du Quiz</h2>
            <button type="button" class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="preview-content"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline close-modal">Fermer</button>
        </div>
    </div>
</div>

<style>
.create-quiz-page {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
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
    --easy-color: #10b981;
    --medium-color: #f59e0b;
    --hard-color: #ef4444;

    font-family: var(--font-sans);
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
    padding: 2rem 0 4rem;
}

.create-quiz-page .quiz-container {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Page Header */
.create-quiz-page .page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.create-quiz-page .page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.create-quiz-page .page-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Form Container */
.create-quiz-page .quiz-form-container {
    max-width: 800px;
    margin: 0 auto;
}

.create-quiz-page .quiz-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Form Card */
.create-quiz-page .form-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.create-quiz-page .card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--background-color);
}

.create-quiz-page .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.create-quiz-page .card-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    color: var(--primary-color);
}

.create-quiz-page .card-body {
    padding: 1.5rem;
}

/* Form Group */
.create-quiz-page .form-group {
    margin-bottom: 1.5rem;
}

.create-quiz-page .form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.create-quiz-page .required {
    color: var(--hard-color);
}

.create-quiz-page .form-hint {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Form Inputs */
.create-quiz-page input[type="text"],
.create-quiz-page textarea,
.create-quiz-page select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: var(--transition);
}

.create-quiz-page input[type="text"]:focus,
.create-quiz-page textarea:focus,
.create-quiz-page select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.create-quiz-page .select-wrapper {
    position: relative;
}

.create-quiz-page .select-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    height: 1rem;
    color: var(--text-muted);
    pointer-events: none;
}

.create-quiz-page select {
    appearance: none;
    padding-right: 2rem;
}

/* Difficulty Selector */
.create-quiz-page .difficulty-selector {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.create-quiz-page .difficulty-option {
    flex: 1;
    min-width: 120px;
}

.create-quiz-page .difficulty-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.create-quiz-page .difficulty-label {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    cursor: pointer;
    transition: var(--transition);
}

.create-quiz-page .difficulty-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.create-quiz-page .difficulty-label.facile .difficulty-dot {
    background-color: var(--easy-color);
}

.create-quiz-page .difficulty-label.moyen .difficulty-dot {
    background-color: var(--medium-color);
}

.create-quiz-page .difficulty-label.difficile .difficulty-dot {
    background-color: var(--hard-color);
}

.create-quiz-page .difficulty-option input[type="radio"]:checked + .difficulty-label {
    border-color: var(--primary-color);
    background-color: var(--primary-light);
}

/* Question Block */
.create-quiz-page .question-block {
    background-color: var(--background-color);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
}

.create-quiz-page .question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.create-quiz-page .question-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.create-quiz-page .btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: var(--transition);
}

.create-quiz-page .btn-icon:hover:not(:disabled) {
    color: var(--hard-color);
    background-color: rgba(239, 68, 68, 0.1);
}

.create-quiz-page .btn-icon:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.create-quiz-page .btn-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

/* Options Container */
.create-quiz-page .options-container {
    margin-top: 1rem;
}

.create-quiz-page .options-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.create-quiz-page .options-hint {
    font-size: 0.75rem;
    font-weight: normal;
    color: var(--text-muted);
}

.create-quiz-page .option-block {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.create-quiz-page .option-input {
    flex: 1;
}

.create-quiz-page .option-correct {
    display: flex;
    align-items: center;
    min-width: 100px;
}

.create-quiz-page .option-correct input[type="radio"] {
    margin-right: 0.5rem;
}

.create-quiz-page .radio-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Buttons */
.create-quiz-page .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    transition: var(--transition);
    cursor: pointer;
    border: none;
}

.create-quiz-page .btn-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

.create-quiz-page .btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.create-quiz-page .btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.create-quiz-page .btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.create-quiz-page .btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.create-quiz-page #add-question {
    margin-top: 1rem;
}

/* Form Actions */
.create-quiz-page .form-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

/* Modal (Unscoped for preview consistency) */
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
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    max-width: 800px;
    width: 90%;
    margin: 2rem auto;
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
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
    color: var(--text-muted);
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: var(--transition);
}

.close-modal:hover {
    color: var(--text-color);
    background-color: var(--border-color);
}

.close-modal svg {
    width: 1.25rem;
    height: 1.25rem;
}

.modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
}

/* Preview Styles (Unscoped for exact original appearance) */
.preview-quiz {
    padding: 1rem;
}

.preview-quiz-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.preview-quiz-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.preview-quiz-info {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.preview-quiz-info-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.preview-quiz-info-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

.preview-question {
    background-color: var(--background-color);
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
}

.preview-question-number {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.preview-question-text {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.preview-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.preview-option {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    transition: var(--transition);
}

.preview-option.correct {
    border-color: var(--secondary-color);
    background-color: rgba(16, 185, 129, 0.1);
}

.preview-option-letter {
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-light);
    color: var(--primary-color);
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.75rem;
}

.preview-option.correct .preview-option-letter {
    background-color: var(--secondary-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .create-quiz-page .form-actions {
        flex-direction: column;
    }
    
    .create-quiz-page .difficulty-selector {
        flex-direction: column;
    }
    
    .create-quiz-page .option-block {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .create-quiz-page .option-correct {
        margin-left: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionIndex = <?= count($questions) ?>;
    const questionsContainer = document.getElementById('questions-container');
    const addQuestionBtn = document.getElementById('add-question');
    const previewBtn = document.getElementById('preview-quiz');
    const previewModal = document.getElementById('preview-modal');
    const previewContent = document.getElementById('preview-content');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const form = document.getElementById('edit-quiz-form');

    // Ajouter une nouvelle question
    addQuestionBtn.addEventListener('click', function() {
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.dataset.questionIndex = questionIndex;
        
        questionBlock.innerHTML = `
            <div class="question-header">
                <h3 class="question-title">Question ${questionIndex + 1}</h3>
                <button type="button" class="btn-icon remove-question" title="Supprimer cette question">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
            
            <div class="form-group">
                <label for="question-${questionIndex}">Texte de la question <span class="required">*</span></label>
                <input type="text" id="question-${questionIndex}" name="questions[${questionIndex}][text]" required placeholder="Saisissez votre question">
            </div>
            
            <div class="options-container">
                <label class="options-label">Options <span class="required">*</span> <span class="options-hint">(Sélectionnez la réponse correcte)</span></label>
                
                <div class="option-block">
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][0][text]" placeholder="Option 1" required>
                    </div>
                    <div class="option-correct">
                        <input type="radio" id="q${questionIndex}-option0" name="questions[${questionIndex}][correct_option]" value="0" required>
                        <label for="q${questionIndex}-option0" class="radio-label">Correcte</label>
                    </div>
                </div>
                
                <div class="option-block">
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][1][text]" placeholder="Option 2" required>
                    </div>
                    <div class="option-correct">
                        <input type="radio" id="q${questionIndex}-option1" name="questions[${questionIndex}][correct_option]" value="1">
                        <label for="q${questionIndex}-option1" class="radio-label">Correcte</label>
                    </div>
                </div>
                
                <div class="option-block">
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][2][text]" placeholder="Option 3" required>
                    </div>
                    <div class="option-correct">
                        <input type="radio" id="q${questionIndex}-option2" name="questions[${questionIndex}][correct_option]" value="2">
                        <label for="q${questionIndex}-option2" class="radio-label">Correcte</label>
                    </div>
                </div>
                
                <div class="option-block">
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][3][text]" placeholder="Option 4" required>
                    </div>
                    <div class="option-correct">
                        <input type="radio" id="q${questionIndex}-option3" name="questions[${questionIndex}][correct_option]" value="3">
                        <label for="q${questionIndex}-option3" class="radio-label">Correcte</label>
                    </div>
                </div>
            </div>
        `;
        
        questionsContainer.appendChild(questionBlock);
        questionIndex++;
        
        // Activer tous les boutons de suppression
        document.querySelectorAll('.remove-question').forEach(btn => {
            btn.disabled = document.querySelectorAll('.question-block').length <= 1;
        });
    });
    
    // Supprimer une question
    questionsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-question')) {
            const questionBlock = e.target.closest('.question-block');
            questionBlock.remove();
            
            // Mettre à jour les numéros de questions
            document.querySelectorAll('.question-block').forEach((block, index) => {
                block.querySelector('.question-title').textContent = `Question ${index + 1}`;
            });
            
            // Désactiver le bouton de suppression s'il ne reste qu'une question
            document.querySelectorAll('.remove-question').forEach(btn => {
                btn.disabled = document.querySelectorAll('.question-block').length <= 1;
            });
        }
    });
    
    // Prévisualiser le quiz
    previewBtn.addEventListener('click', function() {
        const title = document.getElementById('titre').value || 'Sans titre';
        const description = document.getElementById('description').value || 'Aucune description';
        const categorySelect = document.getElementById('categorie_id');
        const category = categorySelect.options[categorySelect.selectedIndex].text;
        const difficultyOptions = document.querySelectorAll('input[name="difficulte"]');
        let difficulty = '';
        let difficultyClass = '';
        
        difficultyOptions.forEach(option => {
            if (option.checked) {
                switch(option.value) {
                    case '1':
                        difficulty = 'Facile';
                        difficultyClass = 'easy';
                        break;
                    case '2':
                        difficulty = 'Moyen';
                        difficultyClass = 'medium';
                        break;
                    case '3':
                        difficulty = 'Difficile';
                        difficultyClass = 'hard';
                        break;
                }
            }
        });
        
        const questions = [];
        document.querySelectorAll('.question-block').forEach((block, index) => {
            const questionText = block.querySelector('input[name^="questions"][name$="[text]"]').value || `Question ${index + 1}`;
            const options = [];
            const correctOption = block.querySelector('input[name^="questions"][name$="[correct_option]"]:checked')?.value || '0';
            
            block.querySelectorAll('input[name^="questions"][name$="[options]"][name$="[text]"]').forEach((option, optIndex) => {
                options.push({
                    text: option.value || `Option ${optIndex + 1}`,
                    isCorrect: optIndex.toString() === correctOption
                });
            });
            
            questions.push({
                text: questionText,
                options: options
            });
        });
        
        // Générer le HTML de prévisualisation
        let previewHTML = `
            <div class="preview-quiz">
                <h3 class="preview-quiz-title">${title}</h3>
                <p class="preview-quiz-description">${description}</p>
                
                <div class="preview-quiz-info">
                    <div class="preview-quiz-info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="preview-quiz-info-icon">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Catégorie: ${category !== 'Sélectionnez une catégorie' ? category : 'Non spécifiée'}</span>
                    </div>
                    
                    <div class="preview-quiz-info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="preview-quiz-info-icon ${difficultyClass}">
                            <path d="M18 20V10"></path>
                            <path d="M12 20V4"></path>
                            <path d="M6 20v-6"></path>
                        </svg>
                        <span>Difficulté: ${difficulty}</span>
                    </div>
                    
                    <div class="preview-quiz-info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="preview-quiz-info-icon">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Questions: ${questions.length}</span>
                    </div>
                </div>
        `;
        
        questions.forEach((question, index) => {
            previewHTML += `
                <div class="preview-question">
                    <div class="preview-question-number">Question ${index + 1}</div>
                    <div class="preview-question-text">${question.text}</div>
                    
                    <div class="preview-options">
            `;
            
            const letters = ['A', 'B', 'C', 'D'];
            question.options.forEach((option, optIndex) => {
                previewHTML += `
                    <div class="preview-option ${option.isCorrect ? 'correct' : ''}">
                        <div class="preview-option-letter">${letters[optIndex]}</div>
                        <div class="preview-option-text">${option.text}</div>
                    </div>
                `;
            });
            
            previewHTML += `
                    </div>
                </div>
            `;
        });
        
        previewHTML += `</div>`;
        
        previewContent.innerHTML = previewHTML;
        previewModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
    
    // Fermer la modal
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            previewModal.style.display = 'none';
            document.body.style.overflow = '';
        });
    });
    
    // Fermer la modal en cliquant en dehors
    window.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    
    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        const questions = document.querySelectorAll('.question-block');
        
        if (questions.length < 1) {
            e.preventDefault();
            alert('Veuillez ajouter au moins une question.');
            return;
        }
        
        let hasError = false;
        
        questions.forEach((question, index) => {
            const questionText = question.querySelector(`input[name="questions[${index}][text]"]`).value.trim();
            if (!questionText) {
                e.preventDefault();
                hasError = true;
                alert(`La question ${index + 1} ne peut pas être vide.`);
                return;
            }
            
            const options = question.querySelectorAll(`input[name^="questions[${index}][options]"][name$="[text]"]`);
            const optionValues = Array.from(options).map(opt => opt.value.trim());
            
            // Vérifier si toutes les options sont remplies
            if (optionValues.some(val => !val)) {
                e.preventDefault();
                hasError = true;
                alert(`Toutes les options de la question ${index + 1} doivent être remplies.`);
                return;
            }
            
            // Vérifier si les options sont uniques
            const uniqueOptions = new Set(optionValues);
            if (uniqueOptions.size < optionValues.length) {
                e.preventDefault();
                hasError = true;
                alert(`Les options de la question ${index + 1} doivent être uniques.`);
                return;
            }
            
            // Vérifier si une réponse correcte est sélectionnée
            const correctOption = question.querySelector(`input[name="questions[${index}][correct_option]"]:checked`);
            if (!correctOption) {
                e.preventDefault();
                hasError = true;
                alert(`Veuillez sélectionner une réponse correcte pour la question ${index + 1}.`);
                return;
            }
        });
        
        if (!hasError) {
            // Ajouter une animation de chargement
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon spin">
                    <line x1="12" y1="2" x2="12" y2="6"></line>
                    <line x1="12" y1="18" x2="12" y2="22"></line>
                    <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                    <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                    <line x1="2" y1="12" x2="6" y2="12"></line>
                    <line x1="18" y1="12" x2="22" y2="12"></line>
                    <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                    <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                </svg>
                Enregistrement en cours...
            `;
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>