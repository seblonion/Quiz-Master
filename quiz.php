<?php
$titre_page = "Quiz";
$scripts_supplementaires = ['assets/js/quiz.js'];
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    $_SESSION['message'] = "Vous devez être connecté pour accéder au quiz.";
    $_SESSION['message_type'] = "error";
    rediriger('connexion.php');
}

// Vérifier si les paramètres sont présents
if (!isset($_GET['categorie']) || !isset($_GET['difficulte'])) {
    rediriger('categorie.php');
}

$categorie_id = (int)$_GET['categorie'];
$difficulte_id = (int)$_GET['difficulte'];

// Récupérer les informations sur la catégorie et la difficulté
$categorie = obtenirCategorie($categorie_id);
$difficulte = obtenirDifficulte($difficulte_id);

if (!$categorie || !$difficulte) {
    rediriger('categorie.php');
}

// Fonction pour obtenir 10 questions aléatoires sans répétition
function obtenirQuestionsAleatoires($categorie_id, $difficulte_id, $nombre = 10) {
    $database = new Database();
    $db = $database->connect();
    
    // Récupérer les questions déjà utilisées dans la session
    $questions_utilisees = isset($_SESSION['questions_utilisees']) ? $_SESSION['questions_utilisees'] : [];
    
    // Compter le nombre total de questions disponibles
    $query = "SELECT COUNT(*) as total FROM questions WHERE categorie_id = :categorie_id AND difficulte_id = :difficulte_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
    $stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    $total_questions = $result['total'];
    
    // Si pas assez de questions, prendre toutes les questions disponibles
    $nombre_a_prendre = min($nombre, $total_questions);
    
    // Construire la requête avec des paramètres nommés pour NOT IN
    $placeholders = [];
    $params = [
        ':categorie_id' => $categorie_id,
        ':difficulte_id' => $difficulte_id,
        ':nombre' => $nombre_a_prendre
    ];
    
    if (!empty($questions_utilisees)) {
        foreach ($questions_utilisees as $index => $id) {
            $placeholder = ":question_id_$index";
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }
        $not_in_clause = implode(',', $placeholders);
    } else {
        $not_in_clause = '0';
    }
    
    $query = "SELECT q.*, c.nom as categorie_nom, d.nom as difficulte_nom 
              FROM questions q
              JOIN categories c ON q.categorie_id = c.id
              JOIN difficultes d ON q.difficulte_id = d.id
              WHERE q.categorie_id = :categorie_id 
              AND q.difficulte_id = :difficulte_id
              AND q.id NOT IN ($not_in_clause)
              ORDER BY RAND()
              LIMIT :nombre";
    
    $stmt = $db->prepare($query);
    
    // Lier tous les paramètres
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $questions = $stmt->fetchAll();
    
    // Récupérer les options pour chaque question
    foreach ($questions as &$question) {
        $query = "SELECT * FROM options WHERE question_id = :question_id ORDER BY RAND()";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_id', $question['id'], PDO::PARAM_INT);
        $stmt->execute();
        $question['options'] = $stmt->fetchAll();
    }
    
    return $questions;
}

// Récupérer 10 questions aléatoires
$questions = obtenirQuestionsAleatoires($categorie_id, $difficulte_id, 10);

if (empty($questions)) {
    $_SESSION['message'] = "Il n'y a pas assez de questions disponibles pour ce quiz. Veuillez choisir une autre catégorie ou difficulté.";
    $_SESSION['message_type'] = "error";
    rediriger('categorie.php');
}

// Stocker les IDs des questions utilisées
if (!isset($_SESSION['questions_utilisees'])) {
    $_SESSION['questions_utilisees'] = [];
}

foreach ($questions as $question) {
    $_SESSION['questions_utilisees'][] = $question['id'];
}

// Limiter la taille de questions_utilisees à 50
if (count($_SESSION['questions_utilisees']) > 50) {
    $_SESSION['questions_utilisees'] = array_slice($_SESSION['questions_utilisees'], -50);
}

$titre_page = "Quiz: " . htmlspecialchars($categorie['nom']) . " - " . htmlspecialchars($difficulte['nom']);
?>

