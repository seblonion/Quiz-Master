<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer la question
$question_data = obtenirQuestion($id);

if (!$question_data) {
    $_SESSION['message'] = 'Question non trouvée';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Récupérer toutes les catégories et difficultés
$categories = obtenirToutesLesCategories();
$difficultes = obtenirToutesLesDifficultes();

$erreur = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'] ?? '';
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $difficulte_id = (int)($_POST['difficulte_id'] ?? 0);
    
    $options = [];
    $texte_options = $_POST['option_texte'] ?? [];
    $est_correcte = $_POST['option_correcte'] ?? [];
    
    // Vérification des données
    if (empty($question)) {
        $erreur = 'Le texte de la question est requis';
    } elseif ($categorie_id <= 0) {
        $erreur = 'Veuillez sélectionner une catégorie';
    } elseif ($difficulte_id <= 0) {
        $erreur = 'Veuillez sélectionner une difficulté';
    } elseif (count($texte_options) < 2) {
        $erreur = 'Vous devez fournir au moins deux options';
    } elseif (!isset($est_correcte[0])) {
        $erreur = 'Vous devez indiquer quelle option est correcte';
    } else {
        // Préparer les options
        for ($i = 0; $i < count($texte_options); $i++) {
            if (!empty($texte_options[$i])) {
                $options[] = [
                    'texte' => $texte_options[$i],
                    'est_correcte' => ($est_correcte[0] == $i) ? 1 : 0
                ];
            }
        }
        
        if (count($options) < 2) {
            $erreur = 'Vous devez fournir au moins deux options non vides';
        } else {
            // Tout est bon, on peut mettre à jour la question
            $result = mettreAJourQuestion($id, $question, $categorie_id, $difficulte_id, $options);
            
            if ($result) {
                $success = 'La question a été mise à jour avec succès';
                // Mettre à jour les données de la question
                $question_data = obtenirQuestion($id);
            } else {
                $erreur = 'Une erreur est survenue lors de la mise à jour de la question';
            }
        }
    }
}

// Inclure l'en-tête
$titre_page = "Modifier la question";
include '../includes/header.php';
?>

<main class="create-quiz-page">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Modifier la question #<?= $id ?></h1>
            <p class="page-description">Modifiez les détails de la question et ses options</p>
        </div>

        <div class="quiz-form-container">
            <form method="post" class="quiz-form">
                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-error">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <?= $erreur ?>
                        <button type="button" class="close-alert">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <?= $success ?>
                        <button type="button" class="close-alert">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Détails de la question
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="question">Question <span class="required">*</span></label>
                            <textarea id="question" name="question" class="form-control" rows="4" required placeholder="Saisissez le texte de la question"><?= htmlspecialchars($question_data['question']) ?></textarea>
                            <div class="form-hint">Entrez une question claire et précise</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie_id">Catégorie <span class="required">*</span></label>
                            <div class="select-wrapper">
                                <select id="categorie_id" name="categorie_id" class="form-control" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php foreach ($categories as $categorie): ?>
                                        <option value="<?= $categorie['id'] ?>" <?= $question_data['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categorie['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="select-icon">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="form-hint">Choisir une catégorie aide à classer la question</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="difficulte_id">Difficulté <span class="required">*</span></label>
                            <div class="select-wrapper">
                                <select id="difficulte_id" name="difficulte_id" class="form-control" required>
                                    <option value="">Sélectionner une difficulté</option>
                                    <?php foreach ($difficultes as $difficulte): ?>
                                        <option value="<?= $difficulte['id'] ?>" <?= $question_data['difficulte_id'] == $difficulte['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($difficulte['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="select-icon">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="form-hint">Sélectionnez le niveau de difficulté</div>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Options de réponse
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="options-container">
                            <label class="options-label">Options <span class="required">*</span> <span class="options-hint">(Sélectionnez la réponse correcte)</span></label>
                            <div id="options-container">
                                <?php foreach ($question_data['options'] as $i => $option): ?>
                                    <div class="option-block" data-animation-delay="<?= $i * 0.1 ?>s">
                                        <div class="option-input">
                                            <input type="text" name="option_texte[]" class="form-control" placeholder="Texte de l'option" value="<?= htmlspecialchars($option['texte']) ?>" required>
                                        </div>
                                        <div class="option-correct">
                                            <input type="radio" id="option-<?= $i ?>" name="option_correcte[]" value="<?= $i ?>" <?= $option['est_correcte'] ? 'checked' : '' ?> required>
                                            <label for="option-<?= $i ?>" class="radio-label">Correcte</label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="button" id="add-option" class="btn btn-outline btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Ajouter une option
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                        Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
:root {
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
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.create-quiz-page {
    padding: 2rem 0 4rem;
}

/* Page Header */
.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.page-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Form Container */
.quiz-form-container {
    max-width: 800px;
    margin: 0 auto;
}

.quiz-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Form Card */
.form-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeIn 0.5s ease-out forwards;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--background-color);
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.card-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    color: var(--primary-color);
}

.card-body {
    padding: 1.5rem;
}

/* Form Group */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.required {
    color: #ef4444;
}

.form-hint {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Form Inputs */
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

.select-wrapper {
    position: relative;
}

.select-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    height: 1rem;
    color: var(--text-muted);
    pointer-events: none;
}

select.form-control {
    appearance: none;
    padding-right: 2rem;
}

/* Options Container */
.options-container {
    margin-top: 1rem;
}

.options-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.options-hint {
    font-size: 0.75rem;
    font-weight: normal;
    color: var(--text-muted);
}

.option-block {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 1rem;
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: var(--animation-delay, 0s);
}

.option-input {
    flex: 1;
}

.option-correct {
    display: flex;
    align-items: center;
    min-width: 100px;
}

.option-correct input[type="radio"] {
    margin-right: 0.5rem;
}

.radio-label {
    font-size: 0.75rem;
    color: var(--text-muted);
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
    font-size: 0.875rem;
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
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.btn-icon, .btn-icon-left {
    width: 1rem;
    height: 1rem;
}

.btn-icon-left {
    margin-right: 0.5rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

/* Alert Styles */
.alert {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    position: relative;
    font-size: 0.875rem;
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

.alert-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
}

.close-alert {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: inherit;
    opacity: 0.7;
}

.close-alert:hover {
    opacity: 1;
}

.close-icon {
    width: 1rem;
    height: 1rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .option-block {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .option-correct {
        margin-left: 0.5rem;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des alertes
    const closeAlerts = document.querySelectorAll('.close-alert');
    closeAlerts.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.style.display = 'none';
        });
    });

    // Gestion de l'ajout d'options
    const addOptionBtn = document.getElementById('add-option');
    const optionsContainer = document.getElementById('options-container');
    
    addOptionBtn.addEventListener('click', function() {
        const optionBlocks = document.querySelectorAll('.option-block');
        const newIndex = optionBlocks.length;
        
        const newBlock = document.createElement('div');
        newBlock.className = 'option-block';
        newBlock.style.animationDelay = `${newIndex * 0.1}s`;
        newBlock.innerHTML = `
            <div class="option-input">
                <input type="text" name="option_texte[]" class="form-control" placeholder="Texte de l'option" required>
            </div>
            <div class="option-correct">
                <input type="radio" id="option-${newIndex}" name="option_correcte[]" value="${newIndex}">
                <label for="option-${newIndex}" class="radio-label">Correcte</label>
            </div>
        `;
        
        optionsContainer.appendChild(newBlock);
    });
});
</script>

<?php include '../includes/footer.php'; ?>