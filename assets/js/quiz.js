document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quiz-form');
    if (!quizForm) return;
    
    const totalQuestions = parseInt(quizForm.getAttribute('data-total-questions'));
    const timerSeconds = parseInt(quizForm.getAttribute('data-timer-seconds')) || 10;
    const questionCards = document.querySelectorAll('.question-card');
    const resultsCard = document.querySelector('.results-card');
    const progressFill = document.querySelector('.progress-fill');
    const currentQuestionSpan = document.querySelector('.current-question');
    const timerBar = document.getElementById('question-timer');
    const timerText = document.getElementById('timer-seconds');
    
    let currentQuestionIndex = 0;
    let score = 0;
    let userAnswers = {};
    let timerInterval = null;
    let remainingTime = timerSeconds;
    
    // Démarrer le timer pour la première question
    initializeTimer();
    
    // Submit answer button click
    const submitButtons = document.querySelectorAll('.btn-submit-answer');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const questionCard = this.closest('.question-card');
            submitAnswer(questionCard, false);
        });
    });
    
    // Function to submit the answer
    function submitAnswer(questionCard, isTimerExpired) {
        clearInterval(timerInterval); // Arrêter le timer
        
        const questionIndex = parseInt(questionCard.getAttribute('data-question-index'));
        const radioInputs = questionCard.querySelectorAll('input[type="radio"]');
        
        let selectedOption = null;
        radioInputs.forEach(input => {
            if (input.checked) {
                selectedOption = input;
            }
        });
        
        // Si aucune réponse n'est sélectionnée ou si le timer a expiré
        if (!selectedOption || isTimerExpired) {
            // Afficher le message "Temps écoulé" si c'est le cas
            if (isTimerExpired) {
                const timeExpiredMessage = questionCard.querySelector('.time-expired-message');
                if (timeExpiredMessage) {
                    timeExpiredMessage.classList.remove('hidden');
                }
            }
            
            // Trouver la bonne réponse et la mettre en évidence
            radioInputs.forEach(input => {
                if (input.getAttribute('data-correct') === '1') {
                    input.closest('.option').classList.add('correct');
                    
                    // Enregistrer cette question comme temps écoulé
                    const questionId = input.name.split('_')[1];
                    userAnswers[questionId] = 'temps_ecoule';
                }
            });
            
            // Afficher le feedback négatif
            const feedback = questionCard.querySelector('.feedback');
            const feedbackIncorrect = questionCard.querySelector('.feedback-incorrect');
            
            feedback.classList.remove('hidden');
            feedbackIncorrect.classList.remove('hidden');
        } else {
            // Une option a été sélectionnée
            const questionId = selectedOption.name.split('_')[1];
            userAnswers[questionId] = selectedOption.value;
            
            const isCorrect = selectedOption.getAttribute('data-correct') === '1';
            if (isCorrect) {
                score++;
                selectedOption.closest('.option').classList.add('correct');
                
                // Afficher le feedback positif
                const feedback = questionCard.querySelector('.feedback');
                const feedbackCorrect = questionCard.querySelector('.feedback-correct');
                
                feedback.classList.remove('hidden');
                feedbackCorrect.classList.remove('hidden');
            } else {
                selectedOption.closest('.option').classList.add('incorrect');
                
                // Trouver et mettre en évidence la bonne réponse
                radioInputs.forEach(input => {
                    if (input.getAttribute('data-correct') === '1') {
                        input.closest('.option').classList.add('correct');
                    }
                });
                
                // Afficher le feedback négatif
                const feedback = questionCard.querySelector('.feedback');
                const feedbackIncorrect = questionCard.querySelector('.feedback-incorrect');
                
                feedback.classList.remove('hidden');
                feedbackIncorrect.classList.remove('hidden');
            }
        }
        
        // Désactiver toutes les options
        radioInputs.forEach(input => {
            input.disabled = true;
        });
        
        // Cacher le bouton "Valider" et afficher le bouton "Question suivante"
        questionCard.querySelector('.btn-submit-answer').classList.add('hidden');
        questionCard.querySelector('.btn-next-question').classList.remove('hidden');
        
        // Si le timer a expiré, passer automatiquement à la question suivante après un délai
        if (isTimerExpired) {
            setTimeout(() => {
                goToNextQuestion();
            }, 2000);
        }
    }
    
    // Next question button click
    const nextButtons = document.querySelectorAll('.btn-next-question');
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            goToNextQuestion();
        });
    });
    
    // Function to go to the next question
    function goToNextQuestion() {
        const questionCard = questionCards[currentQuestionIndex];
        questionCard.classList.remove('active');
        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            questionCards[currentQuestionIndex].classList.add('active');
            updateProgress();
            initializeTimer();
        } else {
            showResults();
        }
    }
    
    
    // Timer initialization
    function initializeTimer() {
        remainingTime = timerSeconds;
        
        // Mise à jour du texte du timer
        if (timerText) {
            timerText.textContent = remainingTime;
        }
        
        // Reset de la barre de progression du timer
        if (timerBar) {
            // Reset instantané sans transition
            timerBar.style.transition = 'none';
            timerBar.style.width = '100%';
            
            // Force le navigateur à appliquer les styles avant la prochaine transition
            void timerBar.offsetWidth;
            
            // Démarre l'animation de la barre qui se vide
            timerBar.style.transition = `width ${timerSeconds}s linear`;
            timerBar.style.width = '0%';
        }
        
        // Démarrage du compte à rebours
        clearInterval(timerInterval);
        timerInterval = setInterval(function() {
            remainingTime--;
            
            // Mise à jour du texte du timer
            if (timerText) {
                timerText.textContent = remainingTime;
            }
            
            // Si le temps est écoulé
            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                submitAnswer(questionCards[currentQuestionIndex], true);
            }
        }, 1000);
    }
    
    // Update progress bar and question counter
    function updateProgress() {
        const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }
        if (currentQuestionSpan) {
            currentQuestionSpan.textContent = currentQuestionIndex + 1;
        }
    }
    
    // Restart quiz button click
    const restartButton = document.querySelector('.btn-restart');
    if (restartButton) {
        restartButton.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Show quiz results
    function showResults() {
        if (resultsCard) {
            resultsCard.classList.remove('hidden');
            
            // Update score
            const scoreElement = resultsCard.querySelector('.score');
            if (scoreElement) {
                scoreElement.textContent = score;
            }
            
            // Update message based on score
            const messageElement = resultsCard.querySelector('.results-message');
            if (messageElement) {
                const percentage = (score / totalQuestions) * 100;
                
                if (percentage === 100) {
                    messageElement.textContent = 'Parfait! Vous avez tout bon!';
                    messageElement.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
                    messageElement.style.color = 'var(--success-color)';
                } else if (percentage >= 70) {
                    messageElement.textContent = 'Très bien! Vous avez de bonnes connaissances!';
                    messageElement.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
                    messageElement.style.color = 'var(--success-color)';
                } else if (percentage >= 50) {
                    messageElement.textContent = 'Pas mal! Vous pouvez encore vous améliorer.';
                    messageElement.style.backgroundColor = 'rgba(234, 179, 8, 0.1)';
                    messageElement.style.color = 'var(--warning-color)';
                } else {
                    messageElement.textContent = 'Continuez à apprendre et réessayez!';
                    messageElement.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                    messageElement.style.color = 'var(--danger-color)';
                }
            }
            
            // Generate summary
            const summaryElement = resultsCard.querySelector('.results-summary');
            if (summaryElement) {
                summaryElement.innerHTML = '';
                
                questionCards.forEach((card, index) => {
                    const questionText = card.querySelector('.question-text').textContent;
                    const radioInputs = card.querySelectorAll('input[type="radio"]');
                    
                    if (radioInputs.length === 0) return;
                    
                    const questionId = radioInputs[0].name.split('_')[1];
                    let userAnswer = '';
                    let correctAnswer = '';
                    let isCorrect = false;
                    
                    // Gérer le cas où le temps est écoulé
                    if (userAnswers[questionId] === 'temps_ecoule') {
                        userAnswer = 'Temps écoulé';
                        isCorrect = false;
                    } else {
                        // Traitement normal
                        radioInputs.forEach(input => {
                            if (userAnswers[questionId] === input.value) {
                                userAnswer = input.nextElementSibling.textContent.trim();
                            }
                            
                            if (input.getAttribute('data-correct') === '1') {
                                correctAnswer = input.nextElementSibling.textContent.trim();
                            }
                            
                            if (userAnswers[questionId] === input.value && input.getAttribute('data-correct') === '1') {
                                isCorrect = true;
                            }
                        });
                    }
                    
                    const resultItem = document.createElement('div');
                    resultItem.className = `result-item ${isCorrect ? 'correct' : 'incorrect'}`;
                    
                    resultItem.innerHTML = `
                        <p><strong>Question ${index + 1}:</strong> ${questionText}</p>
                        <p>Votre réponse: ${userAnswer}</p>
                        ${!isCorrect ? `<p>Réponse correcte: ${correctAnswer}</p>` : ''}
                    `;
                    
                    summaryElement.appendChild(resultItem);
                });
            }
            
            // Send results to server if user is logged in
            const categorie_id = document.querySelector('input[name="categorie_id"]').value;
            const difficulte_id = document.querySelector('input[name="difficulte_id"]').value;
            
            // Convertir les "temps_ecoule" en réponses réelles pour le serveur
            const reponsesPourServeur = {};
            Object.keys(userAnswers).forEach(questionId => {
                if (userAnswers[questionId] === 'temps_ecoule') {
                    // Trouver une option incorrecte à envoyer
                    const questionCard = Array.from(questionCards).find(card => {
                        const inputs = card.querySelectorAll('input[type="radio"]');
                        return inputs.length > 0 && inputs[0].name.split('_')[1] === questionId;
                    });
                    
                    if (questionCard) {
                        const inputs = questionCard.querySelectorAll('input[type="radio"]');
                        for (let i = 0; i < inputs.length; i++) {
                            if (inputs[i].getAttribute('data-correct') !== '1') {
                                reponsesPourServeur[questionId] = inputs[i].value;
                                break;
                            }
                        }
                    }
                } else {
                    reponsesPourServeur[questionId] = userAnswers[questionId];
                }
            });
            
            const resultData = {
                categorie_id: categorie_id,
                difficulte_id: difficulte_id,
                score: score,
                total: totalQuestions,
                reponses: reponsesPourServeur
            };
            
            fetch('traiter_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(resultData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Quiz results saved:', data);
                
                // If user is not logged in, show message
                if (data.non_connecte) {
                    const loginMessage = document.createElement('div');
                    loginMessage.className = 'alert';
                    loginMessage.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                    loginMessage.style.color = 'var(--primary-color)';
                    loginMessage.innerHTML = 'Connectez-vous pour enregistrer vos résultats et suivre votre progression!';
                    
                    const actionsDiv = resultsCard.querySelector('.results-actions');
                    if (actionsDiv) {
                        actionsDiv.insertBefore(loginMessage, actionsDiv.firstChild);
                    }
                }
            })
            .catch(error => {
                console.error('Error saving quiz results:', error);
            });
        }
    }
});