<main class="quiz-container">
    <div class="quiz-wrapper">
        <header class="quiz-header">
            <h1><?= htmlspecialchars($categorie['nom']) ?></h1>
            <div class="quiz-meta">
                <span class="difficulty-badge"><?= htmlspecialchars($difficulte['nom']) ?></span>
                <span class="time-info">15 secondes par question</span>
            </div>
        </header>

        <div class="quiz-progress">
            <div class="progress-info">
                <span class="current-question">1</span>/<span class="total-questions"><?= count($questions) ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= (1 / count($questions)) * 100 ?>%"></div>
            </div>
        </div>

        <form id="quiz-form" data-total-questions="<?= count($questions) ?>" data-timer-seconds="15">
            <input type="hidden" name="categorie_id" value="<?= $categorie_id ?>">
            <input type="hidden" name="difficulte_id" value="<?= $difficulte_id ?>">

            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card <?= $index === 0 ? 'active' : '' ?>" data-question-index="<?= $index ?>">
                    <div class="question-header">
                        <div class="timer-container">
                            <svg class="timer-circle" viewBox="0 0 36 36">
                                <path class="timer-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="timer-fill" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="timer-text" id="timer-seconds-<?= $index ?>">15</span>
                        </div>
                        <div class="question-number">Question <?= $index + 1 ?>/<?= count($questions) ?></div>
                    </div>
                    
                    <h2 class="question-text"><?= htmlspecialchars($question['question']) ?></h2>
                    
                    <div class="options-grid">
                        <?php foreach ($question['options'] as $option): ?>
                            <div class="option-item">
                                <input type="radio"
                                       id="q<?= $question['id'] ?>_o<?= $option['id'] ?>"
                                       name="question_<?= $question['id'] ?>"
                                       value="<?= $option['id'] ?>"
                                       data-correct="<?= $option['est_correcte'] ?>">
                                <label for="q<?= $question['id'] ?>_o<?= $option['id'] ?>">
                                    <?= htmlspecialchars($option['texte']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="feedback hidden">
                        <div class="feedback-correct hidden">
                            <svg class="feedback-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>Correct !</span>
                        </div>
                        <div class="feedback-incorrect hidden">
                            <svg class="feedback-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span>Incorrect !</span>
                            <span class="time-expired-message hidden">Temps écoulé !</span>
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-submit-answer">Valider ma réponse</button>
                        <button type="button" class="btn btn-next-question hidden">
                            <?= $index < count($questions) - 1 ? 'Question suivante' : 'Voir mes résultats' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="results-card hidden">
                <div class="results-header">
                    <h2>Quiz Terminé !</h2>
                    <div class="results-score">
                        <div class="score-circle">
                            <span class="score">0</span>/<span><?= count($questions) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="results-message"></div>
                
                <h3>Récapitulatif des questions :</h3>
                <div class="results-summary"></div>
                
                <div class="results-actions">
                    <button type="button" class="btn btn-outline btn-restart">Recommencer</button>
                    <a href="categorie.php?id=<?= $categorie_id ?>" class="btn btn-primary">Retour à la catégorie</a>
                </div>
            </div>

            <!-- Modal pour continuer/recommencer -->
            <div class="modal hidden" id="progress-modal">
                <div class="modal-content">
                    <h2>Reprendre le quiz</h2>
                    <p>Vous avez un quiz en cours dans cette catégorie et difficulté. Voulez-vous continuer là où vous vous êtes arrêté ou recommencer un nouveau quiz ?</p>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-primary" id="continue-quiz">Continuer</button>
                        <button type="button" class="btn btn-outline" id="restart-quiz">Recommencer</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --success-color: #10b981;
    --error-color: #ef4444;
    --background-color: #f9fafb;
    --card-background: #ffffff;
    --text-color: #1f2937;
    --text-muted: #6b7280;
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

.quiz-container {
    padding: 2rem 1rem;
    min-height: 100vh;
    display: flex;
    justify-content: center;
}

.quiz-wrapper {
    width: 100%;
    max-width: 800px;
}

/* Header Styles */
.quiz-header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.quiz-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

.quiz-meta {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.difficulty-badge {
    background-color: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.time-info {
    color: var(--text-muted);
    font-size: 0.875rem;
}

/* Progress Bar */
.quiz-progress {
    margin-bottom: 1.5rem;
    background-color: var(--card-background);
    padding: 1rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
}

.progress-info {
    display: flex;
    justify-content: center;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.progress-bar {
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(to right, var(--primary-color), #818cf8);
    transition: width 0.3s ease;
}

/* Question Card */
.question-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    display: none;
}

.question-card.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.question-number {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-weight: 500;
}

.timer-container {
    position: relative;
    width: 40px;
    height: 40px;
}

.timer-circle {
    transform: rotate(-90deg);
    width: 100%;
    height: 100%;
}

.timer-bg {
    fill: none;
    stroke: #e5e7eb;
    stroke-width: 3;
}

.timer-fill {
    fill: none;
    stroke: var(--primary-color);
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dashoffset 15s linear;
}

.timer-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.875rem;
    font-weight: 600;
}

.question-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    line-height: 1.4;
}

/* Options */
.options-grid {
    display: grid;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.option-item {
    position: relative;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.option-item:hover {
    border-color: #d1d5db;
    background-color: #f9fafb;
}

.option-item input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.option-item label {
    display: block;
    padding: 1rem;
    cursor: pointer;
    font-size: 1rem;
    position: relative;
    padding-left: 2.5rem;
}

.option-item label:before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.option-item input:checked + label:before {
    border-color: var(--primary-color);
    background-color: var(--primary-color);
    box-shadow: inset 0 0 0 4px white;
}

.option-item.correct {
    border-color: var(--success-color);
    background-color: rgba(16, 185, 129, 0.1);
}

.option-item.incorrect {
    border-color: var(--error-color);
    background-color: rgba(239, 68, 68, 0.1);
}

/* Feedback */
.feedback {
    margin: 1rem 0;
    padding: 1rem;
    border-radius: 8px;
    font-weight: 500;
}

.feedback-correct {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.feedback-incorrect {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--error-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.feedback-icon {
    width: 20px;
    height: 20px;
}

.time-expired-message {
    margin-left: auto;
    font-weight: 600;
}

/* Buttons */
.question-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    outline: none;
}

.btn-submit-answer {
    background-color: var(--primary-color);
    color: white;
}

.btn-submit-answer:hover {
    background-color: var(--primary-hover);
}

.btn-next-question {
    background-color: var(--primary-color);
    color: white;
}

.btn-next-question:hover {
    background-color: var(--primary-hover);
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
}

.btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-color);
    color: white;
}

/* Results Card */
.results-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 2rem;
}

.results-header {
    text-align: center;
    margin-bottom: 2rem;
}

.results-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.results-score {
    display: flex;
    justify-content: center;
}

.score-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
}

.results-message {
    padding: 1rem;
    border-radius: 8px;
    margin: 1.5rem 0;
    font-weight: 500;
    text-align: center;
}

.results-summary {
    margin-top: 1.5rem;
    display: grid;
    gap: 1rem;
}

.result-item {
    padding: 1rem;
    border-radius: 8px;
    background-color: #f9fafb;
}

.result-item.correct {
    border-left: 4px solid var(--success-color);
}

.result-item.incorrect {
    border-left: 4px solid var(--error-color);
}

.results-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    padding: 2rem;
    max-width: 500px;
    width: 90%;
}

