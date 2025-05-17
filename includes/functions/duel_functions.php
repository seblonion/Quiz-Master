<?php
/**
 * Functions for the duel system
 */

/**
 * Create a new duel challenge
 * 
 * @param int $challenger_id The ID of the user initiating the challenge
 * @param int $opponent_id The ID of the user being challenged
 * @param string $type The type of duel (timed, accuracy, mixed)
 * @param int $categorie_id Optional category ID
 * @param int $difficulte_id Optional difficulty ID
 * @param int $time_limit Time limit in seconds (0 for no limit)
 * @param int $question_count Number of questions
 * @param string $message Optional invitation message
 * @return int|bool The duel ID if successful, false otherwise
 */
function createDuelChallenge($challenger_id, $opponent_id, $type, $categorie_id = null, $difficulte_id = null, $time_limit = 0, $question_count = 10, $message = '') {
   $database = new Database();
   $db = $database->connect();
   
   try {
       $db->beginTransaction();
       
       // Create the duel
       $query = "INSERT INTO duels (challenger_id, opponent_id, categorie_id, difficulte_id, type, time_limit, question_count) 
                 VALUES (:challenger_id, :opponent_id, :categorie_id, :difficulte_id, :type, :time_limit, :question_count)";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':challenger_id', $challenger_id, PDO::PARAM_INT);
       $stmt->bindParam(':opponent_id', $opponent_id, PDO::PARAM_INT);
       $stmt->bindParam(':categorie_id', $categorie_id, $categorie_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
       $stmt->bindParam(':difficulte_id', $difficulte_id, $difficulte_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
       $stmt->bindParam(':type', $type);
       $stmt->bindParam(':time_limit', $time_limit, PDO::PARAM_INT);
       $stmt->bindParam(':question_count', $question_count, PDO::PARAM_INT);
       $stmt->execute();
       
       $duel_id = $db->lastInsertId();
       
       // Select questions based on category and difficulty (if provided)
       $question_count = max(1, $question_count); // Ensure at least 1 question
       $query = "SELECT q.id 
                 FROM questions q
                 LEFT JOIN options o ON q.id = o.question_id
                 WHERE o.id IS NOT NULL " . 
                 ($categorie_id ? "AND q.categorie_id = :categorie_id " : "") .
                 ($difficulte_id ? "AND q.difficulte_id = :difficulte_id " : "") .
                 "GROUP BY q.id 
                 HAVING COUNT(o.id) > 0
                 ORDER BY RAND() 
                 LIMIT :question_count";
       $stmt = $db->prepare($query);
       if ($categorie_id) $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT);
       if ($difficulte_id) $stmt->bindParam(':difficulte_id', $difficulte_id, PDO::PARAM_INT);
       $stmt->bindParam(':question_count', $question_count, PDO::PARAM_INT);
       $stmt->execute();
       $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

       // Vérifier si des questions ont été trouvées
       if (empty($questions)) {
           $db->rollBack();
           error_log("No questions available for duel_id=$duel_id with categorie_id=" . ($categorie_id ?? 'null') . " and difficulte_id=" . ($difficulte_id ?? 'null'));
           return false;
       }

       // Insert selected questions into duel_questions
       foreach ($questions as $index => $question) {
           $query = "INSERT INTO duel_questions (duel_id, question_id, question_order) 
                     VALUES (:duel_id, :question_id, :question_order)";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
           $stmt->bindParam(':question_id', $question['id'], PDO::PARAM_INT);
           $stmt->bindParam(':question_order', $index, PDO::PARAM_INT);
           $stmt->execute();
       }

       // Create the invitation
       $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
       $query = "INSERT INTO duel_invitations (duel_id, sender_id, recipient_id, message, expires_at) 
                 VALUES (:duel_id, :sender_id, :recipient_id, :message, :expires_at)";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
       $stmt->bindParam(':sender_id', $challenger_id, PDO::PARAM_INT);
       $stmt->bindParam(':recipient_id', $opponent_id, PDO::PARAM_INT);
       $stmt->bindParam(':message', $message);
       $stmt->bindParam(':expires_at', $expires_at);
       $stmt->execute();
       
       $db->commit();
       return $duel_id;
   } catch (PDOException $e) {
       $db->rollBack();
       error_log("Error creating duel challenge: " . $e->getMessage());
       return false;
   }
}

/**
 * Get duel details by ID
 * 
 * @param int $duel_id The duel ID
 * @return array|bool The duel details or false if not found
 */
function getDuelById($duel_id) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT d.*, 
                    c.nom as categorie_nom, 
                    diff.nom as difficulte_nom,
                    u1.nom as challenger_nom,
                    u2.nom as opponent_nom,
                    u3.nom as winner_nom,
                    c.nom as category_name,
                    diff.nom as difficulty_name
             FROM duels d
             LEFT JOIN categories c ON d.categorie_id = c.id
             LEFT JOIN difficultes diff ON d.difficulte_id = diff.id
             LEFT JOIN utilisateurs u1 ON d.challenger_id = u1.id
             LEFT JOIN utilisateurs u2 ON d.opponent_id = u2.id
             LEFT JOIN utilisateurs u3 ON d.winner_id = u3.id
             WHERE d.id = :duel_id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
   $stmt->execute();
   
   $duel = $stmt->fetch(PDO::FETCH_ASSOC);
   if (!$duel) {
       return false;
   }
   
   return $duel;
}

