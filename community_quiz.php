<?php
$titre_page = "Quiz Communautaire";
require_once 'includes/header.php';

// Vérifier si l'ID du quiz est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: categorie.php');
    exit;
}

$quiz_id = (int)$_GET['id'];

// Récupérer les informations du quiz
$database = new Database();
$db = $database->connect();

$query = "SELECT uq.*, c.nom as categorie_nom, c.icone as categorie_icone, c.couleur as categorie_couleur, 
                 u.nom as createur_nom, u.est_contributeur, d.nom as difficulte_nom
          FROM user_quizzes uq 
          LEFT JOIN categories c ON uq.categorie_id = c.id 
          LEFT JOIN utilisateurs u ON uq.utilisateur_id = u.id
          LEFT JOIN difficultes d ON uq.difficulte_id = d.id
          WHERE uq.id = :id AND uq.status = 'approved'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $quiz_id);
$stmt->execute();
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header('Location: categorie.php');
    exit;
}

// Récupérer les questions du quiz
$query = "SELECT * FROM user_quiz_questions WHERE user_quiz_id = :quiz_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':quiz_id', $quiz_id);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque question, récupérer les options
$questions_with_options = [];
foreach ($questions as $question) {
    $query = "SELECT * FROM user_quiz_options WHERE user_quiz_question_id = :question_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':question_id', $question['id']);
    $stmt->execute();
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $question['options'] = $options;
    $questions_with_options[] = $question;
}
?>