.modal-content h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-align: center;
}

.modal-content p {
    margin-bottom: 1.5rem;
    text-align: center;
}

.modal-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Utility Classes */
.hidden {
    display: none !important;
}

/* Responsive Design */
@media (max-width: 640px) {
    .quiz-header h1 {
        font-size: 1.5rem;
    }
    
    .question-text {
        font-size: 1.125rem;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
    }
    
    .question-actions {
        flex-direction: column;
    }
    
    .question-actions .btn {
        width: 100%;
    }
    
    .results-actions {
        flex-direction: column;
    }
    
    .results-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const totalTimerSeconds = parseInt(document.getElementById('quiz-form').dataset.timerSeconds);
    let currentTimerInterval = null;
    let currentQuestionIndex = 0;
    let answers = {};
    const totalQuestions = parseInt(document.getElementById('quiz-form').dataset.totalQuestions);
    const categorieId = document.querySelector('input[name="categorie_id"]').value;
    const difficulteId = document.querySelector('input[name="difficulte_id"]').value;

    // Charger la progression sauvegardée
    async function loadProgress() {
        try {
            const response = await fetch(`get_quiz_progress.php?categorie_id=${categorieId}&difficulte_id=${difficulteId}`);
            const data = await response.json();
            const modal = document.getElementById('progress-modal');
            if (data.success && data.progress) {
                modal.classList.remove('hidden');
                
                document.getElementById('continue-quiz').addEventListener('click', () => {
                    currentQuestionIndex = data.progress.current_question_index || 0;
                    answers = data.progress.answers || {};
                    updateUI();
                    modal.classList.add('hidden');
                    if (currentQuestionIndex < totalQuestions) {
                        startTimer();
                    } else {
                        displayResults();
                    }
                });

                document.getElementById('restart-quiz').addEventListener('click', async () => {
                    try {
                        await fetch('clear_quiz_progress.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ categorie_id: categorieId, difficulte_id: difficulteId })
                        });
                        modal.classList.add('hidden');
                        currentQuestionIndex = 0;
                        answers = {};
                        updateUI();
                        startTimer();
                    } catch (error) {
                        console.error('Erreur lors de la réinitialisation:', error);
                    }
                });
            } else {
                startTimer();
            }
        } catch (error) {
            console.error('Erreur lors du chargement de la progression:', error);
            startTimer();
        }
    }

    // Sauvegarder la progression
    async function saveProgress() {
        try {
            const timerElement = document.getElementById(`timer-seconds-${currentQuestionIndex}`);
            const progressData = {
                categorie_id: categorieId,
                difficulte_id: difficulteId,
                current_question_index: currentQuestionIndex,
                answers: answers,
                time_elapsed: totalTimerSeconds - (parseInt(timerElement.textContent) || 0)
            };
            const response = await fetch('save_quiz_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(progressData)
            });
            const data = await response.json();
            if (!data.success) {
                console.error('Erreur lors de la sauvegarde de la progression:', data.message);
            }
        } catch (error) {
            console.error('Erreur réseau lors de la sauvegarde:', error);
        }
    }

    // Timer logic
    function startTimer() {
        const activeQuestion = document.querySelector('.question-card.active');
        const timerElement = activeQuestion.querySelector('.timer-text');
        const timerFill = activeQuestion.querySelector('.timer-fill');
        let seconds = totalTimerSeconds;

        // Reset timer visuals
        timerElement.textContent = seconds;
        timerFill.style.strokeDashoffset = '0';
        timerFill.style.transition = 'none';

        // Force reflow
        void timerFill.offsetWidth;

        // Start animations
        timerFill.style.transition = `stroke-dashoffset ${totalTimerSeconds}s linear`;
        timerFill.style.strokeDashoffset = '100';

        // Clear any existing interval
        if (currentTimerInterval) clearInterval(currentTimerInterval);

        // Start countdown
        currentTimerInterval = setInterval(() => {
            seconds--;
            timerElement.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(currentTimerInterval);
                if (activeQuestion) {
                    const submitButton = activeQuestion.querySelector('.btn-submit-answer');
                    if (submitButton && !submitButton.classList.contains('hidden')) {
                        const questionId = activeQuestion.querySelector('input[name^="question_"]').name.split('_')[1];
                        answers[questionId] = null;
                        const feedback = activeQuestion.querySelector('.feedback');
                        const feedbackIncorrect = activeQuestion.querySelector('.feedback-incorrect');
                        const timeExpiredMessage = activeQuestion.querySelector('.time-expired-message');
                        feedback.classList.remove('hidden');
                        feedbackIncorrect.classList.remove('hidden');
                        timeExpiredMessage.classList.remove('hidden');
                        submitButton.classList.add('hidden');
                        activeQuestion.querySelector('.btn-next-question').classList.remove('hidden');
                        saveProgress();
                        setTimeout(() => {
                            const nextButton = activeQuestion.querySelector('.btn-next-question');
                            if (nextButton && !nextButton.classList.contains('hidden')) nextButton.click();
                        }, 2000);
                    }
                }
            }
        }, 1000);
    }

    // Update UI
    function updateUI() {
        document.querySelector('.current-question').textContent = currentQuestionIndex + 1;
        document.querySelector('.progress-fill').style.width = `${((currentQuestionIndex + 1) / totalQuestions) * 100}%`;
        document.querySelectorAll('.question-card').forEach((card, index) => {
            card.classList.toggle('active', index === currentQuestionIndex);
            card.style.display = index === currentQuestionIndex ? 'block' : 'none';
        });
    }

    // Event listeners
    document.querySelectorAll('.btn-submit-answer').forEach(button => {
        button.addEventListener('click', function() {
            const questionCard = this.closest('.question-card');
            const questionId = questionCard.querySelector('input[name^="question_"]').name.split('_')[1];
            const selectedOption = questionCard.querySelector('input:checked');
            const feedback = questionCard.querySelector('.feedback');
            const feedbackCorrect = questionCard.querySelector('.feedback-correct');
            const feedbackIncorrect = questionCard.querySelector('.feedback-incorrect');

            if (selectedOption) {
                answers[questionId] = selectedOption.value;
                const isCorrect = selectedOption.dataset.correct === '1';
                selectedOption.parentElement.classList.add(isCorrect ? 'correct' : 'incorrect');
                feedback.classList.remove('hidden');
                feedbackCorrect.classList.toggle('hidden', !isCorrect);
                feedbackIncorrect.classList.toggle('hidden', isCorrect);
            } else {
                answers[questionId] = null;
                feedback.classList.remove('hidden');
                feedbackIncorrect.classList.remove('hidden');
                questionCard.querySelector('.time-expired-message').classList.remove('hidden');
            }

            this.classList.add('hidden');
            questionCard.querySelector('.btn-next-question').classList.remove('hidden');
            if (currentTimerInterval) clearInterval(currentTimerInterval);
            saveProgress();
        });
    });

    document.querySelectorAll('.btn-next-question').forEach(button => {
        button.addEventListener('click', () => {
            currentQuestionIndex++;
            if (currentQuestionIndex < totalQuestions) {
                updateUI();
                setTimeout(startTimer, 100);
            } else {
                document.querySelector('.results-card').classList.remove('hidden');
                document.querySelectorAll('.question-card').forEach(card => card.style.display = 'none');
                displayResults();
            }
        });
    });

    document.querySelector('.btn-restart')?.addEventListener('click', async () => {
        if (confirm('Voulez-vous vraiment recommencer le quiz ? Toute progression sera perdue.')) {
            try {
                await fetch('clear_quiz_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ categorie_id: categorieId, difficulte_id: difficulteId })
                });
                window.location.reload();
            } catch (error) {
                console.error('Erreur lors de la réinitialisation:', error);
            }
        }
    });

    function displayResults() {
        const resultsSummary = document.querySelector('.results-summary');
        const scoreElement = document.querySelector('.score');
        let score = 0;

        document.querySelectorAll('.question-card').forEach((card, index) => {
            const questionId = card.querySelector('input[name^="question_"]').name.split('_')[1];
            const selectedOption = answers[questionId] ? document.querySelector(`input[name="question_${questionId}"][value="${answers[questionId]}"]`) : null;
            const isCorrect = selectedOption && selectedOption.dataset.correct === '1';
            if (isCorrect) score++;

            const resultItem = document.createElement('div');
            resultItem.classList.add('result-item', isCorrect ? 'correct' : 'incorrect');
            resultItem.innerHTML = `
                <p><strong>Question ${index + 1}:</strong> ${card.querySelector('.question-text').textContent}</p>
                <p>Votre réponse: ${selectedOption ? selectedOption.nextElementSibling.textContent : 'Aucune'}</p>
                ${!isCorrect ? `<p>Réponse correcte: ${card.querySelector('input[data-correct="1"]').nextElementSibling.textContent}</p>` : ''}
            `;
            resultsSummary.appendChild(resultItem);
        });

        scoreElement.textContent = score;
        const resultsMessage = document.querySelector('.results-message');
        resultsMessage.textContent = score === totalQuestions ? 'Félicitations, score parfait !' : `Bon travail ! Essayez encore pour améliorer votre score.`;
        resultsMessage.classList.add(score === totalQuestions ? 'feedback-correct' : 'feedback-incorrect');
    }

    // Initialisation
    loadProgress();
});
</script>

<?php require_once 'includes/footer.php'; ?>