/**
 * Get pending duel invitations for a user
 * 
 * @param int $user_id The user ID
 * @return array The pending invitations
 */
function getPendingDuelInvitations($user_id) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT di.*, 
                    d.type, d.categorie_id, d.difficulte_id, d.time_limit, d.question_count,
                    u.nom as sender_nom,
                    c.nom as categorie_nom,
                    diff.nom as difficulte_nom
             FROM duel_invitations di
             JOIN duels d ON di.duel_id = d.id
             JOIN utilisateurs u ON di.sender_id = u.id
             LEFT JOIN categories c ON d.categorie_id = c.id
             LEFT JOIN difficultes diff ON d.difficulte_id = diff.id
             WHERE di.recipient_id = :user_id 
             AND di.status = 'pending'
             AND di.expires_at > NOW()
             ORDER BY di.created_at DESC";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Accept a duel invitation
 * 
 * @param int $invitation_id The invitation ID
 * @return bool True if successful, false otherwise
 */
function acceptDuelInvitation($invitation_id) {
    try {
        $database = new Database();
        $db = $database->connect();
        $db->beginTransaction();

        // Vérifier si l'invitation existe et si le duel est en attente
        $query = "SELECT di.*, d.status AS duel_status 
                  FROM duel_invitations di 
                  JOIN duels d ON di.duel_id = d.id 
                  WHERE di.id = :invitation_id AND di.status = 'pending' AND d.status = 'pending'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
        $stmt->execute();
        $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invitation) {
            error_log("Invitation $invitation_id not found, not pending, or duel not pending");
            $db->rollBack();
            return false;
        }

        // Mettre à jour l'invitation et le duel
        $query = "UPDATE duel_invitations di 
                  JOIN duels d ON di.duel_id = d.id 
                  SET di.status = 'accepted', d.status = 'active', d.started_at = NOW() 
                  WHERE di.id = :invitation_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
        $stmt->execute();

        $db->commit();
        error_log("Successfully accepted invitation $invitation_id, duel set to active");
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error accepting duel invitation $invitation_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Decline a duel invitation
 * 
 * @param int $invitation_id The invitation ID
 * @return bool True if successful, false otherwise
 */
function declineDuelInvitation($invitation_id) {
   $database = new Database();
   $db = $database->connect();
   
   try {
       $db->beginTransaction();
       
       // Get the invitation
       $query = "SELECT * FROM duel_invitations WHERE id = :invitation_id AND status = 'pending'";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
       $stmt->execute();
       $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
       
       if (!$invitation) {
           return false;
       }
       
       // Update invitation status
       $query = "UPDATE duel_invitations SET status = 'declined' WHERE id = :invitation_id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':invitation_id', $invitation_id, PDO::PARAM_INT);
       $stmt->execute();
       
       // Update duel status
       $query = "UPDATE duels SET status = 'cancelled' WHERE id = :duel_id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':duel_id', $invitation['duel_id'], PDO::PARAM_INT);
       $stmt->execute();
       
       $db->commit();
       return true;
   } catch (PDOException $e) {
       $db->rollBack();
       error_log("Error declining duel invitation: " . $e->getMessage());
       return false;
   }
}

/**
 * Get questions for a duel
 * 
 * @param int $duel_id The duel ID
 * @return array The questions with their options
 */
function getDuelQuestions($duel_id) {
    try {
        $database = new Database();
        $db = $database->connect();

        // Récupérer les questions et leurs options
        $query = "SELECT q.id, q.question, q.categorie_id, q.difficulte_id, dq.question_order, 
                         o.id AS option_id, o.texte AS option_texte, o.est_correcte
                  FROM duel_questions dq
                  JOIN questions q ON dq.question_id = q.id
                  LEFT JOIN options o ON q.id = o.question_id
                  WHERE dq.duel_id = :duel_id
                  ORDER BY dq.question_order, o.id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les options par question
        $questions = [];
        foreach ($results as $row) {
            $question_id = $row['id'];
            if (!isset($questions[$question_id])) {
                $questions[$question_id] = [
                    'id' => $row['id'],
                    'texte' => $row['question'], // Utiliser le nom réel de la colonne
                    'categorie_id' => $row['categorie_id'],
                    'difficulte_id' => $row['difficulte_id'],
                    'question_order' => $row['question_order'],
                    'options' => [],
                ];
            }
            if ($row['option_id']) {
                $questions[$question_id]['options'][] = [
                    'id' => $row['option_id'],
                    'texte' => $row['option_texte'],
                    'est_correcte' => (string)$row['est_correcte'], // Convertir en string ("0" ou "1")
                ];
            }
        }

        $questions = array_values($questions);

        // Vérifier si des questions existent
        if (empty($questions)) {
            // Ajouter un débogage pour vérifier les données dans duel_questions
            $check = $db->prepare("SELECT * FROM duel_questions WHERE duel_id = :duel_id");
            $check->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
            $check->execute();
            $duel_questions = $check->fetchAll(PDO::FETCH_ASSOC);
            error_log("No questions found for duel_id=$duel_id. duel_questions table content: " . print_r($duel_questions, true));
            return [];
        }

        // Vérifier si chaque question a des options
        foreach ($questions as $index => $question) {
            if (empty($question['options'])) {
                error_log("No options found for question_id={$question['id']} in duel_id=$duel_id");
                unset($questions[$index]); // Supprimer les questions sans options
            }
        }

        $questions = array_values($questions); // Réindexer le tableau après suppression
        if (empty($questions)) {
            error_log("No questions with options available for duel_id=$duel_id");
        }

        return $questions;
    } catch (Exception $e) {
        error_log("Error fetching questions for duel_id=$duel_id: " . $e->getMessage());
        return [];
    }
}

/**
 * Submit an answer for a duel
 * 
 * @param int $duel_id The duel ID
 * @param int $user_id The user ID
 * @param int $question_id The question ID
 * @param int $answer_id The answer ID
 * @param int $response_time Response time in milliseconds
 * @return bool True if successful, false otherwise
 */
function submitDuelAnswer($duel_id, $user_id, $question_id, $answer_id, $response_time) {
   $database = new Database();
   $db = $database->connect();
   
   try {
       $db->beginTransaction();
       
       // Check if the answer is correct
       $query = "SELECT est_correcte FROM options WHERE id = :answer_id AND question_id = :question_id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':answer_id', $answer_id, PDO::PARAM_INT);
       $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
       $stmt->execute();
       $answer = $stmt->fetch(PDO::FETCH_ASSOC);
       
       $is_correct = $answer && $answer['est_correcte'] ? 1 : 0;
       
       // Record the answer
       $query = "INSERT INTO duel_answers (duel_id, user_id, question_id, answer_id, is_correct, response_time) 
                 VALUES (:duel_id, :user_id, :question_id, :answer_id, :is_correct, :response_time)";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
       $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
       $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
       $stmt->bindParam(':answer_id', $answer_id, PDO::PARAM_INT);
       $stmt->bindParam(':is_correct', $is_correct, PDO::PARAM_INT);
       $stmt->bindParam(':response_time', $response_time, PDO::PARAM_INT);
       $stmt->execute();
       
       // Update duel_results
       if ($is_correct) {
           $query = "UPDATE duel_results 
                     SET score = score + 1, correct_answers = correct_answers + 1 
                     WHERE duel_id = :duel_id AND user_id = :user_id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
           $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
           $stmt->execute();
       }
       
       $db->commit();
       return true;
   } catch (PDOException $e) {
       $db->rollBack();
       error_log("Error submitting duel answer: " . $e->getMessage());
       return false;
   }
}

/**
 * Complete a duel for a user
 * 
 * @param int $duel_id The duel ID
 * @param int $user_id The user ID
 * @param int $completion_time Completion time in seconds
 * @return bool True if successful, false otherwise
 */
function completeDuelForUser($duel_id, $user_id, $completion_time) {
   $database = new Database();
   $db = $database->connect();
   
   try {
       $db->beginTransaction();
       
       // Update duel_results
       $query = "UPDATE duel_results 
                 SET completion_time = :completion_time, completed_at = NOW() 
                 WHERE duel_id = :duel_id AND user_id = :user_id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':completion_time', $completion_time, PDO::PARAM_INT);
       $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
       $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
       $stmt->execute();
       
       // Check if both users have completed the duel
       $query = "SELECT COUNT(*) as completed FROM duel_results 
                 WHERE duel_id = :duel_id AND completed_at IS NOT NULL";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       // If both users have completed, determine the winner
       if ($result['completed'] == 2) {
           // Get duel details
           $query = "SELECT * FROM duels WHERE id = :duel_id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
           $stmt->execute();
           $duel = $stmt->fetch(PDO::FETCH_ASSOC);
           
           // Get results for both users
           $query = "SELECT dr.*, u.nom as user_nom 
                     FROM duel_results dr
                     JOIN utilisateurs u ON dr.user_id = u.id
                     WHERE dr.duel_id = :duel_id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
           $stmt->execute();
           $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
           
           // Determine winner based on duel type
           $winner_id = null;
           
           if (count($results) == 2) {
               $user1 = $results[0];
               $user2 = $results[1];
               
               switch ($duel['type']) {
                   case 'timed':
                       // Priorité au nombre de bonnes réponses
                       if ($user1['correct_answers'] > $user2['correct_answers']) {
                           $winner_id = $user1['user_id'];
                       } elseif ($user2['correct_answers'] > $user1['correct_answers']) {
                           $winner_id = $user2['user_id'];
                       } elseif ($user1['correct_answers'] == $user2['correct_answers']) {
                           // Départage par le temps si les scores sont égaux
                           if ($user1['correct_answers'] == 0 && $user2['correct_answers'] == 0) {
                               $winner_id = null; // Match nul si aucun n'a de bonnes réponses
                           } elseif ($user1['completion_time'] < $user2['completion_time']) {
                               $winner_id = $user1['user_id'];
                           } elseif ($user2['completion_time'] < $user1['completion_time']) {
                               $winner_id = $user2['user_id'];
                           } else {
                               $winner_id = null; // Match nul si temps égaux
                           }
                       }
                       break;
                       
                   case 'accuracy':
                       // Priorité au nombre de bonnes réponses
                       if ($user1['correct_answers'] > $user2['correct_answers']) {
                           $winner_id = $user1['user_id'];
                       } elseif ($user2['correct_answers'] > $user1['correct_answers']) {
                           $winner_id = $user2['user_id'];
                       } elseif ($user1['correct_answers'] == $user2['correct_answers']) {
                           // Départage par le temps si les scores sont égaux
                           if ($user1['correct_answers'] == 0 && $user2['correct_answers'] == 0) {
                               $winner_id = null; // Match nul si aucun n'a de bonnes réponses
                           } elseif ($user1['completion_time'] < $user2['completion_time']) {
                               $winner_id = $user1['user_id'];
                           } elseif ($user2['completion_time'] < $user1['completion_time']) {
                               $winner_id = $user2['user_id'];
                           } else {
                               $winner_id = null; // Match nul si temps égaux
                           }
                       }
                       break;
                       
                   case 'mixed':
                       // Priorité au nombre de bonnes réponses
                       if ($user1['correct_answers'] > $user2['correct_answers']) {
                           $winner_id = $user1['user_id'];
                       } elseif ($user2['correct_answers'] > $user1['correct_answers']) {
                           $winner_id = $user2['user_id'];
                       } elseif ($user1['correct_answers'] == $user2['correct_answers']) {
                           // Départage par un score combiné (précision + vitesse)
                           if ($user1['correct_answers'] == 0 && $user2['correct_answers'] == 0) {
                               $winner_id = null; // Match nul si aucun n'a de bonnes réponses
                           } else {
                               $score1 = ($user1['correct_answers'] / $user1['total_questions'] * 100) + 
                                         (1 / max($user1['completion_time'], 1) * 1000);
                               $score2 = ($user2['correct_answers'] / $user2['total_questions'] * 100) + 
                                         (1 / max($user2['completion_time'], 1) * 1000);
                               
                               if ($score1 > $score2) {
                                   $winner_id = $user1['user_id'];
                               } elseif ($score2 > $score1) {
                                   $winner_id = $user2['user_id'];
                               } else {
                                   $winner_id = null; // Match nul si scores combinés égaux
                               }
                           }
                       }
                       break;
               }
           }
           
           // Update duel status
           $query = "UPDATE duels 
                     SET status = 'completed', completed_at = NOW(), winner_id = :winner_id 
                     WHERE id = :duel_id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':winner_id', $winner_id, $winner_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
           $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
           $stmt->execute();
           
           // Award achievements if there's a winner
           if ($winner_id) {
               awardDuelAchievements($winner_id, $duel_id);
           }
       }
       
       $db->commit();
       return true;
   } catch (PDOException $e) {
       $db->rollBack();
       error_log("Error completing duel: " . $e->getMessage());
       return false;
   }
}