<main class="quiz-page">
    <div class="container">
        <div class="quiz-container" data-quiz-id="<?= $quiz_id ?>" data-quiz-type="community">
            <!-- En-tête du quiz -->
            <div class="quiz-header" style="--category-color: <?= $quiz['categorie_couleur'] ?>">
                <div class="quiz-info">
                    <div class="quiz-category">
                        <div class="category-icon">
                            <i class="fas <?= $quiz['categorie_icone'] ?>"></i>
                        </div>
                        <div class="category-name"><?= htmlspecialchars($quiz['categorie_nom']) ?></div>
                    </div>
                    <h1 class="quiz-title"><?= htmlspecialchars($quiz['titre']) ?></h1>
                    <p class="quiz-description"><?= htmlspecialchars($quiz['description']) ?></p>
                    <div class="quiz-meta">
                        <div class="quiz-meta-item">
                            <i class="fas fa-user"></i>
                            <span>Créé par: <?= htmlspecialchars($quiz['createur_nom']) ?>
                            <?php if ($quiz['est_contributeur']): ?>
                                <i class="fas fa-check-circle certified-icon" title="Contributeur certifié"></i>
                            <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($quiz['difficulte_nom']): ?>
                        <div class="quiz-meta-item">
                            <i class="fas fa-signal"></i>
                            <span>Difficulté: <?= htmlspecialchars($quiz['difficulte_nom']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="quiz-meta-item">
                            <i class="fas fa-question-circle"></i>
                            <span><?= count($questions_with_options) ?> questions</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Écran de démarrage -->
            <div class="quiz-start-screen">
                <div class="quiz-start-content">
                    <h2>Prêt à commencer?</h2>
                    <p>Ce quiz contient <?= count($questions_with_options) ?> questions. Vous pouvez reprendre votre progression à tout moment.</p>
                    
                    <div class="quiz-actions">
                        <button id="start-quiz" class="btn btn-primary">
                            <i class="fas fa-play"></i> Commencer le quiz
                        </button>
                        <button id="resume-quiz" class="btn btn-outline" style="display: none;">
                            <i class="fas fa-redo"></i> Reprendre
                        </button>
                        <button id="restart-quiz" class="btn btn-outline" style="display: none;">
                            <i class="fas fa-sync"></i> Recommencer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Écran de quiz -->
            <div class="quiz-content" style="display: none;">
                <div class="quiz-progress-bar">
                    <div class="progress-bar-inner"></div>
                </div>
                
                <div class="quiz-question-counter">
                    Question <span id="current-question">1</span> sur <span id="total-questions"><?= count($questions_with_options) ?></span>
                </div>
                
                <div class="quiz-timer">
                    <i class="fas fa-clock"></i> <span id="timer">00:00</span>
                </div>
                
                <div class="quiz-question">
                    <h2 id="question-text"></h2>
                </div>
                
                <div class="quiz-options">
                    <!-- Les options seront générées dynamiquement par JavaScript -->
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

            <!-- Écran de résultats -->
            <div class="quiz-results" style="display: none;">
                <div class="results-header">
                    <h2>Résultats du Quiz</h2>
                    <div class="results-score">
                        <div class="score-circle">
                            <div class="score-number">
                                <span id="correct-answers">0</span>/<span id="total-questions-results"><?= count($questions_with_options) ?></span>
                            </div>
                        </div>
                        <div class="score-text">
                            <div class="score-percentage"><span id="score-percentage">0</span>%</div>
                            <div class="score-label">Réponses correctes</div>
                        </div>
                    </div>
                </div>
                
                <div class="results-details">
                    <div class="results-time">
                        <i class="fas fa-clock"></i> Temps: <span id="total-time">00:00</span>
                    </div>
                </div>
                
                <div class="results-actions">
                    <button id="show-answers" class="btn btn-outline">
                        <i class="fas fa-search"></i> Voir les réponses
                    </button>
                    <button id="retry-quiz" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Réessayer
                    </button>
                    <a href="categorie.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Retour aux catégories
                    </a>
                </div>
            </div>

            <!-- Écran de révision des réponses -->
            <div class="quiz-review" style="display: none;">
                <div class="review-header">
                    <h2>Révision des réponses</h2>
                </div>
                
                <div class="review-questions">
                    <!-- Les questions et réponses seront générées dynamiquement par JavaScript -->
                </div>
                
                <div class="review-actions">
                    <button id="back-to-results" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour aux résultats
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Données du quiz
const quizData = {
    id: <?= $quiz_id ?>,
    questions: <?= json_encode($questions_with_options) ?>
};

// Variables globales
let currentQuestionIndex = 0;
let userAnswers = {};
let startTime = 0;
let timerInterval;
let elapsedTime = 0;

// Éléments DOM
const startScreen = document.querySelector('.quiz-start-screen');
const quizContent = document.querySelector('.quiz-content');
const resultsScreen = document.querySelector('.quiz-results');
const reviewScreen = document.querySelector('.quiz-review');
const progressBar = document.querySelector('.progress-bar-inner');
const currentQuestionElement = document.getElementById('current-question');
const totalQuestionsElement = document.getElementById('total-questions');
const questionText = document.getElementById('question-text');
const optionsContainer = document.querySelector('.quiz-options');
const prevButton = document.getElementById('prev-question');
const nextButton = document.getElementById('next-question');
const finishButton = document.getElementById('finish-quiz');
const timerElement = document.getElementById('timer');
const startButton = document.getElementById('start-quiz');
const resumeButton = document.getElementById('resume-quiz');
const restartButton = document.getElementById('restart-quiz');
const retryButton = document.getElementById('retry-quiz');
const showAnswersButton = document.getElementById('show-answers');
const backToResultsButton = document.getElementById('back-to-results');
const correctAnswersElement = document.getElementById('correct-answers');
const totalQuestionsResultsElement = document.getElementById('total-questions-results');
const scorePercentageElement = document.getElementById('score-percentage');
const totalTimeElement = document.getElementById('total-time');

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier s'il y a une progression sauvegardée
    checkSavedProgress();
    
    // Événements
    startButton.addEventListener('click', startQuiz);
    resumeButton.addEventListener('click', resumeQuiz);
    restartButton.addEventListener('click', restartQuiz);
    prevButton.addEventListener('click', goToPreviousQuestion);
    nextButton.addEventListener('click', goToNextQuestion);
    finishButton.addEventListener('click', finishQuiz);
    retryButton.addEventListener('click', restartQuiz);
    showAnswersButton.addEventListener('click', showAnswers);
    backToResultsButton.addEventListener('click', backToResults);
});

