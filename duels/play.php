<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

if (!estConnecte()) {
    error_log("play.php : Utilisateur non connecté, redirection vers connexion.php");
    header('Location: /quizmaster/connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];
error_log("play.php : Session utilisateur_id = " . ($user_id ?? 'non défini'));

if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("play.php : ID du duel manquant, redirection vers index.php");
    header('Location: /quizmaster/duels/index.php');
    exit;
}

$duel_id = (int)$_GET['id'];

$duel = getDuelById($duel_id);
if (!$duel || ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id)) {
    error_log("play.php : Duel ID $duel_id non trouvé ou utilisateur $user_id non autorisé");
    $_SESSION['message'] = "Vous n'avez pas accès à ce duel.";
    $_SESSION['message_type'] = "error";
    header('Location: /quizmaster/duels/index.php');
    exit;
}

if ($duel['status'] !== 'active') {
    error_log("play.php : Duel ID $duel_id n'est pas actif, statut = " . $duel['status']);
    $_SESSION['message'] = "Ce duel n'est pas actif.";
    $_SESSION['message_type'] = "error";
    header('Location: /quizmaster/duels/index.php');
    exit;
}

$questions = getDuelQuestions($duel_id);
if (empty($questions)) {
    error_log("play.php : Aucune question disponible pour le duel ID $duel_id");
    $_SESSION['message'] = "Erreur : Aucune question disponible pour ce duel.";
    $_SESSION['message_type'] = "error";
    header('Location: /quizmaster/duels/index.php');
    exit;
}

error_log("play.php : Questions récupérées pour le duel ID $duel_id : " . json_encode($questions));

$titre_page = "Duel en cours";
include '../includes/header.php';
?>