/**
 * Award achievements for duel winners
 * 
 * @param int $user_id The user ID
 * @param int $duel_id The duel ID
 * @return bool True if successful, false otherwise
 */
function awardDuelAchievements($user_id, $duel_id) {
   $database = new Database();
   $db = $database->connect();
   
   try {
       // Check for perfect score
       $query = "SELECT * FROM duel_results WHERE duel_id = :duel_id AND user_id = :user_id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
       $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       if ($result['correct_answers'] == $result['total_questions']) {
           // Perfect score achievement
           $query = "INSERT INTO duel_achievements (user_id, achievement_type) 
                     VALUES (:user_id, 'perfect_score')";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
           $stmt->execute();
       }
       
       // Check for win streak
       $query = "SELECT d.* FROM duels d
                 WHERE (d.challenger_id = :user_id OR d.opponent_id = :user_id)
                 AND d.winner_id = :user_id
                 AND d.status = 'completed'
                 ORDER BY d.completed_at DESC
                 LIMIT 3";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
       $stmt->execute();
       $recent_wins = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       if (count($recent_wins) == 3) {
           // Win streak achievement
           $query = "INSERT INTO duel_achievements (user_id, achievement_type) 
                     VALUES (:user_id, 'win_streak')";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
           $stmt->execute();
       }
       
       // Check for first win
       $query = "SELECT COUNT(*) as win_count FROM duels 
                 WHERE winner_id = :user_id AND status = 'completed'";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
       $stmt->execute();
       $win_count = $stmt->fetch(PDO::FETCH_ASSOC)['win_count'];
       
       if ($win_count == 1) {
           // First win achievement
           $query = "INSERT INTO duel_achievements (user_id, achievement_type) 
                     VALUES (:user_id, 'first_win')";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
           $stmt->execute();
       }
       
       return true;
   } catch (PDOException $e) {
       error_log("Error awarding duel achievements: " . $e->getMessage());
       return false;
   }
}

