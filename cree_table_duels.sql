-- Script de création des tables pour le système de duels

-- Table principale des duels
CREATE TABLE IF NOT EXISTS duels (
   id INT AUTO_INCREMENT PRIMARY KEY,
   challenger_id INT NOT NULL,
   opponent_id INT NOT NULL,
   categorie_id INT,
   difficulte_id INT,
   type ENUM('timed', 'accuracy', 'mixed') NOT NULL,
   status ENUM('pending', 'active', 'completed', 'cancelled', 'abandoned') NOT NULL DEFAULT 'pending',
   winner_id INT,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   started_at TIMESTAMP NULL,
   completed_at TIMESTAMP NULL,
   time_limit INT DEFAULT 0,
   question_count INT DEFAULT 10,
   FOREIGN KEY (challenger_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   FOREIGN KEY (opponent_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
   FOREIGN KEY (difficulte_id) REFERENCES difficultes(id) ON DELETE SET NULL,
   FOREIGN KEY (winner_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des invitations aux duels
CREATE TABLE IF NOT EXISTS duel_invitations (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   sender_id INT NOT NULL,
   recipient_id INT NOT NULL,
   message TEXT,
   status ENUM('pending', 'accepted', 'declined', 'expired') NOT NULL DEFAULT 'pending',
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   expires_at TIMESTAMP NULL,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   FOREIGN KEY (recipient_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des résultats des duels
CREATE TABLE IF NOT EXISTS duel_results (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   user_id INT NOT NULL,
   score INT NOT NULL DEFAULT 0,
   correct_answers INT NOT NULL DEFAULT 0,
   total_questions INT NOT NULL DEFAULT 0,
   completion_time INT DEFAULT NULL COMMENT 'Temps en secondes',
   started_at TIMESTAMP NULL,
   completed_at TIMESTAMP NULL,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des questions des duels
CREATE TABLE IF NOT EXISTS duel_questions (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   question_id INT NOT NULL,
   question_order INT NOT NULL,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Table des réponses aux questions des duels
CREATE TABLE IF NOT EXISTS duel_answers (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   user_id INT NOT NULL,
   question_id INT NOT NULL,
   answer_id INT,
   is_correct BOOLEAN NOT NULL DEFAULT 0,
   response_time INT COMMENT 'Temps en millisecondes',
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
   FOREIGN KEY (answer_id) REFERENCES options(id) ON DELETE SET NULL
);

-- Table des signalements de duels
CREATE TABLE IF NOT EXISTS duel_reports (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   reporter_id INT NOT NULL,
   reported_id INT NOT NULL,
   reason ENUM('cheating', 'inappropriate_behavior', 'technical_issue', 'other') NOT NULL,
   description TEXT,
   status ENUM('pending', 'investigating', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   resolved_at TIMESTAMP NULL,
   admin_notes TEXT,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (reporter_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   FOREIGN KEY (reported_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des réalisations dans les duels
CREATE TABLE IF NOT EXISTS duel_achievements (
   id INT AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   achievement_type ENUM('first_win', 'win_streak', 'perfect_score', 'speed_demon', 'comeback_king', 'duel_master') NOT NULL,
   value INT NOT NULL DEFAULT 1,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des participants aux duels (pour stocker des informations supplémentaires)
CREATE TABLE IF NOT EXISTS duel_participants (
   id INT AUTO_INCREMENT PRIMARY KEY,
   duel_id INT NOT NULL,
   user_id INT NOT NULL,
   completion_time INT DEFAULT NULL COMMENT 'Temps en secondes',
   status ENUM('pending', 'active', 'completed', 'abandoned') NOT NULL DEFAULT 'pending',
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY (duel_id) REFERENCES duels(id) ON DELETE CASCADE,
   FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
   UNIQUE KEY (duel_id, user_id)
);

-- Vue pour le classement des duels
CREATE OR REPLACE VIEW duel_leaderboard AS
   SELECT 
       u.id,
       u.nom,
       COUNT(d.id) AS total_duels,
       SUM(CASE WHEN d.winner_id = u.id THEN 1 ELSE 0 END) AS wins,
       SUM(CASE WHEN d.winner_id IS NOT NULL AND d.winner_id != u.id THEN 1 ELSE 0 END) AS losses,
       SUM(CASE WHEN d.winner_id IS NULL AND d.status = 'completed' THEN 1 ELSE 0 END) AS draws,
       ROUND((SUM(CASE WHEN d.winner_id = u.id THEN 1 ELSE 0 END) / COUNT(d.id)) * 100, 2) AS win_percentage,
       AVG(dr.completion_time) AS avg_completion_time,
       AVG(dr.correct_answers / dr.total_questions * 100) AS avg_accuracy,
       MAX(d.completed_at) AS last_duel_date,
       (
           SELECT type
           FROM duels d2
           WHERE (d2.challenger_id = u.id OR d2.opponent_id = u.id)
           AND d2.status = 'completed'
           GROUP BY type
           ORDER BY COUNT(*) DESC
           LIMIT 1
       ) AS preferred_duel_type
   FROM 
       utilisateurs u
   LEFT JOIN 
       duels d ON (d.challenger_id = u.id OR d.opponent_id = u.id) AND d.status = 'completed'
   LEFT JOIN 
       duel_results dr ON dr.user_id = u.id AND dr.duel_id = d.id
   GROUP BY 
       u.id, u.nom
   HAVING 
       total_duels > 0
   ORDER BY 
       wins DESC, win_percentage DESC, avg_accuracy DESC;