// Vérifier s'il y a une progression sauvegardée
function checkSavedProgress() {
    const quizId = document.querySelector('.quiz-container').dataset.quizId;
    
    fetch(`get_community_quiz_progress.php?quiz_id=${quizId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.progress) {
                // Afficher le bouton de reprise
                resumeButton.style.display = 'inline-flex';
                restartButton.style.display = 'inline-flex';
                
                // Stocker la progression
                currentQuestionIndex = data.progress.current_question_index;
                userAnswers = data.progress.answers || {};
                elapsedTime = data.progress.time_elapsed || 0;
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération de la progression:', error);
        });
}

// Démarrer le quiz
function startQuiz() {
    startScreen.style.display = 'none';
    quizContent.style.display = 'block';
    
    // Réinitialiser les variables
    currentQuestionIndex = 0;
    userAnswers = {};
    elapsedTime = 0;
    
    // Afficher la première question
    displayQuestion(currentQuestionIndex);
    
    // Démarrer le chronomètre
    startTimer();
}

// Reprendre le quiz
function resumeQuiz() {
    startScreen.style.display = 'none';
    quizContent.style.display = 'block';
    
    // Afficher la question sauvegardée
    displayQuestion(currentQuestionIndex);
    
    // Démarrer le chronomètre avec le temps écoulé
    startTimer(elapsedTime);
}

// Redémarrer le quiz
function restartQuiz() {
    // Effacer la progression sauvegardée
    const quizId = document.querySelector('.quiz-container').dataset.quizId;
    
    fetch('clear_community_quiz_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quiz_id: quizId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Masquer les écrans de résultats et de révision
            resultsScreen.style.display = 'none';
            reviewScreen.style.display = 'none';
            
            // Démarrer un nouveau quiz
            startQuiz();
        }
    })
    .catch(error => {
        console.error('Erreur lors de la suppression de la progression:', error);
    });
}

// Afficher une question
function displayQuestion(index) {
    const question = quizData.questions[index];
    
    // Mettre à jour le texte de la question
    questionText.textContent = question.question;
    
    // Mettre à jour le compteur de questions
    currentQuestionElement.textContent = index + 1;
    
    // Mettre à jour la barre de progression
    const progress = ((index + 1) / quizData.questions.length) * 100;
    progressBar.style.width = `${progress}%`;
    
    // Générer les options
    optionsContainer.innerHTML = '';
    question.options.forEach((option, optionIndex) => {
        const optionElement = document.createElement('div');
        optionElement.className = 'quiz-option';
        
        // Vérifier si cette option a été sélectionnée
        if (userAnswers[question.id] === option.id) {
            optionElement.classList.add('selected');
        }
        
        optionElement.innerHTML = `
            <div class="option-letter">${String.fromCharCode(65 + optionIndex)}</div>
            <div class="option-text">${option.texte}</div>
        `;
        
        optionElement.addEventListener('click', () => selectOption(question.id, option.id, optionElement));
        optionsContainer.appendChild(optionElement);
    });
    
    // Mettre à jour les boutons de navigation
    prevButton.disabled = index === 0;
    nextButton.disabled = !userAnswers[question.id];
    
    // Afficher ou masquer le bouton Terminer
    if (index === quizData.questions.length - 1) {
        nextButton.style.display = 'none';
        finishButton.style.display = 'inline-flex';
        finishButton.disabled = !userAnswers[question.id];
    } else {
        nextButton.style.display = 'inline-flex';
        finishButton.style.display = 'none';
    }
}

// Sélectionner une option
function selectOption(questionId, optionId, optionElement) {
    // Désélectionner toutes les options
    const options = optionsContainer.querySelectorAll('.quiz-option');
    options.forEach(option => option.classList.remove('selected'));
    
    // Sélectionner l'option cliquée
    optionElement.classList.add('selected');
    
    // Enregistrer la réponse
    userAnswers[questionId] = optionId;
    
    // Activer le bouton suivant
    nextButton.disabled = false;
    
    // Activer le bouton terminer si c'est la dernière question
    if (currentQuestionIndex === quizData.questions.length - 1) {
        finishButton.disabled = false;
    }
    
    // Sauvegarder la progression
    saveProgress();
}

// Aller à la question précédente
function goToPreviousQuestion() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        displayQuestion(currentQuestionIndex);
    }
}

// Aller à la question suivante
function goToNextQuestion() {
    if (currentQuestionIndex < quizData.questions.length - 1) {
        currentQuestionIndex++;
        displayQuestion(currentQuestionIndex);
    }
}

// Terminer le quiz
function finishQuiz() {
    // Arrêter le chronomètre
    clearInterval(timerInterval);
    
    // Calculer le score
    let correctAnswers = 0;
    
    quizData.questions.forEach(question => {
        const userAnswer = userAnswers[question.id];
        
        if (userAnswer) {
            const correctOption = question.options.find(option => option.est_correcte === 1);
            
            if (correctOption && userAnswer === correctOption.id) {
                correctAnswers++;
            }
        }
    });
    
    // Mettre à jour les éléments de résultat
    correctAnswersElement.textContent = correctAnswers;
    const percentage = Math.round((correctAnswers / quizData.questions.length) * 100);
    scorePercentageElement.textContent = percentage;
    totalTimeElement.textContent = formatTime(elapsedTime);
    
    // Afficher l'écran de résultats
    quizContent.style.display = 'none';
    resultsScreen.style.display = 'block';
    
    // Effacer la progression sauvegardée
    const quizId = document.querySelector('.quiz-container').dataset.quizId;
    
    fetch('clear_community_quiz_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quiz_id: quizId }),
    })
    .catch(error => {
        console.error('Erreur lors de la suppression de la progression:', error);
    });
}

// Afficher les réponses
function showAnswers() {
    resultsScreen.style.display = 'none';
    reviewScreen.style.display = 'block';
    
    // Générer le contenu de révision
    const reviewQuestionsContainer = document.querySelector('.review-questions');
    reviewQuestionsContainer.innerHTML = '';
    
    quizData.questions.forEach((question, index) => {
        const userAnswer = userAnswers[question.id];
        const correctOption = question.options.find(option => option.est_correcte === 1);
        const isCorrect = userAnswer && correctOption && userAnswer === correctOption.id;
        
        const questionElement = document.createElement('div');
        questionElement.className = `review-question ${isCorrect ? 'correct' : 'incorrect'}`;
        
        let optionsHTML = '';
        question.options.forEach((option, optionIndex) => {
            const isUserAnswer = userAnswer === option.id;
            const isCorrectAnswer = option.est_correcte === 1;
            
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
                    <div class="option-letter">${String.fromCharCode(65 + optionIndex)}</div>
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

// Retourner aux résultats
function backToResults() {
    reviewScreen.style.display = 'none';
    resultsScreen.style.display = 'block';
}

// Démarrer le chronomètre
function startTimer(startSeconds = 0) {
    elapsedTime = startSeconds;
    startTime = Date.now() - (elapsedTime * 1000);
    
    updateTimer();
    
    timerInterval = setInterval(() => {
        updateTimer();
        
        // Sauvegarder la progression toutes les 10 secondes
        if (elapsedTime % 10 === 0) {
            saveProgress();
        }
    }, 1000);
}

// Mettre à jour le chronomètre
function updateTimer() {
    elapsedTime = Math.floor((Date.now() - startTime) / 1000);
    timerElement.textContent = formatTime(elapsedTime);
}

// Formater le temps
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Sauvegarder la progression
function saveProgress() {
    const quizId = document.querySelector('.quiz-container').dataset.quizId;
    
    const progressData = {
        quiz_id: quizId,
        current_question_index: currentQuestionIndex,
        answers: userAnswers,
        time_elapsed: elapsedTime
    };
    
    fetch('save_community_quiz_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(progressData),
    })
    .catch(error => {
        console.error('Erreur lors de la sauvegarde de la progression:', error);
    });
}
</script>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
    --secondary-hover: #059669;
    --danger-color: #ef4444;
    --danger-hover: #dc2626;
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

.quiz-page {
    padding: 2rem 0 4rem;
}

/* Quiz Container */
.quiz-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 2rem;
}

/* Quiz Header */
.quiz-header {
    padding: 2rem;
    background-color: white;
    position: relative;
    overflow: hidden;
}

.quiz-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background-color: var(--category-color, var(--primary-color));
}

.quiz-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quiz-category {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: var(--category-color, var(--primary-color));
    color: white;
    border-radius: 50%;
    font-size: 1.25rem;
}

.category-name {
    font-weight: 600;
    color: var(--category-color, var(--primary-color));
}

.quiz-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.quiz-description {
    font-size: 1rem;
    color: var(--text-muted);
    margin: 0;
}

.quiz-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 0.5rem;
}

.quiz-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.quiz-meta-item i {
    color: var(--category-color, var(--primary-color));
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

/* Quiz Start Screen */
.quiz-start-screen {
    padding: 3rem 2rem;
    text-align: center;
}

.quiz-start-content {
    max-width: 600px;
    margin: 0 auto;
}

.quiz-start-content h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.quiz-start-content p {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
}

.quiz-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
}

/* Quiz Content */
.quiz-content {
    padding: 2rem;
}

.quiz-progress-bar {
    height: 8px;
    background-color: var(--border-color);
    border-radius: 4px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.progress-bar-inner {
    height: 100%;
    background-color: var(--category-color, var(--primary-color));
    width: 0;
    transition: width 0.3s ease;
}

.quiz-question-counter {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.quiz-timer {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.quiz-timer i {
    color: var(--category-color, var(--primary-color));
}

.quiz-question {
    margin-bottom: 2rem;
}

.quiz-question h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.quiz-option {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.quiz-option:hover {
    border-color: var(--category-color, var(--primary-color));
    background-color: var(--primary-light);
}

.quiz-option.selected {
    border-color: var(--category-color, var(--primary-color));
    background-color: var(--primary-light);
}

.option-letter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: var(--border-color);
    color: var(--text-color);
    border-radius: 50%;
    font-weight: 600;
    margin-right: 1rem;
    transition: var(--transition);
}

.quiz-option.selected .option-letter {
    background-color: var(--category-color, var(--primary-color));
    color: white;
}

.option-text {
    flex: 1;
}

.quiz-navigation {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

/* Quiz Results */
.quiz-results {
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
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 8px solid var(--category-color, var(--primary-color));
    display: flex;
    align-items: center;
    justify-content: center;
}

.score-number {
    font-size: 2.5rem;
    font-weight: 700;
}

.score-text {
    text-align: center;
}

.score-percentage {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--category-color, var(--primary-color));
}

.score-label {
    font-size: 1rem;
    color: var(--text-muted);
}

.results-details {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
    font-size: 1rem;
    color: var(--text-muted);
}

.results-time i, .results-badges i {
    color: var(--category-color, var(--primary-color));
    margin-right: 0.5rem;
}

.results-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
}

/* Quiz Review */
.quiz-review {
    padding: 2rem;
}

.review-header {
    text-align: center;
    margin-bottom: 2rem;
}

.review-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
}

.review-questions {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-bottom: 2rem;
}

.review-question {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.review-question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: var(--background-color);
    border-bottom: 1px solid var(--border-color);
}

.question-number {
    font-weight: 600;
}

.question-result {
    font-size: 0.875rem;
    font-weight: 500;
}

.question-result.correct {
    color: var(--secondary-color);
}

.question-result.incorrect {
    color: var(--danger-color);
}

.review-question-text {
    padding: 1rem;
    font-size: 1.125rem;
    font-weight: 500;
    border-bottom: 1px solid var(--border-color);
}

.review-options {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.review-option {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    position: relative;
}

.review-option.correct-answer {
    border-color: var(--secondary-color);
    background-color: rgba(16, 185, 129, 0.1);
}

.review-option.incorrect-answer {
    border-color: var(--danger-color);
    background-color: rgba(239, 68, 68, 0.1);
}

.review-option.missed-answer {
    border-color: var(--secondary-color);
    border-style: dashed;
}

.user-answer-icon, .correct-answer-icon {
    position: absolute;
    right: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.user-answer-icon {
    right: 3rem;
    background-color: var(--text-muted);
    color: white;
}

.correct-answer-icon {
    background-color: var(--secondary-color);
    color: white;
}

.review-actions {
    display: flex;
    justify-content: center;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 1rem;
    transition: var(--transition);
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn i {
    margin-right: 0.5rem;
}

.btn i:last-child {
    margin-right: 0;
    margin-left: 0.5rem;
}

.btn-primary {
    background-color: var(--category-color, var(--primary-color));
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-primary:disabled {
    background-color: var(--border-color);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-outline {
    background-color: transparent;
    border: 2px solid var(--category-color, var(--primary-color));
    color: var(--category-color, var(--primary-color));
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.btn-outline:disabled {
    border-color: var(--border-color);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
}

.btn-success {
    background-color: var(--secondary-color);
    color: white;
}

.btn-success:hover {
    background-color: var(--secondary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-success:disabled {
    background-color: var(--border-color);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quiz-title {
        font-size: 1.5rem;
    }
    
    .quiz-meta {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .quiz-navigation {
        flex-direction: column;
        gap: 0.75rem;
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
}

@media (max-width: 576px) {
    .quiz-header {
        padding: 1.5rem;
    }
    
    .quiz-content, .quiz-results, .quiz-review {
        padding: 1.5rem;
    }
    
    .score-circle {
        width: 120px;
        height: 120px;
    }
    
    .score-number {
        font-size: 2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>