/**
 * Get active duels for a user
 * 
 * @param int $user_id The user ID
 * @return array The active duels
 */
function getActiveDuelsForUser($user_id) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT d.*, 
                    c.nom as categorie_nom, 
                    diff.nom as difficulte_nom,
                    u1.nom as challenger_nom,
                    u2.nom as opponent_nom
             FROM duels d
             LEFT JOIN categories c ON d.categorie_id = c.id
             LEFT JOIN difficultes diff ON d.difficulte_id = diff.id
             LEFT JOIN utilisateurs u1 ON d.challenger_id = u1.id
             LEFT JOIN utilisateurs u2 ON d.opponent_id = u2.id
             WHERE (d.challenger_id = :user_id OR d.opponent_id = :user_id)
             AND d.status = 'active'
             ORDER BY d.started_at DESC";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get completed duels for a user
 * 
 * @param int $user_id The user ID
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array The completed duels
 */
function getCompletedDuelsForUser($user_id, $limit = 10, $offset = 0) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT d.*, 
                    c.nom as categorie_nom, 
                    diff.nom as difficulte_nom,
                    u1.nom as challenger_nom,
                    u2.nom as opponent_nom,
                    u3.nom as winner_nom,
                    dr.score, dr.correct_answers, dr.total_questions, dr.completion_time
             FROM duels d
             LEFT JOIN categories c ON d.categorie_id = c.id
             LEFT JOIN difficultes diff ON d.difficulte_id = diff.id
             LEFT JOIN utilisateurs u1 ON d.challenger_id = u1.id
             LEFT JOIN utilisateurs u2 ON d.opponent_id = u2.id
             LEFT JOIN utilisateurs u3 ON d.winner_id = u3.id
             LEFT JOIN duel_results dr ON d.id = dr.duel_id AND dr.user_id = :user_id
             WHERE (d.challenger_id = :user_id OR d.opponent_id = :user_id)
             AND d.status = 'completed'
             ORDER BY d.completed_at DESC
             LIMIT :limit OFFSET :offset";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
   $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
   $stmt->execute();
   
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get duel leaderboard
 * 
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array The leaderboard
 */