<main class="duel-play-page">
    <div class="container">
        <div class="duel-header">
            <h1 class="duel-title">
                <?= htmlspecialchars($duel['challenger_nom']) ?> vs <?= htmlspecialchars($duel['opponent_nom']) ?>
            </h1>
            <div class="duel-info">
                <span class="duel-type badge badge-<?= $duel['type'] ?>">
                    <?= getDuelTypeLabel($duel['type']) ?>
                </span>
                <?php if ($duel['categorie_nom']): ?>
                    <span class="duel-category">
                        Catégorie: <?= htmlspecialchars($duel['categorie_nom']) ?>
                    </span>
                <?php endif; ?>
                <?php if ($duel['difficulte_nom']): ?>
                    <span class="duel-difficulty">
                        Difficulté: <?= htmlspecialchars($duel['difficulte_nom']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="duel-container">
            <div class="duel-progress-container">
                <div class="duel-progress">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>
                <div class="duel-stats">
                    <div class="stat">
                        <span class="stat-label">Question</span>
                        <span class="stat-value" id="question-counter">1/<?= count($questions) ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Score</span>
                        <span class="stat-value" id="score-counter">0</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Temps</span>
                        <span class="stat-value" id="time-counter">00:00</span>
                    </div>
                </div>
            </div>

            <div id="quiz-container" class="quiz-container">
                <div id="loading" class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Chargement du duel...</p>
                </div>
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
    --warning-color: #f59e0b;
    --success-color: #10b981;
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

.duel-play-page {
    padding: 2rem 0 4rem;
}

.duel-header {
    text-align: center;
    margin-bottom: 2rem;
}

.duel-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.duel-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 1rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-timed {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-accuracy {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-mixed {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.duel-container {
    max-width: 800px;
    margin: 0 auto;
}

.duel-progress-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.duel-progress {
    height: 0.5rem;
    background-color: var(--border-color);
    border-radius: 9999px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-bar {
    height: 100%;
    background-color: var(--primary-color);
    width: 0%;
    transition: width 0.3s ease;
}

.duel-stats {
    display: flex;
    justify-content: space-between;
}

.stat {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-color);
}

.quiz-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 2rem;
    min-height: 400px;
}

.question-container {
    animation: fadeIn 0.5s ease-out;
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
}

.question-timer {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--primary-color);
}

.question-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.answers-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.answer-option {
    position: relative;
}

.answer-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.answer-label {
    display: block;
    padding: 1.25rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.answer-option input[type="radio"]:checked + .answer-label {
    border-color: var(--primary-color);
    background-color: var(--primary-light);
}

.answer-option input[type="radio"]:focus + .answer-label {
    box-shadow: 0 0 0 2px var(--primary-light);
}

.answer-option.correct .answer-label {
    border-color: var(--success-color);
    background-color: rgba(16, 185, 129, 0.1);
}

.answer-option.incorrect .answer-label {
    border-color: var(--danger-color);
    background-color: rgba(239, 68, 68, 0.1);
}

.answer-feedback {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.feedback-correct {
    color: var(--success-color);
}

.feedback-incorrect {
    color: var(--danger-color);
}

.question-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
}

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

.btn-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-disabled:hover {
    transform: none;
    box-shadow: none;
}

.results-container {
    text-align: center;
    animation: fadeIn 0.5s ease-out;
}

.results-header {
    margin-bottom: 2rem;
}

.results-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.results-subtitle {
    font-size: 1.125rem;
    color: var(--text-muted);
}

.results-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.result-stat {
    background-color: var(--primary-light);
    padding: 1.5rem;
    border-radius: var(--border-radius);
}

.result-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.result-stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.results-message {
    font-size: 1.125rem;
    margin-bottom: 2rem;
}

.results-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

.loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid var(--primary-light);
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .duel-title {
        font-size: 1.5rem;
    }
    
    .quiz-container {
        padding: 1.5rem;
    }
    
    .question-text {
        font-size: 1.125rem;
    }
    
    .results-stats {
        grid-template-columns: 1fr;
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
document.addEventListener('DOMContentLoaded', function() {
    const duelId = <?= $duel_id ?>;
    const userId = <?= $user_id ?>;
    const questions = <?= json_encode($questions) ?>;
    const timeLimit = <?= $duel['time_limit'] ?>;
    const duelType = "<?= $duel['type'] ?>";

    let currentQuestionIndex = 0;
    let score = 0;
    let startTime = new Date();
    let timer = null;
    let questionStartTime = null;
    let answers = [];
    let isSubmitting = false;
    let hasCompleted = false;
    let pendingSubmissions = 0;
    let lastError = '';
    const maxRetryAttempts = 2;
    const retryDelay = 1000;

    const quizContainer = document.getElementById('quiz-container');
    const progressBar = document.getElementById('progress-bar');
    const questionCounter = document.getElementById('question-counter');
    const scoreCounter = document.getElementById('score-counter');
    const timeCounter = document.getElementById('time-counter');

    window.addEventListener('beforeunload', function(e) {
        if (!hasCompleted) {
            const confirmationMessage = 'Vous êtes en train de jouer un duel. Êtes-vous sûr de vouloir quitter ? Votre progression sera perdue.';
            (e || window.event).returnValue = confirmationMessage;
            return confirmationMessage;
        }
    });

    function initQuiz() {
        if (!questions || questions.length === 0) {
            console.error("play.php : Aucune question disponible pour le duel ID", duelId);
            quizContainer.innerHTML = `
                <div class="results-container">
                    <div class="results-header">
                        <h2 class="results-title">Erreur</h2>
                        <p class="results-subtitle">Aucune question disponible pour ce duel.</p>
                    </div>
                    <div class="results-actions">
                        <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                    </div>
                </div>
            `;
            return;
        }

        console.log("play.php : Questions chargées pour le duel ID", duelId, ":", questions);
        startTime = new Date();
        questionStartTime = new Date();
        updateTimer();
        timer = setInterval(updateTimer, 1000);
        document.getElementById('loading').style.display = 'none';
        showQuestion(currentQuestionIndex);
    }

    function updateTimer() {
        const currentTime = new Date();
        const elapsedSeconds = Math.floor((currentTime - startTime) / 1000);
        const minutes = Math.floor(elapsedSeconds / 60);
        const seconds = elapsedSeconds % 60;
        timeCounter.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        if (timeLimit > 0 && elapsedSeconds >= timeLimit) {
            console.log("play.php : Limite de temps atteinte pour le duel ID", duelId, ":", timeLimit, "secondes");
            clearInterval(timer);
            finishQuiz();
        }
    }

    function showQuestion(index) {
        const question = questions[index];
        if (!question || !question.texte || !question.options || question.options.length === 0) {
            console.error("play.php : Données de la question invalides à l'index", index, ":", question);
            quizContainer.innerHTML = `
                <div class="results-container">
                    <div class="results-header">
                        <h2 class="results-title">Erreur</h2>
                        <p class="results-subtitle">Les données de la question ${index + 1} sont invalides ou incomplètes.</p>
                    </div>
                    <div class="results-actions">
                        <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                    </div>
                </div>
            `;
            clearInterval(timer);
            return;
        }

        console.log("play.php : Affichage de la question", index + 1, "ID", question.id, "Réponses disponibles :", question.options);

        questionStartTime = new Date();
        progressBar.style.width = `${((index + 1) / questions.length) * 100}%`;
        questionCounter.textContent = `${index + 1}/${questions.length}`;

        const questionHTML = `
            <div class="question-container">
                <div class="question-header">
                    <span class="question-number">Question ${index + 1} sur ${questions.length}</span>
                    <span class="question-timer" id="question-timer">00:00</span>
                </div>
                <div class="question-text">${question.texte}</div>
                <div class="answers-list">
                    ${question.options.map((answer, answerIndex) => `
                        <div class="answer-option">
                            <input type="radio" id="answer-${answerIndex}" name="answer" value="${answer.id}">
                            <label class="answer-label" for="answer-${answerIndex}">${answer.texte}</label>
                        </div>
                    `).join('')}
                </div>
                <div class="question-navigation">
                    <button id="btn-skip" class="btn btn-outline">Passer</button>
                    <button id="btn-next" class="btn btn-primary btn-disabled" disabled>Suivant</button>
                </div>
            </div>
        `;

        quizContainer.innerHTML = questionHTML;

        document.querySelectorAll('input[name="answer"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('btn-next').classList.remove('btn-disabled');
                document.getElementById('btn-next').disabled = false;
            });
        });

        document.getElementById('btn-next').addEventListener('click', function() {
            if (!this.disabled && !isSubmitting) {
                submitAnswer();
            }
        });

        document.getElementById('btn-skip').addEventListener('click', function() {
            if (!isSubmitting) {
                console.log("play.php : Question", index + 1, "passée pour le duel ID", duelId);
                nextQuestion();
            }
        });

        const questionTimer = document.getElementById('question-timer');
        let questionSeconds = 0;
        const questionTimerInterval = setInterval(function() {
            questionSeconds++;
            const qMinutes = Math.floor(questionSeconds / 60);
            const qSeconds = questionSeconds % 60;
            questionTimer.textContent = `${qMinutes.toString().padStart(2, '0')}:${qSeconds.toString().padStart(2, '0')}`;
        }, 1000);

        quizContainer.dataset.timerInterval = questionTimerInterval;
    }

    async function submitAnswer() {
        if (isSubmitting) {
            console.log("play.php : Soumission déjà en cours, ignorée pour la question", currentQuestionIndex + 1);
            return;
        }
        isSubmitting = true;

        const selectedAnswer = document.querySelector('input[name="answer"]:checked');
        if (!selectedAnswer) {
            console.log("play.php : Aucune réponse sélectionnée pour la question", currentQuestionIndex + 1);
            isSubmitting = false;
            return;
        }

        const answerId = parseInt(selectedAnswer.value);
        const questionId = questions[currentQuestionIndex].id;
        const endTime = new Date();
        const responseTime = endTime - questionStartTime;

        // Vérifier si answerId est valide
        const validAnswerIds = questions[currentQuestionIndex].options.map(a => a.id);
        if (!validAnswerIds.includes(answerId)) {
            console.error("play.php : answerId", answerId, "non valide pour la question ID", questionId, "Réponses valides :", validAnswerIds);
            isSubmitting = false;
            showError("Réponse invalide sélectionnée. Veuillez réessayer.");
            return;
        }

        if (!questionId || !answerId || responseTime < 0) {
            console.error("play.php : Données invalides pour la soumission - questionId:", questionId, "answerId:", answerId, "responseTime:", responseTime);
            isSubmitting = false;
            showError("Données invalides pour la soumission de la réponse. Veuillez réessayer.");
            return;
        }

        const isCorrect = questions[currentQuestionIndex].options.find(a => a.id === answerId).est_correcte === "1";
        if (isCorrect) {
            score++;
            scoreCounter.textContent = score;
        }

        answers.push({
            question_id: questionId,
            answer_id: answerId,
            is_correct: isCorrect,
            response_time: responseTime
        });

        const submissionData = {
            duel_id: duelId,
            user_id: userId,
            question_id: questionId,
            answer_id: answerId,
            response_time: responseTime
        };
        console.log(`play.php : Données envoyées à submit_duel_answer.php pour la question ${currentQuestionIndex + 1} du duel ID ${duelId} :`, JSON.stringify(submissionData));

        let attempts = 0;
        let success = false;

        while (attempts < maxRetryAttempts && !success) {
            try {
                const response = await fetch('/quizmaster/api/submit_duel_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(submissionData)
                });

                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log(`play.php : Réponse de submit_duel_answer.php pour la question ${currentQuestionIndex + 1} du duel ID ${duelId} :`, data);

                if (data.success) {
                    localStorage.removeItem(`pendingAnswer_${duelId}_${questionId}`);
                    success = true;
                } else {
                    lastError = data.error || `Erreur serveur inconnue`;
                    console.error(`play.php : Erreur de soumission pour la question ${currentQuestionIndex + 1} :`, lastError);
                    if (data.error !== 'Réponse déjà soumise') {
                        localStorage.setItem(`pendingAnswer_${duelId}_${questionId}`, JSON.stringify(submissionData));
                    }
                }
            } catch (error) {
                console.error(`play.php : Erreur réseau lors de la soumission pour la question ${currentQuestionIndex + 1} :`, error.message);
                localStorage.setItem(`pendingAnswer_${duelId}_${questionId}`, JSON.stringify(submissionData));
            }

            attempts++;
            if (!success && attempts < maxRetryAttempts) {
                console.log(`play.php : Nouvelle tentative de soumission (${attempts + 1}/${maxRetryAttempts}) après ${retryDelay}ms pour la question ${currentQuestionIndex + 1}`);
                await new Promise(resolve => setTimeout(resolve, retryDelay));
            }
        }

        if (!success) {
            console.error(`play.php : Échec de l'envoi des réponses après ${maxRetryAttempts} tentatives pour la question ${currentQuestionIndex + 1}`);
            showError(`Échec de l'envoi de la réponse après ${maxRetryAttempts} tentatives : ${lastError}`);
            isSubmitting = false;
            return;
        }

        proceedAfterSubmission();
    }

    function proceedAfterSubmission() {
        const selectedAnswer = document.querySelector('input[name="answer"]:checked');
        const answerId = parseInt(selectedAnswer.value);
        const isCorrect = questions[currentQuestionIndex].options.find(a => a.id === answerId).est_correcte === "1";

        const answerOption = selectedAnswer.parentElement;
        if (isCorrect) {
            answerOption.classList.add('correct');
            const feedback = document.createElement('div');
            feedback.className = 'answer-feedback feedback-correct';
            feedback.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg> Correct!';
            answerOption.appendChild(feedback);
        } else {
            answerOption.classList.add('incorrect');
            const feedback = document.createElement('div');
            feedback.className = 'answer-feedback feedback-incorrect';
            feedback.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg> Incorrect';
            answerOption.appendChild(feedback);

            const correctAnswerId = questions[currentQuestionIndex].options.find(a => a.est_correcte === "1").id;
            const correctOption = document.querySelector(`input[value="${correctAnswerId}"]`).parentElement;
            correctOption.classList.add('correct');
            const correctFeedback = document.createElement('div');
            correctFeedback.className = 'answer-feedback feedback-correct';
            correctFeedback.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg> Réponse correcte';
            correctOption.appendChild(correctFeedback);
        }

        document.querySelectorAll('input[name="answer"]').forEach(radio => {
            radio.disabled = true;
        });
        document.getElementById('btn-next').disabled = true;
        document.getElementById('btn-skip').disabled = true;

        const nextButton = document.getElementById('btn-next');
        nextButton.textContent = currentQuestionIndex < questions.length - 1 ? 'Question suivante' : 'Terminer';

        if (quizContainer.dataset.timerInterval) {
            clearInterval(quizContainer.dataset.timerInterval);
        }

        setTimeout(function() {
            nextQuestion();
        }, 2000);
    }

    function nextQuestion() {
        if (quizContainer.dataset.timerInterval) {
            clearInterval(quizContainer.dataset.timerInterval);
        }

        currentQuestionIndex++;
        isSubmitting = false;

        if (currentQuestionIndex < questions.length) {
            console.log("play.php : Passage à la question", currentQuestionIndex + 1, "pour le duel ID", duelId);
            showQuestion(currentQuestionIndex);
        } else {
            finishQuiz();
        }
    }

    function finishQuiz() {
        clearInterval(timer);
        hasCompleted = true;
        const endTime = new Date();
        const totalTime = Math.floor((endTime - startTime) / 1000);
        console.log("play.php : Duel ID", duelId, "terminé par l'utilisateur", userId, "en", totalTime, "secondes");

        let retryAttempts = 0;

        async function retryPendingSubmissions() {
            let pendingKeys = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith(`pendingAnswer_${duelId}_`)) {
                    pendingKeys.push(key);
                }
            }

            if (pendingKeys.length === 0) {
                console.log("play.php : Aucune soumission en attente pour le duel ID", duelId);
                await completeDuel(totalTime);
                return;
            }

            if (retryAttempts >= maxRetryAttempts) {
                console.error("play.php : Échec des nouvelles tentatives de soumission après", maxRetryAttempts, "essais pour le duel ID", duelId, ":", pendingKeys);
                showSubmissionError(pendingKeys);
                return;
            }

            retryAttempts++;
            console.log("play.php : Nouvelle tentative de soumission des réponses en attente (essai", retryAttempts, "sur", maxRetryAttempts, ") pour le duel ID", duelId, ":", pendingKeys);

            for (const key of pendingKeys) {
                const answerData = JSON.parse(localStorage.getItem(key));
                pendingSubmissions++;
                console.log("play.php : Nouvelle tentative de soumission pour", key, ":", answerData);
                try {
                    const response = await fetch('/quizmaster/api/submit_duel_answer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(answerData)
                    });
                    if (!response.ok) {
                        throw new Error(`Erreur réseau - Statut: ${response.status}`);
                    }
                    const data = await response.json();
                    console.log("play.php : Réponse de submit_duel_answer.php pour la nouvelle tentative", key, ":", data);
                    if (data.success) {
                        localStorage.removeItem(key);
                        console.log("play.php : Nouvelle tentative de soumission réussie pour", key);
                    } else {
                        lastError = data.error || 'Erreur inconnue';
                        throw new Error(lastError);
                    }
                } catch (error) {
                    console.error("play.php : Échec de la nouvelle tentative de soumission pour", key, ":", error.message);
                } finally {
                    pendingSubmissions--;
                }
            }

            await checkPendingSubmissions(totalTime);
        }

        async function checkPendingSubmissions(totalTime) {
            if (pendingSubmissions > 0) {
                console.log("play.php : En attente de", pendingSubmissions, "soumissions en cours pour le duel ID", duelId);
                return;
            }

            let remainingPending = 0;
            for (let i = 0; i < localStorage.length; i++) {
                if (localStorage.key(i).startsWith(`pendingAnswer_${duelId}_`)) {
                    remainingPending++;
                }
            }

            if (remainingPending > 0) {
                console.log("play.php :", remainingPending, "soumissions toujours en attente pour le duel ID", duelId, ", nouvelle tentative...");
                await retryPendingSubmissions();
            } else {
                console.log("play.php : Toutes les soumissions ont été traitées pour le duel ID", duelId);
                await completeDuel(totalTime);
            }
        }

        retryPendingSubmissions();
    }

    function showSubmissionError(pendingKeys) {
        const errorHTML = `
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">Erreur d'envoi des réponses</h2>
                    <p class="results-subtitle">Échec de l'envoi de ${pendingKeys.length} réponse(s) après plusieurs tentatives. Raison : ${lastError}</p>
                </div>
                <div class="results-message">
                    Veuillez vérifier votre connexion et réessayer. Si le problème persiste, contactez l'administrateur.
                </div>
                <div class="results-actions">
                    <button id="retry-submission" class="btn btn-primary">Réessayer</button>
                    <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                </div>
            </div>
        `;

        quizContainer.innerHTML = errorHTML;

        document.getElementById('retry-submission').addEventListener('click', function() {
            console.log("play.php : Utilisateur a cliqué sur 'Réessayer' pour le duel ID", duelId);
            retryPendingSubmissions();
        });
    }

    async function completeDuel(totalTime) {
        console.log("play.php : Appel de complete_duel.php pour le duel ID", duelId, "avec totalTime", totalTime);
        try {
            const response = await fetch('/quizmaster/api/complete_duel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    duel_id: duelId,
                    user_id: userId,
                    completion_time: totalTime
                })
            });
            if (!response.ok) {
                throw new Error(`Erreur réseau lors de la complétion du duel: ${response.status} ${response.statusText}`);
            }
            const data = await response.json();
            console.log("play.php : Réponse de complete_duel.php pour le duel ID", duelId, ":", data);
            if (!data.success) {
                throw new Error(data.error || "Échec de la complétion du duel côté serveur");
            }
            if (data.duel_completed) {
                console.log("play.php : Duel ID", duelId, "terminé pour les deux joueurs, affichage des résultats");
                showResults(data.winner_id === userId, data.winner_id === null);
            } else {
                console.log("play.php : Duel ID", duelId, "en attente de l'adversaire, passage à l'écran d'attente");
                showWaiting();
            }
        } catch (error) {
            console.error("play.php : Erreur lors de la complétion du duel ID", duelId, ":", error.message);
            showError(`Une erreur est survenue lors de la finalisation du duel : ${error.message}`);
        }
    }

    function showWaiting() {
        const accuracy = Math.round((score / questions.length) * 100);
        const waitingHTML = `
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">Duel terminé!</h2>
                    <p class="results-subtitle">En attente que votre adversaire termine...</p>
                </div>
                <div class="results-stats">
                    <div class="result-stat">
                        <div class="result-stat-value">${score}/${questions.length}</div>
                        <div class="result-stat-label">Score</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${accuracy}%</div>
                        <div class="result-stat-label">Précision</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${timeCounter.textContent}</div>
                        <div class="result-stat-label">Temps</div>
                    </div>
                </div>
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>En attente de votre adversaire...</p>
                </div>
                <div class="results-actions">
                    <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                </div>
            </div>
        `;

        quizContainer.innerHTML = waitingHTML;

        const maxPollTime = 5 * 60 * 1000;
        const maxPollAttempts = 60;
        let pollAttempts = 0;
        const pollStartTime = new Date();
        const pollInterval = setInterval(async function() {
            pollAttempts++;
            const elapsedPollTime = new Date() - pollStartTime;

            if (elapsedPollTime >= maxPollTime || pollAttempts >= maxPollAttempts) {
                console.log("play.php : Timeout ou limite d'essais atteinte pour le polling du duel ID", duelId, "après", pollAttempts, "tentatives");
                clearInterval(pollInterval);
                await forceDuelCompletion();
                return;
            }

            console.log("play.php : Vérification du statut du duel ID", duelId, "(tentative", pollAttempts, "sur", maxPollAttempts, ")");
            try {
                const response = await fetch(`/quizmaster/api/check_duel_status.php?duel_id=${duelId}`);
                if (!response.ok) {
                    throw new Error(`Erreur réseau lors de la vérification du statut: ${response.statusText}`);
                }
                const data = await response.json();
                console.log("play.php : Statut du duel ID", duelId, ":", data);
                if (data.error) {
                    throw new Error(`Erreur du serveur: ${data.error}`);
                }
                if (data.status === 'completed') {
                    console.log("play.php : Duel ID", duelId, "terminé, affichage des résultats");
                    clearInterval(pollInterval);
                    showResults(data.winner_id === userId, data.winner_id === null);
                } else if (data.status === 'abandoned') {
                    console.log("play.php : Duel ID", duelId, "abandonné par l'adversaire");
                    clearInterval(pollInterval);
                    showAbandonedResult();
                } else {
                    console.log("play.php : Duel ID", duelId, "toujours en attente, statut =", data.status);
                }
            } catch (error) {
                console.error("play.php : Erreur lors de la vérification du statut du duel ID", duelId, ":", error.message);
                if (pollAttempts >= maxPollAttempts) {
                    clearInterval(pollInterval);
                    showError("Une erreur persistante est survenue lors de la vérification du statut du duel.");
                }
            }
        }, 5000);
    }

    async function forceDuelCompletion() {
        console.log("play.php : Forçage de la fin du duel ID", duelId);
        try {
            const response = await fetch('/quizmaster/api/force_complete_duel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    duel_id: duelId,
                    user_id: userId
                })
            });
            if (!response.ok) {
                throw new Error(`Erreur réseau lors du forçage de la complétion: ${response.statusText}`);
            }
            const data = await response.json();
            console.log("play.php : Résultat du forçage de la complétion pour le duel ID", duelId, ":", data);
            if (data.status === 'completed') {
                showResults(data.winner_id === userId, data.winner_id === null);
            } else {
                throw new Error("Échec du forçage de la complétion du duel");
            }
        } catch (error) {
            console.error("play.php : Erreur lors du forçage de la complétion du duel ID", duelId, ":", error.message);
            quizContainer.innerHTML = `
                <div class="results-container">
                    <div class="results-header">
                        <h2 class="results-title">Erreur</h2>
                        <p class="results-subtitle">Une erreur est survenue lors de la vérification des résultats.</p>
                    </div>
                    <div class="results-actions">
                        <a href="/quizmaster/duels/results.php?id=${duelId}" class="btn btn-primary">Voir les détails</a>
                        <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                    </div>
                </div>
            `;
        }
    }

    function showAbandonedResult() {
        const accuracy = Math.round((score / questions.length) * 100);
        console.log("play.php : Affichage du résultat d'abandon pour le duel ID", duelId);
        quizContainer.innerHTML = `
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">Adversaire absent</h2>
                    <p class="results-subtitle">Votre adversaire a abandonné ou n'a pas terminé le duel.</p>
                </div>
                <div class="results-stats">
                    <div class="result-stat">
                        <div class="result-stat-value">${score}/${questions.length}</div>
                        <div class="result-stat-label">Score</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${accuracy}%</div>
                        <div class="result-stat-label">Précision</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${timeCounter.textContent}</div>
                        <div class="result-stat-label">Temps</div>
                    </div>
                </div>
                <div class="results-message">
                    Vous êtes déclaré vainqueur par forfait.
                </div>
                <div class="results-actions">
                    <a href="/quizmaster/duels/results.php?id=${duelId}" class="btn btn-primary">Voir les détails</a>
                    <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                </div>
            </div>
        `;
    }

    function showResults(isWinner, isDraw) {
        const accuracy = Math.round((score / questions.length) * 100);
        let resultMessage = '';
        if (isDraw) {
            resultMessage = 'Match nul! Vous avez tous les deux bien joué.';
        } else if (isWinner) {
            resultMessage = 'Félicitations! Vous avez gagné ce duel!';
        } else {
            resultMessage = 'Dommage! Vous avez perdu ce duel.';
        }

        console.log("play.php : Affichage des résultats pour le duel ID", duelId, "- Gagnant:", isWinner, "- Match nul:", isDraw);
        quizContainer.innerHTML = `
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">Duel terminé!</h2>
                    <p class="results-subtitle">${resultMessage}</p>
                </div>
                <div class="results-stats">
                    <div class="result-stat">
                        <div class="result-stat-value">${score}/${questions.length}</div>
                        <div class="result-stat-label">Score</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${accuracy}%</div>
                        <div class="result-stat-label">Précision</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value">${timeCounter.textContent}</div>
                        <div class="result-stat-label">Temps</div>
                    </div>
                </div>
                <div class="results-message">
                    ${isWinner ? 
                        'Bravo pour votre victoire! Continuez comme ça!' : 
                        isDraw ? 
                            'Match nul! Vous êtes de force égale.' : 
                            'Ne vous découragez pas, entraînez-vous et réessayez!'}
                </div>
                <div class="results-actions">
                    <a href="/quizmaster/duels/results.php?id=${duelId}" class="btn btn-primary">Voir les détails</a>
                    <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                </div>
            </div>
        `;
    }

    function showError(message = "Une erreur est survenue lors de l'enregistrement de vos résultats.") {
        console.error("play.php : Affichage de l'écran d'erreur pour le duel ID", duelId, ":", message);
        quizContainer.innerHTML = `
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">Erreur</h2>
                    <p class="results-subtitle">${message}</p>
                </div>
                <div class="results-message">
                    Veuillez réessayer ou contacter l'administrateur si le problème persiste.
                </div>
                <div class="results-actions">
                    <a href="/quizmaster/duels/index.php" class="btn btn-outline">Retour aux duels</a>
                </div>
            </div>
        `;
    }

    function getDuelTypeLabel(type) {
        switch(type) {
            case 'timed': return 'Contre la montre';
            case 'accuracy': return 'Précision';
            case 'mixed': return 'Mixte';
            default: return type;
        }
    }

    // Soumettre les réponses en attente au chargement
    window.addEventListener('load', async () => {
        let pendingKeys = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith(`pendingAnswer_${duelId}_`)) {
                pendingKeys.push(key);
            }
        }

        for (const key of pendingKeys) {
            const answerData = JSON.parse(localStorage.getItem(key));
            console.log("play.php : Tentative de soumission des réponses en attente pour", key, ":", answerData);
            try {
                const response = await fetch('/quizmaster/api/submit_duel_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(answerData)
                });
                const data = await response.json();
                console.log("play.php : Résultat de la soumission en attente pour", key, ":", data);
                if (data.success) {
                    localStorage.removeItem(key);
                } else {
                    console.error("play.php : Échec de la soumission en attente pour", key, ":", data.error);
                }
            } catch (error) {
                console.error("play.php : Erreur réseau lors de la soumission en attente pour", key, ":", error.message);
            }
        }
    });

    setTimeout(function() {
        console.log("play.php : Démarrage du duel ID", duelId);
        initQuiz();
    }, 1000);
});
</script>

<?php
function getDuelTypeLabel($type) {
    switch($type) {
        case 'timed': return 'Contre la montre';
        case 'accuracy': return 'Précision';
        case 'mixed': return 'Mixte';
        default: return $type;
    }
}

include '../includes/footer.php';
?>