function getDuelLeaderboard($limit, $offset, $period = 'all', $type = 'all') {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT 
            id, nom, total_duels, wins, losses, draws, win_percentage, 
            avg_accuracy, avg_completion_time, est_contributeur
        FROM duel_leaderboard 
        WHERE total_duels > 0
    ";
    $params = [];

    // Filtres
    if ($period == 'month') {
        $query .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    } elseif ($period == 'week') {
        $query .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    }

    if ($type != 'all') {
        $query .= " AND preferred_duel_type = :type";
        $params[':type'] = $type;
    }

    // Tri et pagination
    $query .= " ORDER BY wins DESC, win_percentage DESC, avg_accuracy DESC LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Submit a duel report
 * 
 * @param int $duel_id The duel ID
 * @param int $reporter_id The reporter user ID
 * @param int $reported_user_id The reported user ID
 * @param string $reason The reason for the report
 * @param string $description Additional description
 * @return bool True if successful, false otherwise
 */
function submitDuelReport($duel_id, $reporter_id, $reported_id, $reason, $description) {
    $database = new Database();
    $db = $database->connect();
    
    try {
        $query = "INSERT INTO duel_reports (duel_id, reporter_id, reported_id, reason, description) 
                  VALUES (:duel_id, :reporter_id, :reported_id, :reason, :description)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
        $stmt->bindParam(':reporter_id', $reporter_id, PDO::PARAM_INT);
        $stmt->bindParam(':reported_id', $reported_id, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        error_log("Error submitting duel report: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's duel statistics
 * 
 * @param int $user_id The user ID
 * @return array The statistics
 */
function getUserDuelStatistics($user_id) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT 
               COUNT(d.id) AS total_duels,
               SUM(CASE WHEN d.winner_id = :user_id THEN 1 ELSE 0 END) AS wins,
               SUM(CASE WHEN d.winner_id IS NOT NULL AND d.winner_id != :user_id THEN 1 ELSE 0 END) AS losses,
               SUM(CASE WHEN d.winner_id IS NULL AND d.status = 'completed' THEN 1 ELSE 0 END) AS draws,
               ROUND(AVG(dr.completion_time), 2) AS avg_completion_time,
               ROUND(AVG(dr.correct_answers / dr.total_questions * 100), 2) AS avg_accuracy
             FROM duels d
             LEFT JOIN duel_results dr ON d.id = dr.duel_id AND dr.user_id = :user_id
             WHERE (d.challenger_id = :user_id OR d.opponent_id = :user_id)
             AND d.status = 'completed'";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   
   $stats = $stmt->fetch(PDO::FETCH_ASSOC);
   
   // Get achievements
   $query = "SELECT achievement_type, COUNT(*) as count 
             FROM duel_achievements 
             WHERE user_id = :user_id 
             GROUP BY achievement_type";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   $achievements = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
   
   $stats['achievements'] = $achievements;
   
   return $stats;
}

/**
* Get user's rank in duel leaderboard
* 
* @param int $user_id The user ID
* @param string $period Optional period filter (all, month, week)
* @param string $type Optional duel type filter (all, timed, accuracy, mixed)
* @return array|bool The user's rank data or false if not found
*/
function getUserDuelRank($user_id, $period = 'all', $type = 'all') {
   $database = new Database();
   $db = $database->connect();
   
   $whereClause = "WHERE total_duels > 0";
   $params = [':user_id' => $user_id];
   
   // Add period filter
   if ($period == 'month') {
       $whereClause .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
   } elseif ($period == 'week') {
       $whereClause .= " AND last_duel_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
   }
   
   // Add type filter
   if ($type != 'all') {
       $whereClause .= " AND preferred_duel_type = :type";
       $params[':type'] = $type;
   }
   
   // First get the user's data
   $query = "SELECT * FROM duel_leaderboard WHERE id = :user_id AND total_duels > 0";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
   
   if (!$user_data) {
       return false;
   }
   
   // Then count how many users are ranked higher
   $query = "SELECT COUNT(*) as rank_position FROM duel_leaderboard 
             $whereClause
             AND (
                 wins > :wins OR 
                 (wins = :wins AND win_percentage > :win_percentage) OR
                 (wins = :wins AND win_percentage = :win_percentage AND avg_accuracy > :avg_accuracy)
             )";
   
   $stmt = $db->prepare($query);
   $stmt->bindParam(':wins', $user_data['wins'], PDO::PARAM_INT);
   $stmt->bindParam(':win_percentage', $user_data['win_percentage']);
   $stmt->bindParam(':avg_accuracy', $user_data['avg_accuracy']);
   
   foreach ($params as $key => $value) {
       if ($key != ':user_id') {
           $stmt->bindValue($key, $value);
       }
   }
   
   $stmt->execute();
   $rank_position = $stmt->fetch(PDO::FETCH_ASSOC)['rank_position'];
   
   // Add rank to user data
   $user_data['rank'] = $rank_position;
   
   return $user_data;
}

/**
* Get duel results for the results page
* 
* @param int $duel_id The duel ID
* @return array The duel results
*/
function getDuelResults($duel_id) {
   $database = new Database();
   $db = $database->connect();
   
   // Get the duel
   $query = "SELECT * FROM duels WHERE id = :duel_id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
   $stmt->execute();
   $duel = $stmt->fetch(PDO::FETCH_ASSOC);
   
   if (!$duel) {
       return false;
   }
   
   // Get results for both players
   $query = "SELECT dr.*, u.nom as user_name 
             FROM duel_results dr
             JOIN utilisateurs u ON dr.user_id = u.id
             WHERE dr.duel_id = :duel_id";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
   $stmt->execute();
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
   if (count($results) != 2) {
       return false;
   }
   
   // Format results
   $challenger_result = null;
   $opponent_result = null;
   
   foreach ($results as $result) {
       if ($result['user_id'] == $duel['challenger_id']) {
           $challenger_result = $result;
       } else {
           $opponent_result = $result;
       }
   }
   
   if (!$challenger_result || !$opponent_result) {
       return false;
   }
   
   // Calculate accuracy
   $challenger_accuracy = $challenger_result['total_questions'] > 0 ? 
       ($challenger_result['correct_answers'] / $challenger_result['total_questions']) * 100 : 0;
   
   $opponent_accuracy = $opponent_result['total_questions'] > 0 ? 
       ($opponent_result['correct_answers'] / $opponent_result['total_questions']) * 100 : 0;
   
   return [
       'challenger_score' => $challenger_result['correct_answers'],
       'challenger_total' => $challenger_result['total_questions'],
       'challenger_accuracy' => $challenger_accuracy,
       'challenger_time' => $challenger_result['completion_time'],
       
       'opponent_score' => $opponent_result['correct_answers'],
       'opponent_total' => $opponent_result['total_questions'],
       'opponent_accuracy' => $opponent_accuracy,
       'opponent_time' => $opponent_result['completion_time'],
       
       'winner_id' => $duel['winner_id'],
       'duel' => $duel
   ];
}

/**
* Get player answers for a duel
* 
* @param int $duel_id The duel ID
* @param int $user_id The user ID
* @return array The player's answers
*/
function getDuelPlayerAnswers($duel_id, $user_id) {
   $database = new Database();
   $db = $database->connect();
   
   $query = "SELECT da.*, q.question as question_text, r.texte as answer, 
             da.response_time / 1000 as time_taken
             FROM duel_answers da
             JOIN questions q ON da.question_id = q.id
             LEFT JOIN options r ON da.answer_id = r.id
             WHERE da.duel_id = :duel_id AND da.user_id = :user_id
             ORDER BY da.created_at";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
   $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $stmt->execute();
   
   $answers = [];
   while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
       $answers[$row['question_id']] = [
           'answer_id' => $row['answer_id'],
           'answer' => $row['answer'],
           'is_correct' => $row['is_correct'] == 1,
           'time_taken' => $row['time_taken'],
           'created_at' => $row['created_at']
       ];
   }
   
   return $answers;
}

/**
* Determine the winner of a duel based on results
* 
* @param array $results The duel results
* @return int|string The winner ID or 'draw' if it's a tie
*/
function determineWinner($results) {
   if (!$results || !isset($results['duel']['type'])) {
       return false;
   }
   
   // Si un gagnant est déjà défini dans la table duels
   if ($results['winner_id']) {
       return $results['winner_id'];
   }
   
   // Sinon, déterminer le gagnant en fonction du type de duel
   $challenger_score = $results['challenger_score'] ?? 0;
   $opponent_score = $results['opponent_score'] ?? 0;
   $challenger_time = $results['challenger_time'] ?? PHP_INT_MAX;
   $opponent_time = $results['opponent_time'] ?? PHP_INT_MAX;
   $challenger_total = $results['challenger_total'] ?? 1; // Éviter division par 0
   $opponent_total = $results['opponent_total'] ?? 1;
   
   switch ($results['duel']['type']) {
       case 'timed':
           // Priorité au score
           if ($challenger_score > $opponent_score) {
               return $results['duel']['challenger_id'];
           } elseif ($opponent_score > $challenger_score) {
               return $results['duel']['opponent_id'];
           } elseif ($challenger_score == $opponent_score) {
               // Départage par le temps
               if ($challenger_score == 0 && $opponent_score == 0) {
                   return 'draw'; // Match nul si aucun n'a de bonnes réponses
               } elseif ($challenger_time < $opponent_time) {
                   return $results['duel']['challenger_id'];
               } elseif ($opponent_time < $challenger_time) {
                   return $results['duel']['opponent_id'];
               } else {
                   return 'draw'; // Match nul si temps égaux
               }
           }
           break;
           
       case 'accuracy':
           // Priorité au score
           if ($challenger_score > $opponent_score) {
               return $results['duel']['challenger_id'];
           } elseif ($opponent_score > $challenger_score) {
               return $results['duel']['opponent_id'];
           } elseif ($challenger_score == $opponent_score) {
               // Départage par le temps
               if ($challenger_score == 0 && $opponent_score == 0) {
                   return 'draw'; // Match nul si aucun n'a de bonnes réponses
               } elseif ($challenger_time < $opponent_time) {
                   return $results['duel']['challenger_id'];
               } elseif ($opponent_time < $challenger_time) {
                   return $results['duel']['opponent_id'];
               } else {
                   return 'draw'; // Match nul si temps égaux
               }
           }
           break;
           
       case 'mixed':
           // Priorité au score
           if ($challenger_score > $opponent_score) {
               return $results['duel']['challenger_id'];
           } elseif ($opponent_score > $challenger_score) {
               return $results['duel']['opponent_id'];
           } elseif ($challenger_score == $opponent_score) {
               // Départage par un score combiné
               if ($challenger_score == 0 && $opponent_score == 0) {
                   return 'draw'; // Match nul si aucun n'a de bonnes réponses
               } else {
                   $score1 = ($challenger_score / $challenger_total * 100) + 
                             (1 / max($challenger_time, 1) * 1000);
                   $score2 = ($opponent_score / $opponent_total * 100) + 
                             (1 / max($opponent_time, 1) * 1000);
                   
                   if ($score1 > $score2) {
                       return $results['duel']['challenger_id'];
                   } elseif ($score2 > $score1) {
                       return $results['duel']['opponent_id'];
                   } else {
                       return 'draw'; // Match nul si scores combinés égaux
                   }
               }
           }
           break;
   }
   
   return 'draw'; // Par défaut, match nul
}

/**
* Get popular duels
* 
* @param int $limit Optional limit
* @return array The popular duels
*/
if (!function_exists('getPopularDuels')) {
   function getPopularDuels($limit = 3) {
       $database = new Database();
       $db = $database->connect();
       
       // Cette requête est un exemple - vous devrez l'adapter à votre schéma de base de données
       $query = "SELECT 
                    d.id,
                    CONCAT('Duel ', d.type) as title,
                    d.type,
                    COUNT(DISTINCT dr.user_id) as player_count,
                    AVG(dr.completion_time) as avg_time,
                    'Un duel passionnant pour tester vos connaissances' as description
                 FROM duels d
                 JOIN duel_results dr ON d.id = dr.duel_id
                 WHERE d.status = 'completed'
                 GROUP BY d.id, d.type
                 ORDER BY player_count DESC
                 LIMIT :limit";
                 
       $stmt = $db->prepare($query);
       $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
       $stmt->execute();
       
       $popular_duels = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       // Si aucun résultat, retourner des données d'exemple
       if (empty($popular_duels)) {
           return [
               [
                   'id' => 1,
                   'title' => 'Quiz Culture Générale',
                   'type' => 'mixed',
                   'player_count' => 124,
                   'avg_time' => 45,
                   'description' => 'Un duel mixte avec des questions variées de culture générale. Idéal pour tester vos connaissances !'
               ],
               [
                   'id' => 2,
                   'title' => 'Défi Mathématiques',
                   'type' => 'accuracy',
                   'player_count' => 87,
                   'avg_time' => 60,
                   'description' => 'Testez votre précision en mathématiques avec ce duel spécial. La précision est la clé !'
               ],
               [
                   'id' => 3,
                   'title' => 'Course contre la montre',
                   'type' => 'timed',
                   'player_count' => 156,
                   'avg_time' => 32,
                   'description' => 'Un duel rapide où la vitesse compte autant que les bonnes réponses. Soyez rapide !'
               ]
           ];
       }
       
       return $popular_duels;
   }
}

?>