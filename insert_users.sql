-- Script pour insérer 20 utilisateurs avec des quiz complétés et des réponses
-- Base de données : quizmaster
USE `quizmaster`;

-- Insertion des utilisateurs
INSERT INTO `utilisateurs` (`est_admin`, `nom`, `email`, `mot_de_passe`, `date_inscription`) VALUES
(1, 'Admin', 'admin@quizmaster.fr', 'admin123_hashed', '2024-10-01 10:00:00'),
(0, 'Jean Dupont', 'jean.dupont@gmail.com', 'password123_hashed', '2024-11-15 14:30:00'),
(0, 'Marie Martin', 'marie.martin@yahoo.fr', 'pass456_hashed', '2024-12-01 09:15:00'),
(0, 'Luc Durand', 'luc.durand@outlook.com', 'luc789_hashed', '2024-10-20 16:45:00'),
(0, 'Sophie Bernard', 'sophie.bernard@gmail.com', 'sophie101_hashed', '2024-11-10 12:00:00'),
(0, 'Thomas Lefevre', 'thomas.lefevre@free.fr', 'thomas202_hashed', '2024-12-05 08:30:00'),
(0, 'Emma Petit', 'emma.petit@hotmail.com', 'emma303_hashed', '2024-10-25 17:20:00'),
(0, 'Paul Robert', 'paul.robert@gmail.com', 'paul404_hashed', '2024-11-30 11:10:00'),
(0, 'Clara Simon', 'clara.simon@orange.fr', 'clara505_hashed', '2024-12-10 13:40:00'),
(0, 'Louis Dubois', 'louis.dubois@yahoo.com', 'louis606_hashed', '2024-10-15 15:50:00'),
(0, 'Anna Richard', 'anna.richard@gmail.com', 'anna707_hashed', '2024-11-20 10:25:00'),
(0, 'Hugo Garcia', 'hugo.garcia@outlook.com', 'hugo808_hashed', '2024-12-15 14:00:00'),
(0, 'Léa Laurent', 'lea.laurent@free.fr', 'lea909_hashed', '2024-10-30 09:35:00'),
(0, 'Maxime Roux', 'maxime.roux@hotmail.com', 'max1010_hashed', '2024-11-25 16:15:00'),
(0, 'Camille Morel', 'camille.morel@gmail.com', 'cam1111_hashed', '2024-12-20 12:50:00'),
(0, 'Antoine Lemoine', 'antoine.lemoine@yahoo.fr', 'ant1212_hashed', '2024-10-10 18:00:00'),
(0, 'Julie Fournier', 'julie.fournier@orange.fr', 'jul1313_hashed', '2024-11-05 11:30:00'),
(0, 'Victor Gauthier', 'victor.gauthier@gmail.com', 'vic1414_hashed', '2024-12-25 10:45:00'),
(0, 'Alice Mercier', 'alice.mercier@free.fr', 'ali1515_hashed', '2024-10-05 13:20:00'),
(0, 'Raphaël Colin', 'raphael.colin@hotmail.com', 'rap1616_hashed', '2024-11-15 15:55:00');

-- Insertion des quiz complétés (chaque utilisateur a 1 à 3 quiz)
-- Admin (ID=1) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(1, 1, 1, 80, 100, '2024-10-02 12:00:00'), -- Histoire, Facile
(1, 2, 2, 60, 100, '2024-10-03 14:30:00'); -- Science, Moyen

-- Jean Dupont (ID=2) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(2, 3, 1, 90, 100, '2024-11-16 10:00:00'), -- Géographie, Facile
(2, 4, 2, 70, 100, '2024-11-17 11:30:00'), -- Arts & Culture, Moyen
(2, 5, 3, 50, 100, '2024-11-18 13:00:00'); -- Sport, Difficile

-- Marie Martin (ID=3) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(3, 6, 1, 85, 100, '2024-12-02 15:00:00'); -- Divers, Facile

-- Luc Durand (ID=4) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(4, 1, 2, 65, 100, '2024-10-21 09:00:00'), -- Histoire, Moyen
(4, 2, 1, 95, 100, '2024-10-22 10:30:00'); -- Science, Facile

-- Sophie Bernard (ID=5) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(5, 3, 2, 75, 100, '2024-11-11 14:00:00'), -- Géographie, Moyen
(5, 4, 1, 80, 100, '2024-11-12 15:30:00'), -- Arts & Culture, Facile
(5, 5, 2, 60, 100, '2024-11-13 17:00:00'); -- Sport, Moyen

-- Thomas Lefevre (ID=6) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(6, 6, 2, 70, 100, '2024-12-06 12:00:00'); -- Divers, Moyen

-- Emma Petit (ID=7) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(7, 1, 3, 55, 100, '2024-10-26 11:00:00'), -- Histoire, Difficile
(7, 2, 1, 90, 100, '2024-10-27 13:30:00'); -- Science, Facile

-- Paul Robert (ID=8) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(8, 3, 1, 85, 100, '2024-12-01 09:00:00'), -- Géographie, Facile
(8, 4, 3, 50, 100, '2024-12-02 10:30:00'), -- Arts & Culture, Difficile
(8, 5, 1, 95, 100, '2024-12-03 12:00:00'); -- Sport, Facile

-- Clara Simon (ID=9) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(9, 6, 3, 60, 100, '2024-12-11 14:00:00'); -- Divers, Difficile

-- Louis Dubois (ID=10) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(10, 1, 1, 80, 100, '2024-10-16 15:00:00'), -- Histoire, Facile
(10, 2, 2, 70, 100, '2024-10-17 16:30:00'); -- Science, Moyen

-- Anna Richard (ID=11) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(11, 3, 3, 65, 100, '2024-11-21 10:00:00'), -- Géographie, Difficile
(11, 4, 1, 90, 100, '2024-11-22 11:30:00'), -- Arts & Culture, Facile
(11, 5, 2, 75, 100, '2024-11-23 13:00:00'); -- Sport, Moyen

-- Hugo Garcia (ID=12) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(12, 6, 1, 85, 100, '2024-12-16 15:00:00'); -- Divers, Facile

-- Léa Laurent (ID=13) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(13, 1, 2, 70, 100, '2024-10-31 09:00:00'), -- Histoire, Moyen
(13, 2, 3, 60, 100, '2024-11-01 10:30:00'); -- Science, Difficile

-- Maxime Roux (ID=14) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(14, 3, 1, 95, 100, '2024-11-26 14:00:00'), -- Géographie, Facile
(14, 4, 2, 80, 100, '2024-11-27 15:30:00'), -- Arts & Culture, Moyen
(14, 5, 3, 55, 100, '2024-11-28 17:00:00'); -- Sport, Difficile

-- Camille Morel (ID=15) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(15, 6, 2, 75, 100, '2024-12-21 12:00:00'); -- Divers, Moyen

-- Antoine Lemoine (ID=16) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(16, 1, 3, 50, 100, '2024-10-11 11:00:00'), -- Histoire, Difficile
(16, 2, 1, 90, 100, '2024-10-12 13:30:00'); -- Science, Facile

-- Julie Fournier (ID=17) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(17, 3, 2, 80, 100, '2024-11-06 09:00:00'), -- Géographie, Moyen
(17, 4, 1, 85, 100, '2024-11-07 10:30:00'), -- Arts & Culture, Facile
(17, 5, 2, 70, 100, '2024-11-08 12:00:00'); -- Sport, Moyen

-- Victor Gauthier (ID=18) a complété 1 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(18, 6, 3, 60, 100, '2024-12-26 14:00:00'); -- Divers, Difficile

-- Alice Mercier (ID=19) a complété 2 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(19, 1, 1, 90, 100, '2024-10-06 15:00:00'), -- Histoire, Facile
(19, 2, 2, 75, 100, '2024-10-07 16:30:00'); -- Science, Moyen

-- Raphaël Colin (ID=20) a complété 3 quiz
INSERT INTO `quiz_completes` (`utilisateur_id`, `categorie_id`, `difficulte_id`, `score`, `total`, `date_completion`) VALUES
(20, 3, 3, 65, 100, '2024-11-16 10:00:00'), -- Géographie, Difficile
(20, 4, 1, 95, 100, '2024-11-17 11:30:00'), -- Arts & Culture, Facile
(20, 5, 2, 80, 100, '2024-11-18 13:00:00'); -- Sport, Moyen

-- Insertion des réponses des utilisateurs (exemple pour quelques quiz)
-- On associe des réponses à des questions existantes dans la base
-- Quiz 1 de Admin (ID=1, quiz_complete_id=1, catégorie Histoire, Facile)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(1, 1, 1), -- Question: Révolution française, Réponse: 1789 (correcte)
(1, 2, 6), -- Question: Premier président Ve République, Réponse: Charles de Gaulle (correcte)
(1, 3, 11); -- Question: Bataille de Napoléon, Réponse: Waterloo (correcte)

-- Quiz 1 de Jean Dupont (ID=2, quiz_complete_id=3, catégorie Géographie, Facile)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(3, 12, 49), -- Question: Capitale de la France, Réponse: Paris (correcte)
(3, 28, 113), -- Question: Plus long fleuve de France, Réponse: La Loire (correcte)
(3, 29, 117); -- Question: Capitale de la Bretagne, Réponse: Rennes (correcte)

-- Quiz 1 de Marie Martin (ID=3, quiz_complete_id=6, catégorie Divers, Facile)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(6, 24, 97), -- Question: Monnaie du Japon, Réponse: Le yen (correcte)
(6, 35, 141), -- Question: Devise de la République française, Réponse: Liberté, Égalité, Fraternité (correcte)
(6, 36, 145); -- Question: Roi des fromages, Réponse: Le Camembert (correcte)

-- Quiz 1 de Luc Durand (ID=4, quiz_complete_id=7, catégorie Histoire, Moyen)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(7, 4, 13), -- Question: Traité Première Guerre mondiale, Réponse: Versailles (correcte)
(7, 5, 18), -- Question: Gouvernement de Vichy, Réponse: Philippe Pétain (correcte)
(7, 25, 101); -- Question: Bataille de Verdun, Réponse: 1916 (correcte)

-- Quiz 1 de Sophie Bernard (ID=5, quiz_complete_id=9, catégorie Géographie, Moyen)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(9, 13, 53), -- Question: Plus grande île Méditerranée, Réponse: Sicile (correcte)
(9, 30, 121), -- Question: Plus haut sommet France, Réponse: Mont Blanc (correcte)
(9, 31, 125); -- Question: Département 64, Réponse: Pyrénées-Atlantiques (correcte)

-- Ajout de quelques réponses supplémentaires pour d'autres utilisateurs (exemple)
-- Quiz 1 de Thomas Lefevre (ID=6, quiz_complete_id=12, catégorie Divers, Moyen)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(12, 25, 101), -- Question: Plus grand océan, Réponse: Pacifique (correcte)
(12, 37, 149), -- Question: Plus vieux monument Paris, Réponse: Obélisque de la Concorde (correcte)
(12, 38, 153); -- Question: Plus grand parc d'attractions, Réponse: Disneyland Paris (correcte)

-- Quiz 1 de Emma Petit (ID=7, quiz_complete_id=13, catégorie Histoire, Difficile)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(13, 6, 21), -- Question: Chef gouvernement 1944, Réponse: Charles de Gaulle (correcte)
(13, 7, 26), -- Question: Traité CECA, Réponse: Paris (correcte)
(13, 26, 105); -- Question: Crise de mai 1958, Réponse: Pierre Pflimlin (correcte)

-- Quiz 1 de Paul Robert (ID=8, quiz_complete_id=16, catégorie Géographie, Facile)
INSERT INTO `reponses_utilisateurs` (`quiz_complete_id`, `question_id`, `option_id`) VALUES
(16, 12, 49), -- Question: Capitale de la France, Réponse: Paris (correcte)
(16, 28, 113), -- Question: Plus long fleuve France, Réponse: La Loire (correcte)
(16, 29, 117); -- Question: Capitale de la Bretagne, Réponse: Rennes (correcte)

-- Notifications pour quelques utilisateurs (exemple)
INSERT INTO `notifications` (`utilisateur_id`, `type`, `message`, `related_id`, `is_read`, `created_at`) VALUES
(1, 'admin_message', 'Bienvenue sur QuizMaster, Admin!', NULL, 0, '2024-10-01 10:05:00'),
(2, 'high_score', 'Félicitations, vous avez obtenu 90% en Géographie (Facile)!', 3, 0, '2024-11-16 10:05:00'),
(3, 'new_quiz', 'Vous avez complété un nouveau quiz en Divers!', 6, 0, '2024-12-02 15:05:00'),
(5, 'high_score', 'Super score de 80% en Arts & Culture (Facile)!', 10, 0, '2024-11-12 15:35:00'),
(8, 'new_quiz', 'Vous avez complété un quiz en Géographie!', 16, 0, '2024-12-01 09:05:00');


-- Insertion de question 

-- Insertion des données de base
-- Catégories
INSERT INTO `categories` (`nom`, `description`, `icone`, `couleur`) VALUES
('Histoire', 'Questions sur les événements historiques, les personnages et les périodes importantes', 'fa-book', '#ef4444'),
('Science', 'Questions sur la physique, la chimie, la biologie et les découvertes scientifiques', 'fa-flask', '#3b82f6'),
('Géographie', 'Questions sur les pays, les capitales, les fleuves et les montagnes', 'fa-globe', '#22c55e'),
('Arts & Culture', 'Questions sur la littérature, la peinture, la musique et le cinéma', 'fa-palette', '#a855f7'),
('Sport', 'Questions sur les compétitions sportives, les athlètes et les records', 'fa-trophy', '#eab308'),
('Divers', 'Questions variées sur différents sujets de culture générale', 'fa-star', '#ec4899');

-- Niveaux de difficulté
INSERT INTO `difficultes` (`nom`) VALUES
('Facile'),
('Moyen'),
('Difficile');

-- Insertion de questions d'exemple pour la catégorie Histoire
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a eu lieu la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1789', 1),
(@last_id, '1799', 0),
(@last_id, '1769', 0),
(@last_id, '1809', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui était le premier président de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'François Mitterrand', 0),
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Valéry Giscard d''Estaing', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle bataille a marqué la fin de l''Empire de Napoléon Bonaparte?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille d''Austerlitz', 0),
(@last_id, 'La bataille de Marengo', 0),
(@last_id, 'La bataille de Waterloo', 1),
(@last_id, 'La bataille de Wagram', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel traité a mis fin à la Première Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Versailles', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Vienne', 0),
(@last_id, 'Le traité de Rome', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a dirigé le gouvernement de Vichy pendant la Seconde Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 0),
(@last_id, 'Philippe Pétain', 1),
(@last_id, 'Pierre Laval', 0),
(@last_id, 'François Darlan', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui était le chef du gouvernement provisoire de la République française en 1944?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Bidault', 0),
(@last_id, 'Félix Gouin', 0),
(@last_id, 'Vincent Auriol', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel était le nom du traité qui a créé la Communauté européenne du charbon et de l''acier (CECA)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Rome', 0),
(@last_id, 'Le traité de Maastricht', 0),
(@last_id, 'Le traité de Paris', 1),
(@last_id, 'Le traité de Lisbonne', 0);

-- Insertion de questions d'exemple pour la catégorie Science
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle est la formule chimique de l''eau?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'H2O', 1),
(@last_id, 'CO2', 0),
(@last_id, 'O2', 0),
(@last_id, 'H2O2', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l''élément chimique le plus abondant dans l''univers?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Oxygène', 0),
(@last_id, 'Carbone', 0),
(@last_id, 'Hydrogène', 1),
(@last_id, 'Hélium', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la vitesse approximative de la lumière dans le vide?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '300 000 km/s', 1),
(@last_id, '150 000 km/s', 0),
(@last_id, '500 000 km/s', 0),
(@last_id, '1 000 000 km/s', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quelle est la constante de Planck (approximative)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '6,63 × 10^-34 J·s', 1),
(@last_id, '9,81 m/s²', 0),
(@last_id, '3,14159', 0),
(@last_id, '1,38 × 10^-23 J/K', 0);

-- Insertion de questions d'exemple pour la catégorie Géographie
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la capitale de la France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Lyon', 0),
(@last_id, 'Marseille', 0),
(@last_id, 'Paris', 1),
(@last_id, 'Bordeaux', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle est la plus grande île de la Méditerranée?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Corse', 0),
(@last_id, 'La Sardaigne', 0),
(@last_id, 'La Sicile', 1),
(@last_id, 'Chypre', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la capitale du Kirghizistan?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Astana', 0),
(@last_id, 'Tachkent', 0),
(@last_id, 'Bichkek', 1),
(@last_id, 'Douchanbé', 0);

-- Insertion de questions d'exemple pour la catégorie Arts & Culture
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Qui a peint ''La Joconde''?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vincent van Gogh', 0),
(@last_id, 'Pablo Picasso', 0),
(@last_id, 'Léonard de Vinci', 1),
(@last_id, 'Claude Monet', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Qui a composé l''opéra ''Carmen''?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Giuseppe Verdi', 0),
(@last_id, 'Georges Bizet', 1),
(@last_id, 'Richard Wagner', 0),
(@last_id, 'Giacomo Puccini', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Qui a peint ''Les Demoiselles d''Avignon''?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Henri Matisse', 0),
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Georges Braque', 0),
(@last_id, 'Salvador Dalí', 0);

-- Insertion de questions d'exemple pour la catégorie Sport
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'En quelle année la France a-t-elle remporté sa première Coupe du Monde de football?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1998', 1),
(@last_id, '2002', 0),
(@last_id, '1994', 0),
(@last_id, '2006', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Qui détient le record du monde du 100 mètres?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Carl Lewis', 0),
(@last_id, 'Usain Bolt', 1),
(@last_id, 'Justin Gatlin', 0),
(@last_id, 'Asafa Powell', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Qui est le seul joueur de football à avoir remporté trois Coupes du Monde en tant que joueur?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pelé', 1),
(@last_id, 'Diego Maradona', 0),
(@last_id, 'Zinedine Zidane', 0),
(@last_id, 'Franz Beckenbauer', 0);

-- Insertion de questions d'exemple pour la catégorie Divers
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le nom de la monnaie utilisée au Japon?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le yuan', 0),
(@last_id, 'Le won', 0),
(@last_id, 'Le yen', 1),
(@last_id, 'Le ringgit', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le nom du plus grand océan du monde?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''océan Atlantique', 0),
(@last_id, 'L''océan Indien', 0),
(@last_id, 'L''océan Pacifique', 1),
(@last_id, 'L''océan Arctique', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quel est le nom du théorème qui établit que dans un triangle rectangle, le carré de l''hypoténuse est égal à la somme des carrés des deux autres côtés?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le théorème de Thalès', 0),
(@last_id, 'Le théorème de Pythagore', 1),
(@last_id, 'Le théorème de Fermat', 0),
(@last_id, 'Le théorème d''Euclide', 0);

-- Badges
INSERT INTO `badges` (`nom`, `description`, `categorie_id`, `difficulte_id`, `condition_score`, `condition_nombre_quiz`) VALUES
('Débutant en Histoire', 'Complétez 3 quiz d''Histoire avec un score d''au moins 60%', 1, NULL, 60, 3),
('Expert en Sciences', 'Obtenez un score parfait dans un quiz de Science difficile', 2, 3, 100, 1),
('Explorateur de Géographie', 'Complétez des quiz dans toutes les difficultés de Géographie', 3, NULL, NULL, 3),
('Amateur d''Arts', 'Complétez 5 quiz d''Arts & Culture', 4, NULL, NULL, 5),
('Champion Sportif', 'Obtenez un score moyen de 80% dans les quiz de Sport', 5, NULL, 80, 3),
('Polyvalent', 'Complétez au moins un quiz dans chaque catégorie', NULL, NULL, NULL, 6),
('Maître du Savoir', 'Obtenez un score parfait dans un quiz difficile de chaque catégorie', NULL, 3, 100, 6);

-- Histoire
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel roi de France est surnommé ''le Roi Soleil'' ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XVI', 0),
(@last_id, 'François Ier', 0),
(@last_id, 'Henri IV', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel événement a déclenché la guerre de Cent Ans entre la France et l''Angleterre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La succession au trône de France après Charles IV', 1),
(@last_id, 'La bataille de Crécy', 0),
(@last_id, 'La révolte des paysans anglais', 0),
(@last_id, 'Le traité de Troyes', 0);

-- Science
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel gaz est essentiel à la respiration humaine ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Oxygène', 1),
(@last_id, 'Azote', 0),
(@last_id, 'Dioxyde de carbone', 0),
(@last_id, 'Hélium', 0);

-- Ajout de questions supplémentaires par thème

-- HISTOIRE (Catégorie 1)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel roi de France était surnommé "le Roi Soleil"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XVI', 0),
(@last_id, 'François Ier', 0),
(@last_id, 'Henri IV', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle bataille Napoléon a-t-il perdue en 1815?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Waterloo', 1),
(@last_id, 'Austerlitz', 0),
(@last_id, 'Wagram', 0),
(@last_id, 'Iéna', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'En quelle année a eu lieu la bataille de Verdun?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1916', 1),
(@last_id, '1914', 0),
(@last_id, '1918', 0),
(@last_id, '1915', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi a signé l''Édit de Nantes en 1598?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Henri IV', 1),
(@last_id, 'Louis XIII', 0),
(@last_id, 'François Ier', 0),
(@last_id, 'Louis XIV', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui était le président du Conseil au moment de la crise de mai 1958?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pierre Pflimlin', 1),
(@last_id, 'Guy Mollet', 0),
(@last_id, 'Félix Gaillard', 0),
(@last_id, 'Michel Debré', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle loi de 1905 a séparé les Églises et l''État en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La loi de séparation des Églises et de l''État', 1),
(@last_id, 'La loi Ferry', 0),
(@last_id, 'La loi Falloux', 0),
(@last_id, 'La loi Combes', 0);

-- SCIENCE (Catégorie 2)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l''élément chimique le plus abondant dans l''Univers?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Hydrogène', 1),
(@last_id, 'Oxygène', 0),
(@last_id, 'Carbone', 0),
(@last_id, 'Hélium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l''organe responsable de la production d''insuline?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le pancréas', 1),
(@last_id, 'Le foie', 0),
(@last_id, 'Les reins', 0),
(@last_id, 'La rate', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la vitesse de la lumière dans le vide?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '299 792 458 m/s', 1),
(@last_id, '300 000 000 m/s', 0),
(@last_id, '150 000 000 m/s', 0),
(@last_id, '200 000 000 m/s', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel scientifique français a découvert la radioactivité?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Henri Becquerel', 1),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Louis Pasteur', 0),
(@last_id, 'Pierre Curie', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quelle est la constante de Planck (approximative)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '6,63 × 10^(-34) J·s', 1),
(@last_id, '9,81 × 10^(-34) J·s', 0),
(@last_id, '3,14 × 10^(-34) J·s', 0),
(@last_id, '1,38 × 10^(-34) J·s', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel physicien français a formulé le principe d''incertitude en mécanique quantique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Werner Heisenberg', 1),
(@last_id, 'Louis de Broglie', 0),
(@last_id, 'Niels Bohr', 0),
(@last_id, 'Max Planck', 0);

-- GÉOGRAPHIE (Catégorie 3)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel est le plus long fleuve de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Loire', 1),
(@last_id, 'La Seine', 0),
(@last_id, 'Le Rhône', 0),
(@last_id, 'La Garonne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la capitale de la Bretagne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Rennes', 1),
(@last_id, 'Nantes', 0),
(@last_id, 'Brest', 0),
(@last_id, 'Quimper', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le plus haut sommet de la France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Mont Blanc', 1),
(@last_id, 'Le Pic du Midi', 0),
(@last_id, 'L''Aiguille du Midi', 0),
(@last_id, 'Le Mont Ventoux', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel département français a pour numéro 64?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pyrénées-Atlantiques', 1),
(@last_id, 'Pyrénées-Orientales', 0),
(@last_id, 'Hautes-Pyrénées', 0),
(@last_id, 'Landes', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le plus petit département français en superficie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Hauts-de-Seine', 0),
(@last_id, 'Val-de-Marne', 0),
(@last_id, 'Seine-Saint-Denis', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle île française se trouve au large de Madagascar?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Réunion', 1),
(@last_id, 'Mayotte', 0),
(@last_id, 'La Martinique', 0),
(@last_id, 'La Guadeloupe', 0);

-- ARTS & CULTURE (Catégorie 4)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Qui a peint "La Joconde"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Léonard de Vinci', 1),
(@last_id, 'Pablo Picasso', 0),
(@last_id, 'Claude Monet', 0),
(@last_id, 'Vincent van Gogh', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Qui a écrit "Les Misérables"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Victor Hugo', 1),
(@last_id, 'Émile Zola', 0),
(@last_id, 'Gustave Flaubert', 0),
(@last_id, 'Alexandre Dumas', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel mouvement artistique a été fondé par André Breton?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le surréalisme', 1),
(@last_id, 'Le cubisme', 0),
(@last_id, 'L''impressionnisme', 0),
(@last_id, 'Le dadaïsme', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Qui a réalisé le film "Les 400 coups"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'François Truffaut', 1),
(@last_id, 'Jean-Luc Godard', 0),
(@last_id, 'Claude Chabrol', 0),
(@last_id, 'Éric Rohmer', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Qui a composé l''opéra "Pelléas et Mélisande"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Claude Debussy', 1),
(@last_id, 'Maurice Ravel', 0),
(@last_id, 'Camille Saint-Saëns', 0),
(@last_id, 'Hector Berlioz', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel poète français a écrit "Les Fleurs du mal"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles Baudelaire', 1),
(@last_id, 'Arthur Rimbaud', 0),
(@last_id, 'Paul Verlaine', 0),
(@last_id, 'Stéphane Mallarmé', 0);

-- SPORT (Catégorie 5)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'En quelle année la France a-t-elle remporté sa première Coupe du monde de football?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1998', 1),
(@last_id, '2018', 0),
(@last_id, '1994', 0),
(@last_id, '2002', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel sport pratique Tony Parker?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Basketball', 1),
(@last_id, 'Football', 0),
(@last_id, 'Tennis', 0),
(@last_id, 'Rugby', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Combien de fois la France a-t-elle organisé les Jeux Olympiques d''été?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '2 fois (1900 et 1924)', 1),
(@last_id, '1 fois (1924)', 0),
(@last_id, '3 fois (1900, 1924 et 1968)', 0),
(@last_id, 'Jamais', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel cycliste français a remporté 5 fois le Tour de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Bernard Hinault', 1),
(@last_id, 'Jacques Anquetil', 0),
(@last_id, 'Laurent Fignon', 0),
(@last_id, 'Raymond Poulidor', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Qui détient le record de France du 100 mètres masculin?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jimmy Vicaut', 1),
(@last_id, 'Christophe Lemaitre', 0),
(@last_id, 'Ronald Pognon', 0),
(@last_id, 'Pascal Barré', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'En quelle année a été créé le championnat de France de football professionnel?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1932', 1),
(@last_id, '1919', 0),
(@last_id, '1945', 0),
(@last_id, '1958', 0);

-- DIVERS (Catégorie 6)
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quelle est la devise de la République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Liberté, Égalité, Fraternité', 1),
(@last_id, 'L''union fait la force', 0),
(@last_id, 'Un pour tous, tous pour un', 0),
(@last_id, 'Travail, Famille, Patrie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel fromage français est surnommé "le roi des fromages"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Camembert', 1),
(@last_id, 'Le Roquefort', 0),
(@last_id, 'Le Comté', 0),
(@last_id, 'Le Brie', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le plus vieux monument de Paris encore debout?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''obélisque de la Concorde', 1),
(@last_id, 'Notre-Dame de Paris', 0),
(@last_id, 'La tour Eiffel', 0),
(@last_id, 'L''Arc de Triomphe', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le plus grand parc d''attractions d''Europe?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Disneyland Paris', 1),
(@last_id, 'Europa-Park', 0),
(@last_id, 'Parc Astérix', 0),
(@last_id, 'PortAventura', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quelle est la plus ancienne université française encore en activité?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Université de Paris (Sorbonne)', 1),
(@last_id, 'Université de Montpellier', 0),
(@last_id, 'Université de Toulouse', 0),
(@last_id, 'Université d''Orléans', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quel écrivain a créé le personnage du Commissaire Maigret?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Georges Simenon', 1),
(@last_id, 'Fred Vargas', 0),
(@last_id, 'Jean-Patrick Manchette', 0),
(@last_id, 'Gaston Leroux', 0);

-- Ajout de questions supplémentaires (10 par thème)

-- HISTOIRE (Catégorie 1) - 10 questions supplémentaires
-- Facile (4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a débuté la Seconde Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1939', 1),
(@last_id, '1940', 0),
(@last_id, '1938', 0),
(@last_id, '1941', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a été le premier président de la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vincent Auriol', 1),
(@last_id, 'René Coty', 0),
(@last_id, 'Charles de Gaulle', 0),
(@last_id, 'Albert Lebrun', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle reine de France était surnommée "l''Autrichienne"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marie-Antoinette', 1),
(@last_id, 'Marie de Médicis', 0),
(@last_id, 'Joséphine de Beauharnais', 0),
(@last_id, 'Anne d''Autriche', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a proclamé l''Empire français en 1804?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Robespierre', 0),
(@last_id, 'Talleyrand', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle était la date précise de la prise de la Bastille?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '14 juillet 1789', 1),
(@last_id, '4 juillet 1789', 0),
(@last_id, '14 juillet 1790', 0),
(@last_id, '20 juin 1789', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi de France fut surnommé "le Bien-Aimé"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XV', 1),
(@last_id, 'Louis XIV', 0),
(@last_id, 'Louis XVI', 0),
(@last_id, 'Henri IV', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Sous quel roi a-t-on construit le château de Versailles?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XVI', 0),
(@last_id, 'Henri IV', 0),
(@last_id, 'François Ier', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a été signée l''ordonnance de Villers-Cotterêts imposant le français comme langue administrative?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1539', 1),
(@last_id, '1492', 0),
(@last_id, '1610', 0),
(@last_id, '1453', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui était le Premier ministre français lors de la crise des missiles de Cuba?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Georges Pompidou', 1),
(@last_id, 'Michel Debré', 0),
(@last_id, 'Maurice Couve de Murville', 0),
(@last_id, 'Jacques Chaban-Delmas', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel événement historique s''est déroulé le 9 Thermidor an II (27 juillet 1794)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La chute de Robespierre', 1),
(@last_id, 'La prise des Tuileries', 0),
(@last_id, 'L''exécution de Louis XVI', 0),
(@last_id, 'Le coup d''État de Bonaparte', 0);

-- SCIENCE (Catégorie 2) - 10 questions supplémentaires
-- Facile (4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Qui a découvert la pénicilline?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Alexander Fleming', 1),
(@last_id, 'Louis Pasteur', 0),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Edward Jenner', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel scientifique français a inventé le vaccin contre la rage?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis Pasteur', 1),
(@last_id, 'Claude Bernard', 0),
(@last_id, 'Antoine Lavoisier', 0),
(@last_id, 'Pierre et Marie Curie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Comment s''appelle la planète la plus proche du Soleil?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mercure', 1),
(@last_id, 'Vénus', 0),
(@last_id, 'Mars', 0),
(@last_id, 'Jupiter', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle est la formule chimique du dioxyde de carbone?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'CO2', 1),
(@last_id, 'H2O', 0),
(@last_id, 'O3', 0),
(@last_id, 'CH4', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle particule élémentaire a une charge positive?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le proton', 1),
(@last_id, 'L''électron', 0),
(@last_id, 'Le neutron', 0),
(@last_id, 'Le photon', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel astronome français a calculé la circonférence de la Terre en 1669?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean Picard', 1),
(@last_id, 'Pierre-Simon Laplace', 0),
(@last_id, 'Jean-Dominique Cassini', 0),
(@last_id, 'Jérôme Lalande', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel mathématicien français a énoncé le "dernier théorème de Fermat"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pierre de Fermat', 1),
(@last_id, 'René Descartes', 0),
(@last_id, 'Blaise Pascal', 0),
(@last_id, 'Henri Poincaré', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel physicien français a découvert l''effet photoélectrique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Antoine Henri Becquerel', 1),
(@last_id, 'Albert Einstein', 0),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Niels Bohr', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quelle est la valeur approximative du nombre d''Avogadro?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '6,022 × 10^23', 1),
(@last_id, '3,14 × 10^23', 0),
(@last_id, '9,81 × 10^23', 0),
(@last_id, '1,602 × 10^23', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Qui a élaboré la classification périodique des éléments chimiques?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Dmitri Mendeleïev', 1),
(@last_id, 'Antoine Lavoisier', 0),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Ernest Rutherford', 0);

-- GÉOGRAPHIE (Catégorie 3) - 10 questions supplémentaires
-- Facile (4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la plus grande ville de France après Paris?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marseille', 1),
(@last_id, 'Lyon', 0),
(@last_id, 'Toulouse', 0),
(@last_id, 'Nice', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Dans quelle région se trouve la ville de Bordeaux?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Nouvelle-Aquitaine', 1),
(@last_id, 'Occitanie', 0),
(@last_id, 'Pays de la Loire', 0),
(@last_id, 'Centre-Val de Loire', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel est le plus grand lac naturel de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le lac du Bourget', 1),
(@last_id, 'Le lac d''Annecy', 0),
(@last_id, 'Le lac de Serre-Ponçon', 0),
(@last_id, 'Le lac Léman', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Avec combien de pays la France métropolitaine partage-t-elle des frontières terrestres?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '8', 1),
(@last_id, '6', 0),
(@last_id, '7', 0),
(@last_id, '9', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le plus long fleuve entièrement français?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Loire', 1),
(@last_id, 'La Seine', 0),
(@last_id, 'La Garonne', 0),
(@last_id, 'Le Rhône', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Dans quel département se trouve le Mont Saint-Michel?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Manche', 1),
(@last_id, 'L''Ille-et-Vilaine', 0),
(@last_id, 'Le Calvados', 0),
(@last_id, 'La Mayenne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle chaîne de montagnes sépare la France de l''Espagne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les Pyrénées', 1),
(@last_id, 'Les Alpes', 0),
(@last_id, 'Le Massif central', 0),
(@last_id, 'Les Vosges', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le point culminant de la Corse?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Monte Cinto', 1),
(@last_id, 'Le Monte Rotondo', 0),
(@last_id, 'Le Monte d''Oro', 0),
(@last_id, 'Le Cap Corse', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la région la moins peuplée de France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Corse', 1),
(@last_id, 'La Bourgogne-Franche-Comté', 0),
(@last_id, 'Le Centre-Val de Loire', 0),
(@last_id, 'La Normandie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la longueur exacte du littoral français métropolitain?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Environ 5 500 km', 1),
(@last_id, 'Environ 3 500 km', 0),
(@last_id, 'Environ 7 000 km', 0),
(@last_id, 'Environ 4 200 km', 0);

-- ARTS & CULTURE (Catégorie 4) - 10 questions supplémentaires
-- Facile (4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel célèbre peintre français a réalisé "Le Déjeuner sur l''herbe"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Édouard Manet', 1),
(@last_id, 'Claude Monet', 0),
(@last_id, 'Auguste Renoir', 0),
(@last_id, 'Paul Cézanne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel écrivain français a écrit "Le Petit Prince"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Antoine de Saint-Exupéry', 1),
(@last_id, 'Jules Verne', 0),
(@last_id, 'Albert Camus', 0),
(@last_id, 'Marcel Proust', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel est le musée le plus visité de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Louvre', 1),
(@last_id, 'Le Musée d''Orsay', 0),
(@last_id, 'Le Centre Pompidou', 0),
(@last_id, 'Le Musée du quai Branly', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel auteur français a écrit "Les Trois Mousquetaires"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Alexandre Dumas', 1),
(@last_id, 'Victor Hugo', 0),
(@last_id, 'Gustave Flaubert', 0),
(@last_id, 'Honoré de Balzac', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel réalisateur français a tourné "Le Mépris" avec Brigitte Bardot?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean-Luc Godard', 1),
(@last_id, 'François Truffaut', 0),
(@last_id, 'Claude Chabrol', 0),
(@last_id, 'Alain Resnais', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel sculpteur français a réalisé "Le Penseur"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Auguste Rodin', 1),
(@last_id, 'Edgar Degas', 0),
(@last_id, 'Antoine Bourdelle', 0),
(@last_id, 'Camille Claudel', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel mouvement artistique fondé en France a pour principe la représentation fidèle de la nature?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le réalisme', 1),
(@last_id, 'L''impressionnisme', 0),
(@last_id, 'Le romantisme', 0),
(@last_id, 'Le surréalisme', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel écrivain français a obtenu le Prix Nobel de littérature en 1957?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Albert Camus', 1),
(@last_id, 'Jean-Paul Sartre', 0),
(@last_id, 'André Malraux', 0),
(@last_id, 'Simone de Beauvoir', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Qui a composé l''opéra "Carmen"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Georges Bizet', 1),
(@last_id, 'Jacques Offenbach', 0),
(@last_id, 'Hector Berlioz', 0),
(@last_id, 'Charles Gounod', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel architecte a conçu la pyramide du Louvre?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Ieoh Ming Pei', 1),
(@last_id, 'Jean Nouvel', 0),
(@last_id, 'Renzo Piano', 0),
(@last_id, 'Le Corbusier', 0);

-- SPORT (Catégorie 5) - 10 questions supplémentaires
-- Facile (4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel joueur de football français a marqué deux buts en finale de la Coupe du Monde 1998?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Zinédine Zidane', 1),
(@last_id, 'Thierry Henry', 0),
(@last_id, 'Emmanuel Petit', 0),
(@last_id, 'David Trezeguet', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Dans quelle ville se déroule le tournoi de tennis Roland-Garros?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Marseille', 0),
(@last_id, 'Lyon', 0),
(@last_id, 'Bordeaux', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel sport pratique Teddy Riner?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le judo', 1),
(@last_id, 'La boxe', 0),
(@last_id, 'Le taekwondo', 0),
(@last_id, 'La lutte', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quelle ville française a accueilli les Jeux Olympiques d''hiver en 1992?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Albertville', 1),
(@last_id, 'Chamonix', 0),
(@last_id, 'Grenoble', 0),
(@last_id, 'Val d''Isère', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quelle équipe de football a remporté le plus de fois le championnat de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'AS Saint-Étienne', 1),
(@last_id, 'Paris Saint-Germain', 0),
(@last_id, 'Olympique de Marseille', 0),
(@last_id, 'Olympique Lyonnais', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel nageur français a remporté 5 médailles dont 4 or aux JO de Londres en 2012?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Florent Manaudou', 0),
(@last_id, 'Yannick Agnel', 0),
(@last_id, 'Camille Lacourt', 0),
(@last_id, 'Alain Bernard', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quelle mythique course cycliste française a été créée en 1903?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Tour de France', 1),
(@last_id, 'Paris-Roubaix', 0),
(@last_id, 'Le Critérium du Dauphiné', 0),
(@last_id, 'Paris-Nice', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Qui est le meilleur buteur de l''histoire de l''équipe de France de football?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Olivier Giroud', 1),
(@last_id, 'Thierry Henry', 0),
(@last_id, 'Michel Platini', 0),
(@last_id, 'Karim Benzema', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'En quelle année la France a-t-elle organisé pour la première fois les Jeux Olympiques?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1900', 1),
(@last_id, '1924', 0),
(@last_id, '1968', 0),
(@last_id, '1992', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel est le record de France du marathon masculin (en 2023)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '2h 5min 11s', 1),
(@last_id, '2h 6min 36s', 0),
(@last_id, '2h 4min 48s', 0),
(@last_id, '2h 7min 23s', 0);

-- Suite des questions pour DIVERS (Catégorie 6)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quelle est la spécialité culinaire de la ville de Marseille?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bouillabaisse', 1),
(@last_id, 'La choucroute', 0),
(@last_id, 'Le cassoulet', 0),
(@last_id, 'La quiche lorraine', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l''emblème figurant sur le drapeau national français?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Aucun emblème (drapeau tricolore uniquement)', 1),
(@last_id, 'Un coq', 0),
(@last_id, 'Une fleur de lys', 0),
(@last_id, 'Un aigle', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quelle est la plus ancienne marque de voiture française encore existante?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Peugeot', 1),
(@last_id, 'Renault', 0),
(@last_id, 'Citroën', 0),
(@last_id, 'Bugatti', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plat traditionnel du Nouvel An chinois en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les raviolis chinois', 1),
(@last_id, 'Le canard laqué', 0),
(@last_id, 'Le riz cantonais', 0),
(@last_id, 'Les nouilles sautées', 0);

-- Moyen (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le plus ancien café de Paris encore en activité?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Procope', 1),
(@last_id, 'Les Deux Magots', 0),
(@last_id, 'Café de Flore', 0),
(@last_id, 'La Closerie des Lilas', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quelle est la spécialité du village de Grasse?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les parfums', 1),
(@last_id, 'Les fruits confits', 0),
(@last_id, 'La lavande', 0),
(@last_id, 'Les savons', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Lequel de ces fromages français n''a pas d''AOC (Appellation d''Origine Contrôlée)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Kiri', 1),
(@last_id, 'Le Comté', 0),
(@last_id, 'Le Roquefort', 0),
(@last_id, 'Le Camembert de Normandie', 0);

-- Difficile (3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quel est le plus ancien restaurant de Paris encore en activité?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Tour d''Argent', 1),
(@last_id, 'Le Grand Véfour', 0),
(@last_id, 'Lapérouse', 0),
(@last_id, 'Le Procope', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quelle est la capacité exacte du Stade de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '80 698 places', 1),
(@last_id, '75 000 places', 0),
(@last_id, '85 000 places', 0),
(@last_id, '78 338 places', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quel célèbre couturier français a créé le "New Look" en 1947?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Christian Dior', 1),
(@last_id, 'Coco Chanel', 0),
(@last_id, 'Yves Saint Laurent', 0),
(@last_id, 'Pierre Balmain', 0);

-- Ajouter encore plus de questions pour équilibrer les catégories

-- Ajout de questions supplémentaires pour HISTOIRE (Catégorie 1)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel célèbre général français est devenu empereur?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Charles de Gaulle', 0),
(@last_id, 'Maréchal Pétain', 0),
(@last_id, 'Louis-Napoléon Bonaparte', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle bataille napoléonienne s''est déroulée le 2 décembre 1805?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Austerlitz', 1),
(@last_id, 'Waterloo', 0),
(@last_id, 'Iéna', 0),
(@last_id, 'Wagram', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel traité a mis fin à la guerre de Cent Ans?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Picquigny', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Troyes', 0),
(@last_id, 'Le traité de Brétigny', 0);

-- Ajout de questions supplémentaires pour SCIENCE (Catégorie 2)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle planète est connue comme la planète rouge?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mars', 1),
(@last_id, 'Vénus', 0),
(@last_id, 'Jupiter', 0),
(@last_id, 'Mercure', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel physicien français a développé la théorie des quanta de lumière?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis de Broglie', 1),
(@last_id, 'Pierre Curie', 0),
(@last_id, 'Henri Becquerel', 0),
(@last_id, 'Paul Langevin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel est le nom du premier ordinateur électronique français construit en 1955?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'GAMMA 3', 1),
(@last_id, 'ENIAC', 0),
(@last_id, 'MINITEL', 0),
(@last_id, 'ANTARES', 0);

-- Ajout de questions supplémentaires pour GÉOGRAPHIE (Catégorie 3)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la capitale de la Normandie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Rouen', 1),
(@last_id, 'Caen', 0),
(@last_id, 'Le Havre', 0),
(@last_id, 'Cherbourg', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le plus petit département français en termes de population?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Lozère', 1),
(@last_id, 'La Creuse', 0),
(@last_id, 'Les Hautes-Alpes', 0),
(@last_id, 'L''Ariège', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Sur quelle rivière se trouve le Pont du Gard?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Gardon', 1),
(@last_id, 'L''Hérault', 0),
(@last_id, 'Le Vidourle', 0),
(@last_id, 'Le Tarn', 0);

-- Ajout de questions supplémentaires pour ARTS & CULTURE (Catégorie 4)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel peintre français est connu pour ses "Nymphéas"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Claude Monet', 1),
(@last_id, 'Paul Cézanne', 0),
(@last_id, 'Edgar Degas', 0),
(@last_id, 'Pierre-Auguste Renoir', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel film français a remporté la Palme d''Or au Festival de Cannes en 2008?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Entre les murs', 1),
(@last_id, 'La Vie d''Adèle', 0),
(@last_id, 'Amour', 0),
(@last_id, 'Dheepan', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel poète français a écrit "Le Dormeur du val"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Arthur Rimbaud', 1),
(@last_id, 'Charles Baudelaire', 0),
(@last_id, 'Paul Verlaine', 0),
(@last_id, 'Victor Hugo', 0);

-- Ajout de questions supplémentaires pour SPORT (Catégorie 5)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel joueur de football français a remporté le Ballon d''Or en 2022?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Karim Benzema', 1),
(@last_id, 'Kylian Mbappé', 0),
(@last_id, 'N''Golo Kanté', 0),
(@last_id, 'Antoine Griezmann', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel joueur de tennis français a remporté Roland-Garros en 1983?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Yannick Noah', 1),
(@last_id, 'Jo-Wilfried Tsonga', 0),
(@last_id, 'Henri Leconte', 0),
(@last_id, 'Richard Gasquet', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel sport équestre traditionnel est pratiqué en Camargue?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La course camarguaise', 1),
(@last_id, 'Le polo', 0),
(@last_id, 'La course landaise', 0),
(@last_id, 'Le saut d''obstacles', 0);

-- Ajout de questions supplémentaires pour DIVERS (Catégorie 6)
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le TGV le plus rapide de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le TGV Est', 1),
(@last_id, 'Le TGV Atlantique', 0),
(@last_id, 'Le TGV Sud-Est', 0),
(@last_id, 'Le TGV Nord', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quelle est la plus ancienne fête foraine de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Foire du Trône', 1),
(@last_id, 'La Foire Saint-Romain', 0),
(@last_id, 'La Fête des Loges', 0),
(@last_id, 'La Foire de Lille', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 3, 'Quelle est la devise de la SNCF?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'À nous de vous faire préférer le train', 1),
(@last_id, 'Le progrès ne vaut que s''il est partagé par tous', 0),
(@last_id, 'Chacun sa route, chacun son chemin', 0),
(@last_id, 'Voyagez au-delà de vos attentes', 0);

-- Ajout de 50 questions supplémentaires par thème

-- HISTOIRE (Catégorie 1) - 50 questions supplémentaires
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a eu lieu la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1789', 1),
(@last_id, '1780', 0),
(@last_id, '1799', 0),
(@last_id, '1769', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a été le premier président de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'François Mitterrand', 0),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Valéry Giscard d''Estaing', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle dynastie a régné sur la France de 987 à 1328?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les Capétiens', 1),
(@last_id, 'Les Mérovingiens', 0),
(@last_id, 'Les Carolingiens', 0),
(@last_id, 'Les Valois', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a été construite la Tour Eiffel?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1889', 1),
(@last_id, '1869', 0),
(@last_id, '1899', 0),
(@last_id, '1909', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui était le roi de France pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XVI', 1),
(@last_id, 'Louis XIV', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVIII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle est la date de la prise de la Bastille?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '14 juillet 1789', 1),
(@last_id, '14 juillet 1790', 0),
(@last_id, '10 août 1792', 0),
(@last_id, '21 janvier 1793', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui était la reine de France pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marie-Antoinette', 1),
(@last_id, 'Marie de Médicis', 0),
(@last_id, 'Anne d''Autriche', 0),
(@last_id, 'Joséphine de Beauharnais', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a eu lieu la bataille de Waterloo?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1815', 1),
(@last_id, '1805', 0),
(@last_id, '1825', 0),
(@last_id, '1812', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel empereur a dirigé la France de 1804 à 1814?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Louis-Napoléon Bonaparte', 0),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Charles X', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année les femmes ont-elles obtenu le droit de vote en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1944', 1),
(@last_id, '1918', 0),
(@last_id, '1936', 0),
(@last_id, '1958', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a été le président de la France pendant la crise de mai 1968?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'François Mitterrand', 0),
(@last_id, 'Valéry Giscard d''Estaing', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle était la capitale de la France pendant l''occupation allemande?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vichy', 1),
(@last_id, 'Paris', 0),
(@last_id, 'Lyon', 0),
(@last_id, 'Bordeaux', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année la France a-t-elle adhéré à la Communauté Économique Européenne (CEE)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1957', 1),
(@last_id, '1945', 0),
(@last_id, '1963', 0),
(@last_id, '1973', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a dirigé la France pendant la Seconde Guerre mondiale depuis Londres?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Philippe Pétain', 0),
(@last_id, 'Pierre Laval', 0),
(@last_id, 'Jean Moulin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a été signée l''abolition de l''esclavage en France (décret de Schoelcher)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1848', 1),
(@last_id, '1789', 0),
(@last_id, '1830', 0),
(@last_id, '1871', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel roi a fait construire le château de Versailles?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XIII', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVI', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel traité a mis fin à la guerre de Cent Ans?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Picquigny', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Troyes', 0),
(@last_id, 'Le traité de Brétigny', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a dirigé le Gouvernement provisoire de la République française en 1944?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Bidault', 0),
(@last_id, 'Henri Queuille', 0),
(@last_id, 'Félix Gouin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle bataille décisive a eu lieu en 732 près de Poitiers?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille de Poitiers (ou Tours)', 1),
(@last_id, 'La bataille de Vouillé', 0),
(@last_id, 'La bataille de Soissons', 0),
(@last_id, 'La bataille de Tolbiac', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le premier roi des Francs?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Clovis', 1),
(@last_id, 'Charlemagne', 0),
(@last_id, 'Mérovée', 0),
(@last_id, 'Childéric', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'En quelle année a eu lieu la bataille de Marignan?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1515', 1),
(@last_id, '1415', 0),
(@last_id, '1615', 0),
(@last_id, '1715', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi de France a été surnommé "le Roi Soleil"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XIII', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVI', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le premier président de la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vincent Auriol', 1),
(@last_id, 'René Coty', 0),
(@last_id, 'Georges Bidault', 0),
(@last_id, 'Félix Gouin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a mis fin à la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La crise d''Algérie', 1),
(@last_id, 'La guerre d''Indochine', 0),
(@last_id, 'La crise de Suez', 0),
(@last_id, 'La guerre froide', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel traité a mis fin à la guerre d''Algérie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les accords d''Évian', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Les accords de Genève', 0),
(@last_id, 'Le traité de Versailles', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi de France a été guillotiné pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XVI', 1),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVII', 0),
(@last_id, 'Louis XVIII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle bataille a eu lieu le 18 juin 1815?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille de Waterloo', 1),
(@last_id, 'La bataille d''Austerlitz', 0),
(@last_id, 'La bataille de Marengo', 0),
(@last_id, 'La bataille de Wagram', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le dernier roi de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis-Philippe Ier', 1),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Charles X', 0),
(@last_id, 'Napoléon III', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a déclenché la Première Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''assassinat de l''archiduc François-Ferdinand', 1),
(@last_id, 'L''invasion de la Pologne', 0),
(@last_id, 'La crise des Sudètes', 0),
(@last_id, 'Le torpillage du Lusitania', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel mouvement de résistance a été créé par Jean Moulin pendant la Seconde Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Conseil National de la Résistance', 1),
(@last_id, 'Combat', 0),
(@last_id, 'Libération', 0),
(@last_id, 'Franc-Tireur', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a eu lieu le 10 mai 1981 en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''élection de François Mitterrand à la présidence', 1),
(@last_id, 'Le référendum sur Maastricht', 0),
(@last_id, 'La dissolution de l''Assemblée nationale', 0),
(@last_id, 'La cohabitation avec Jacques Chirac', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel général a mené la résistance gauloise contre Jules César?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vercingétorix', 1),
(@last_id, 'Ambiorix', 0),
(@last_id, 'Dumnorix', 0),
(@last_id, 'Brennus', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le premier chef du gouvernement de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Michel Debré', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Jacques Chaban-Delmas', 0),
(@last_id, 'Pierre Messmer', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a été signée l''ordonnance de Villers-Cotterêts?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1539', 1),
(@last_id, '1498', 0),
(@last_id, '1589', 0),
(@last_id, '1610', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le président du Directoire pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paul Barras', 1),
(@last_id, 'Emmanuel-Joseph Sieyès', 0),
(@last_id, 'Jean-Lambert Tallien', 0),
(@last_id, 'Lazare Carnot', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel événement s''est déroulé le 9 Thermidor an II?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La chute de Robespierre', 1),
(@last_id, 'La prise des Tuileries', 0),
(@last_id, 'La création du Comité de salut public', 0),
(@last_id, 'L''exécution de Danton', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel ministre de Louis XIV a créé l''Académie des sciences en 1666?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean-Baptiste Colbert', 1),
(@last_id, 'François Michel Le Tellier de Louvois', 0),
(@last_id, 'Nicolas Fouquet', 0),
(@last_id, 'Michel Le Tellier', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel traité a mis fin à la guerre de Succession d''Espagne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité d''Utrecht', 1),
(@last_id, 'Le traité de Rastatt', 0),
(@last_id, 'Le traité de Ryswick', 0),
(@last_id, 'Le traité de Westphalie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle a été la première ville libérée par les Alliés sur le territoire français en 1944?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Bayeux', 1),
(@last_id, 'Caen', 0),
(@last_id, 'Cherbourg', 0),
(@last_id, 'Saint-Lô', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle loi de 1905 a séparé les Églises et l''État en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La loi de séparation des Églises et de l''État', 1),
(@last_id, 'La loi Ferry', 0),
(@last_id, 'La loi Falloux', 0),
(@last_id, 'La loi Combes', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui était le premier ministre français lors de la crise des missiles de Cuba en 1962?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Georges Pompidou', 1),
(@last_id, 'Michel Debré', 0),
(@last_id, 'Jacques Chaban-Delmas', 0),
(@last_id, 'Maurice Couve de Murville', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel roi a créé l''Académie française en 1635?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIII', 1),
(@last_id, 'Louis XIV', 0),
(@last_id, 'Henri IV', 0),
(@last_id, 'Louis XII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le premier Premier ministre de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Michel Debré', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Félix Gaillard', 0),
(@last_id, 'Guy Mollet', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel cardinal a été le principal ministre de Louis XIII?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le cardinal de Richelieu', 1),
(@last_id, 'Le cardinal Mazarin', 0),
(@last_id, 'Le cardinal de Retz', 0),
(@last_id, 'Le cardinal Fleury', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a été signé l''Édit de Nantes?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1598', 1),
(@last_id, '1572', 0),
(@last_id, '1610', 0),
(@last_id, '1685', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le dernier président de la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'René Coty', 1),
(@last_id, 'Vincent Auriol', 0),
(@last_id, 'Albert Lebrun', 0),
(@last_id, 'Paul Ramadier', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel traité a mis fin à la guerre franco-prussienne de 1870?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Francfort', 1),
(@last_id, 'Le traité de Versailles', 0),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Sedan', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a proclamé l''Empire français en 1804?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Talleyrand', 0),
(@last_id, 'Fouché', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel ministère a été créé par Napoléon Bonaparte en 1804?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le ministère des Cultes', 1),
(@last_id, 'Le ministère de l''Intérieur', 0),
(@last_id, 'Le ministère des Affaires étrangères', 0),
(@last_id, 'Le ministère de la Guerre', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a eu lieu le Sacre de Napoléon Ier?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1804', 1),
(@last_id, '1802', 0),
(@last_id, '1806', 0),
(@last_id, '1810', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle bataille a eu lieu le 2 décembre 1805?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille d''Austerlitz', 1),
(@last_id, 'La bataille de Trafalgar', 0),
(@last_id, 'La bataille d''Iéna', 0),
(@last_id, 'La bataille de Wagram', 0);

-- GÉOGRAPHIE (Catégorie 3) - 50 questions supplémentaires
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la capitale de la France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Lyon', 0),
(@last_id, 'Marseille', 0),
(@last_id, 'Toulouse', 0);

-- Ajout de 50 questions supplémentaires par thème

-- HISTOIRE (Catégorie 1) - 50 questions supplémentaires
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a eu lieu la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1789', 1),
(@last_id, '1780', 0),
(@last_id, '1799', 0),
(@last_id, '1769', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a été le premier président de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'François Mitterrand', 0),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Valéry Giscard d''Estaing', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle dynastie a régné sur la France de 987 à 1328?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les Capétiens', 1),
(@last_id, 'Les Mérovingiens', 0),
(@last_id, 'Les Carolingiens', 0),
(@last_id, 'Les Valois', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a été construite la Tour Eiffel?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1889', 1),
(@last_id, '1869', 0),
(@last_id, '1899', 0),
(@last_id, '1909', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui était le roi de France pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XVI', 1),
(@last_id, 'Louis XIV', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVIII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle est la date de la prise de la Bastille?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '14 juillet 1789', 1),
(@last_id, '14 juillet 1790', 0),
(@last_id, '10 août 1792', 0),
(@last_id, '21 janvier 1793', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui était la reine de France pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marie-Antoinette', 1),
(@last_id, 'Marie de Médicis', 0),
(@last_id, 'Anne d''Autriche', 0),
(@last_id, 'Joséphine de Beauharnais', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a eu lieu la bataille de Waterloo?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1815', 1),
(@last_id, '1805', 0),
(@last_id, '1825', 0),
(@last_id, '1812', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel empereur a dirigé la France de 1804 à 1814?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Louis-Napoléon Bonaparte', 0),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Charles X', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année les femmes ont-elles obtenu le droit de vote en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1944', 1),
(@last_id, '1918', 0),
(@last_id, '1936', 0),
(@last_id, '1958', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a été le président de la France pendant la crise de mai 1968?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'François Mitterrand', 0),
(@last_id, 'Valéry Giscard d''Estaing', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quelle était la capitale de la France pendant l''occupation allemande?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vichy', 1),
(@last_id, 'Paris', 0),
(@last_id, 'Lyon', 0),
(@last_id, 'Bordeaux', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année la France a-t-elle adhéré à la Communauté Économique Européenne (CEE)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1957', 1),
(@last_id, '1945', 0),
(@last_id, '1963', 0),
(@last_id, '1973', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Qui a dirigé la France pendant la Seconde Guerre mondiale depuis Londres?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Philippe Pétain', 0),
(@last_id, 'Pierre Laval', 0),
(@last_id, 'Jean Moulin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'En quelle année a été signée l''abolition de l''esclavage en France (décret de Schoelcher)?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1848', 1),
(@last_id, '1789', 0),
(@last_id, '1830', 0),
(@last_id, '1871', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 1, 'Quel roi a fait construire le château de Versailles?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XIII', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVI', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel traité a mis fin à la guerre de Cent Ans?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Picquigny', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Troyes', 0),
(@last_id, 'Le traité de Brétigny', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a dirigé le Gouvernement provisoire de la République française en 1944?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Charles de Gaulle', 1),
(@last_id, 'Georges Bidault', 0),
(@last_id, 'Henri Queuille', 0),
(@last_id, 'Félix Gouin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle bataille décisive a eu lieu en 732 près de Poitiers?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille de Poitiers (ou Tours)', 1),
(@last_id, 'La bataille de Vouillé', 0),
(@last_id, 'La bataille de Soissons', 0),
(@last_id, 'La bataille de Tolbiac', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le premier roi des Francs?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Clovis', 1),
(@last_id, 'Charlemagne', 0),
(@last_id, 'Mérovée', 0),
(@last_id, 'Childéric', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'En quelle année a eu lieu la bataille de Marignan?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1515', 1),
(@last_id, '1415', 0),
(@last_id, '1615', 0),
(@last_id, '1715', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi de France a été surnommé "le Roi Soleil"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIV', 1),
(@last_id, 'Louis XIII', 0),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVI', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le premier président de la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vincent Auriol', 1),
(@last_id, 'René Coty', 0),
(@last_id, 'Georges Bidault', 0),
(@last_id, 'Félix Gouin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a mis fin à la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La crise d''Algérie', 1),
(@last_id, 'La guerre d''Indochine', 0),
(@last_id, 'La crise de Suez', 0),
(@last_id, 'La guerre froide', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel traité a mis fin à la guerre d''Algérie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les accords d''Évian', 1),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Les accords de Genève', 0),
(@last_id, 'Le traité de Versailles', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel roi de France a été guillotiné pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XVI', 1),
(@last_id, 'Louis XV', 0),
(@last_id, 'Louis XVII', 0),
(@last_id, 'Louis XVIII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quelle bataille a eu lieu le 18 juin 1815?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille de Waterloo', 1),
(@last_id, 'La bataille d''Austerlitz', 0),
(@last_id, 'La bataille de Marengo', 0),
(@last_id, 'La bataille de Wagram', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Qui a été le dernier roi de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis-Philippe Ier', 1),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Charles X', 0),
(@last_id, 'Napoléon III', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a déclenché la Première Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''assassinat de l''archiduc François-Ferdinand', 1),
(@last_id, 'L''invasion de la Pologne', 0),
(@last_id, 'La crise des Sudètes', 0),
(@last_id, 'Le torpillage du Lusitania', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel mouvement de résistance a été créé par Jean Moulin pendant la Seconde Guerre mondiale?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Conseil National de la Résistance', 1),
(@last_id, 'Combat', 0),
(@last_id, 'Libération', 0),
(@last_id, 'Franc-Tireur', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel événement a eu lieu le 10 mai 1981 en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L''élection de François Mitterrand à la présidence', 1),
(@last_id, 'Le référendum sur Maastricht', 0),
(@last_id, 'La dissolution de l''Assemblée nationale', 0),
(@last_id, 'La cohabitation avec Jacques Chirac', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 2, 'Quel général a mené la résistance gauloise contre Jules César?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Vercingétorix', 1),
(@last_id, 'Ambiorix', 0),
(@last_id, 'Dumnorix', 0),
(@last_id, 'Brennus', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le premier chef du gouvernement de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Michel Debré', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Jacques Chaban-Delmas', 0),
(@last_id, 'Pierre Messmer', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a été signée l''ordonnance de Villers-Cotterêts?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1539', 1),
(@last_id, '1498', 0),
(@last_id, '1589', 0),
(@last_id, '1610', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le président du Directoire pendant la Révolution française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paul Barras', 1),
(@last_id, 'Emmanuel-Joseph Sieyès', 0),
(@last_id, 'Jean-Lambert Tallien', 0),
(@last_id, 'Lazare Carnot', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel événement s''est déroulé le 9 Thermidor an II?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La chute de Robespierre', 1),
(@last_id, 'La prise des Tuileries', 0),
(@last_id, 'La création du Comité de salut public', 0),
(@last_id, 'L''exécution de Danton', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel ministre de Louis XIV a créé l''Académie des sciences en 1666?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean-Baptiste Colbert', 1),
(@last_id, 'François Michel Le Tellier de Louvois', 0),
(@last_id, 'Nicolas Fouquet', 0),
(@last_id, 'Michel Le Tellier', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel traité a mis fin à la guerre de Succession d''Espagne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité d''Utrecht', 1),
(@last_id, 'Le traité de Rastatt', 0),
(@last_id, 'Le traité de Ryswick', 0),
(@last_id, 'Le traité de Westphalie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle a été la première ville libérée par les Alliés sur le territoire français en 1944?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Bayeux', 1),
(@last_id, 'Caen', 0),
(@last_id, 'Cherbourg', 0),
(@last_id, 'Saint-Lô', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle loi de 1905 a séparé les Églises et l''État en France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La loi de séparation des Églises et de l''État', 1),
(@last_id, 'La loi Ferry', 0),
(@last_id, 'La loi Falloux', 0),
(@last_id, 'La loi Combes', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui était le premier ministre français lors de la crise des missiles de Cuba en 1962?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Georges Pompidou', 1),
(@last_id, 'Michel Debré', 0),
(@last_id, 'Jacques Chaban-Delmas', 0),
(@last_id, 'Maurice Couve de Murville', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel roi a créé l''Académie française en 1635?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Louis XIII', 1),
(@last_id, 'Louis XIV', 0),
(@last_id, 'Henri IV', 0),
(@last_id, 'Louis XII', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le premier Premier ministre de la Ve République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Michel Debré', 1),
(@last_id, 'Georges Pompidou', 0),
(@last_id, 'Félix Gaillard', 0),
(@last_id, 'Guy Mollet', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel cardinal a été le principal ministre de Louis XIII?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le cardinal de Richelieu', 1),
(@last_id, 'Le cardinal Mazarin', 0),
(@last_id, 'Le cardinal de Retz', 0),
(@last_id, 'Le cardinal Fleury', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a été signé l''Édit de Nantes?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1598', 1),
(@last_id, '1572', 0),
(@last_id, '1610', 0),
(@last_id, '1685', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a été le dernier président de la IVe République française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'René Coty', 1),
(@last_id, 'Vincent Auriol', 0),
(@last_id, 'Albert Lebrun', 0),
(@last_id, 'Paul Ramadier', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel traité a mis fin à la guerre franco-prussienne de 1870?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le traité de Francfort', 1),
(@last_id, 'Le traité de Versailles', 0),
(@last_id, 'Le traité de Paris', 0),
(@last_id, 'Le traité de Sedan', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Qui a proclamé l''Empire français en 1804?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Napoléon Bonaparte', 1),
(@last_id, 'Louis XVIII', 0),
(@last_id, 'Talleyrand', 0),
(@last_id, 'Fouché', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quel ministère a été créé par Napoléon Bonaparte en 1804?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le ministère des Cultes', 1),
(@last_id, 'Le ministère de l''Intérieur', 0),
(@last_id, 'Le ministère des Affaires étrangères', 0),
(@last_id, 'Le ministère de la Guerre', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'En quelle année a eu lieu le Sacre de Napoléon Ier?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1804', 1),
(@last_id, '1802', 0),
(@last_id, '1806', 0),
(@last_id, '1810', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(1, 3, 'Quelle bataille a eu lieu le 2 décembre 1805?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La bataille d''Austerlitz', 1),
(@last_id, 'La bataille de Trafalgar', 0),
(@last_id, 'La bataille d''Iéna', 0),
(@last_id, 'La bataille de Wagram', 0);

-- GÉOGRAPHIE (Catégorie 3) - 50 questions supplémentaires
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la capitale de la France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Lyon', 0),
(@last_id, 'Marseille', 0),
(@last_id, 'Toulouse', 0);

-- GÉOGRAPHIE (Catégorie 3) - Suite
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel est le plus long fleuve de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Loire', 1),
(@last_id, 'La Seine', 0),
(@last_id, 'Le Rhône', 0),
(@last_id, 'La Garonne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel est le point culminant de la France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Mont Blanc', 1),
(@last_id, 'Le Mont Ventoux', 0),
(@last_id, 'Le Pic du Midi', 0),
(@last_id, 'Le Puy de Sancy', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle mer borde la Côte d''Azur?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Méditerranée', 1),
(@last_id, 'L''Atlantique', 0),
(@last_id, 'La Manche', 0),
(@last_id, 'La Mer du Nord', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel est le plus grand lac naturel de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le lac du Bourget', 1),
(@last_id, 'Le lac d''Annecy', 0),
(@last_id, 'Le lac de Serre-Ponçon', 0),
(@last_id, 'Le lac de Sainte-Croix', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Combien y a-t-il de régions en France métropolitaine depuis 2016?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '13', 1),
(@last_id, '22', 0),
(@last_id, '18', 0),
(@last_id, '10', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle région est surnommée "le grenier à blé" de la France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Beauce', 1),
(@last_id, 'La Brie', 0),
(@last_id, 'La Champagne', 0),
(@last_id, 'La Sologne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quel département possède le numéro 75?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Seine-et-Marne', 0),
(@last_id, 'Yvelines', 0),
(@last_id, 'Val-de-Marne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle ville est connue comme la capitale de la Bourgogne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Dijon', 1),
(@last_id, 'Auxerre', 0),
(@last_id, 'Mâcon', 0),
(@last_id, 'Nevers', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Dans quelle ville se trouve le Parlement européen?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Strasbourg', 1),
(@last_id, 'Bruxelles', 0),
(@last_id, 'Luxembourg', 0),
(@last_id, 'Paris', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 1, 'Quelle est la plus grande ville du Sud-Ouest de la France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Toulouse', 1),
(@last_id, 'Bordeaux', 0),
(@last_id, 'Montpellier', 0),
(@last_id, 'Nantes', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le plus petit département français en termes de superficie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Territoire de Belfort', 0),
(@last_id, 'Hauts-de-Seine', 0),
(@last_id, 'Val-de-Marne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle est l''île française la plus proche de l''Italie?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Corse', 1),
(@last_id, 'Porquerolles', 0),
(@last_id, 'L''île d''Yeu', 0),
(@last_id, 'Noirmoutier', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le nom du plus haut sommet des Pyrénées françaises?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Vignemale', 1),
(@last_id, 'Le Mont Perdu', 0),
(@last_id, 'Le Pic du Midi de Bigorre', 0),
(@last_id, 'Le Pic d''Aneto', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle rivière traverse la ville de Paris?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Seine', 1),
(@last_id, 'La Loire', 0),
(@last_id, 'Le Rhône', 0),
(@last_id, 'La Garonne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel département français a pour chef-lieu Ajaccio?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Corse-du-Sud', 1),
(@last_id, 'La Haute-Corse', 0),
(@last_id, 'Les Alpes-Maritimes', 0),
(@last_id, 'Le Var', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel canal relie la Méditerranée à l''Atlantique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Canal du Midi', 1),
(@last_id, 'Le Canal de Bourgogne', 0),
(@last_id, 'Le Canal de la Marne au Rhin', 0),
(@last_id, 'Le Canal de Briare', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel département français porte le numéro 20?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Corse (avant sa division)', 1),
(@last_id, 'Les Alpes-de-Haute-Provence', 0),
(@last_id, 'La Haute-Savoie', 0),
(@last_id, 'La Haute-Corse', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle chaîne de montagnes sépare la France de l''Espagne?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Les Pyrénées', 1),
(@last_id, 'Les Alpes', 0),
(@last_id, 'Le Massif central', 0),
(@last_id, 'Le Jura', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quelle est la préfecture du département du Bas-Rhin?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Strasbourg', 1),
(@last_id, 'Colmar', 0),
(@last_id, 'Mulhouse', 0),
(@last_id, 'Metz', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 2, 'Quel est le plus grand port maritime français?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marseille', 1),
(@last_id, 'Le Havre', 0),
(@last_id, 'Dunkerque', 0),
(@last_id, 'Nantes-Saint-Nazaire', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le nom du plus haut sommet de la Corse?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Monte Cinto', 1),
(@last_id, 'Monte Rotondo', 0),
(@last_id, 'Monte d''Oro', 0),
(@last_id, 'Paglia Orba', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel département français a le numéro 988?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Nouvelle-Calédonie', 1),
(@last_id, 'Polynésie française', 0),
(@last_id, 'Martinique', 0),
(@last_id, 'Guyane', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la longueur précise du littoral français métropolitain?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Environ 5 500 km', 1),
(@last_id, 'Environ 3 500 km', 0),
(@last_id, 'Environ 7 000 km', 0),
(@last_id, 'Environ 4 200 km', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le point le plus bas de France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le delta du Rhône', 1),
(@last_id, 'Le marais poitevin', 0),
(@last_id, 'La baie de Somme', 0),
(@last_id, 'La Camargue', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la superficie de la France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Environ 550 000 km²', 1),
(@last_id, 'Environ 450 000 km²', 0),
(@last_id, 'Environ 650 000 km²', 0),
(@last_id, 'Environ 350 000 km²', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la superficie de la Zone Économique Exclusive (ZEE) française?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Environ 11 millions de km²', 1),
(@last_id, 'Environ 5 millions de km²', 0),
(@last_id, 'Environ 8 millions de km²', 0),
(@last_id, 'Environ 3 millions de km²', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le département français le moins peuplé?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Lozère', 1),
(@last_id, 'La Creuse', 0),
(@last_id, 'Les Hautes-Alpes', 0),
(@last_id, 'L''Ariège', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le plus ancien parc national de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Parc national de la Vanoise', 1),
(@last_id, 'Le Parc national des Cévennes', 0),
(@last_id, 'Le Parc national de Port-Cros', 0),
(@last_id, 'Le Parc national des Pyrénées', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quelle est la ville la plus haute de France métropolitaine?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Briançon', 1),
(@last_id, 'Chamonix', 0),
(@last_id, 'Font-Romeu', 0),
(@last_id, 'Saint-Véran', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(3, 3, 'Quel est le plus grand lac artificiel de France?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le lac de Serre-Ponçon', 1),
(@last_id, 'Le lac du Der-Chantecoq', 0),
(@last_id, 'Le lac de Sainte-Croix', 0),
(@last_id, 'Le lac de Vouglans', 0);

-- SCIENCES (Catégorie 2) - Ajout de questions
-- Facile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle est l''unité de mesure de la force?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le newton', 1),
(@last_id, 'Le watt', 0),
(@last_id, 'Le joule', 0),
(@last_id, 'Le pascal', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est le symbole chimique de l''or?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Au', 1),
(@last_id, 'Or', 0),
(@last_id, 'Ag', 0),
(@last_id, 'Fe', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle est la formule chimique de l''eau?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'H2O', 1),
(@last_id, 'CO2', 0),
(@last_id, 'O2', 0),
(@last_id, 'CH4', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle planète est surnommée la planète rouge?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mars', 1),
(@last_id, 'Vénus', 0),
(@last_id, 'Jupiter', 0),
(@last_id, 'Saturne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel scientifique français a découvert la radioactivité?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Henri Becquerel', 1),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Pierre Curie', 0),
(@last_id, 'Louis Pasteur', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l''os le plus long du corps humain?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le fémur', 1),
(@last_id, 'L''humérus', 0),
(@last_id, 'Le tibia', 0),
(@last_id, 'Le péroné', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quelle est la vitesse de la lumière dans le vide?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '300 000 km/s', 1),
(@last_id, '100 000 km/s', 0),
(@last_id, '500 000 km/s', 0),
(@last_id, '200 000 km/s', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Comment s''appelle la galaxie dans laquelle se trouve notre système solaire?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Voie lactée', 1),
(@last_id, 'Andromède', 0),
(@last_id, 'La Grande Ourse', 0),
(@last_id, 'La Petite Ourse', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est le symbole chimique du carbone?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'C', 1),
(@last_id, 'Ca', 0),
(@last_id, 'Co', 0),
(@last_id, 'Cr', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Combien d''os compose le squelette humain adulte?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '206', 1),
(@last_id, '186', 0),
(@last_id, '226', 0),
(@last_id, '246', 0);

-- Moyen
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la loi de Newton qui stipule que toute action entraîne une réaction égale et opposée?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La troisième loi', 1),
(@last_id, 'La première loi', 0),
(@last_id, 'La deuxième loi', 0),
(@last_id, 'La loi de la gravitation universelle', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel élément a pour symbole chimique "Fe"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le fer', 1),
(@last_id, 'Le fluor', 0),
(@last_id, 'Le francium', 0),
(@last_id, 'Le fermium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la particule élémentaire qui porte une charge positive?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le proton', 1),
(@last_id, 'L''électron', 0),
(@last_id, 'Le neutron', 0),
(@last_id, 'Le photon', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la théorie proposée par Albert Einstein en 1915?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La relativité générale', 1),
(@last_id, 'La relativité restreinte', 0),
(@last_id, 'La mécanique quantique', 0),
(@last_id, 'La théorie des cordes', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel astronome français a calculé la circonférence de la Terre en 1669?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean Picard', 1),
(@last_id, 'Pierre-Simon Laplace', 0),
(@last_id, 'Jean-Dominique Cassini', 0),
(@last_id, 'Nicolas Copernic', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom de la théorie sur l''origine de l''univers proposée par Georges Lemaître?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La théorie du Big Bang', 1),
(@last_id, 'La théorie de l''état stationnaire', 0),
(@last_id, 'La théorie de l''inflation cosmique', 0),
(@last_id, 'La théorie des univers parallèles', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est l''unité de mesure de la puissance?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le watt', 1),
(@last_id, 'Le joule', 0),
(@last_id, 'L''ampère', 0),
(@last_id, 'Le coulomb', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom de la loi qui décrit la relation entre la pression et le volume d''un gaz à température constante?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La loi de Boyle-Mariotte', 1),
(@last_id, 'La loi de Gay-Lussac', 0),
(@last_id, 'La loi de Charles', 0),
(@last_id, 'La loi d''Avogadro', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom de la théorie qui explique le fonctionnement du monde à l''échelle atomique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La mécanique quantique', 1),
(@last_id, 'La relativité générale', 0),
(@last_id, 'La thermodynamique', 0),
(@last_id, 'L''électromagnétisme', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Qui a énoncé les lois de la génétique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Gregor Mendel', 1),
(@last_id, 'Charles Darwin', 0),
(@last_id, 'Louis Pasteur', 0),
(@last_id, 'Alfred Russel Wallace', 0);

-- Difficile
INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quelle est la valeur approximative de la constante de Planck?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '6,626 × 10^(-34) J·s', 1),
(@last_id, '6,022 × 10^23 mol^(-1)', 0),
(@last_id, '1,602 × 10^(-19) C', 0),
(@last_id, '9,109 × 10^(-31) kg', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel physicien a proposé le principe d''incertitude en mécanique quantique?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Werner Heisenberg', 1),
(@last_id, 'Niels Bohr', 0),
(@last_id, 'Max Planck', 0),
(@last_id, 'Erwin Schrödinger', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel compositeur est célèbre pour sa symphonie "L\'Ode à la joie"?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Johann Sebastian Bach', 0),
(@last_id, 'Ludwig van Beethoven', 1),
(@last_id, 'Wolfgang Amadeus Mozart', 0),
(@last_id, 'Franz Schubert', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Dans quel musée se trouve la célèbre œuvre "La Nuit étoilée" de Van Gogh ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le Louvre', 0),
(@last_id, 'Le musée d\'Orsay', 0),
(@last_id, 'Le musée Van Gogh à Amsterdam', 1),
(@last_id, 'Le musée du Prado', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Qui a écrit "Roméo et Juliette" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Molière', 0),
(@last_id, 'William Shakespeare', 1),
(@last_id, 'Victor Hugo', 0),
(@last_id, 'Jean-Paul Sartre', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quelle peinture célèbre de Léonard de Vinci montre un sourire mystérieux ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Création d\'Adam', 0),
(@last_id, 'La Mona Lisa', 1),
(@last_id, 'Le Jugement Dernier', 0),
(@last_id, 'La Cène', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel est le nom de la célèbre danseuse russe qui a marqué l\'histoire du ballet ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Anna Pavlova', 1),
(@last_id, 'Martha Graham', 0),
(@last_id, 'Isadora Duncan', 0),
(@last_id, 'Sylvie Guillem', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel monument historique est situé en Inde et est une merveille du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La Grande Muraille de Chine', 0),
(@last_id, 'Le Taj Mahal', 1),
(@last_id, 'Le Machu Picchu', 0),
(@last_id, 'Le Colisée', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Qui est l\'auteur du roman "1984" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Aldous Huxley', 0),
(@last_id, 'George Orwell', 1),
(@last_id, 'Ray Bradbury', 0),
(@last_id, 'J.R.R. Tolkien', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel artiste est reconnu pour ses œuvres de street art, notamment ses graffitis ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jean-Michel Basquiat', 0),
(@last_id, 'Banksy', 1),
(@last_id, 'Damien Hirst', 0),
(@last_id, 'Jeff Koons', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quelle est la couleur dominante dans le tableau "Le Cri" d\'Edvard Munch ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Bleu', 0),
(@last_id, 'Jaune', 0),
(@last_id, 'Rouge', 1),
(@last_id, 'Vert', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 1, 'Quel film d\'animation a été réalisé par Hayao Miyazaki et raconte l\'histoire de Chihiro ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mon voisin Totoro', 0),
(@last_id, 'Le Voyage de Chihiro', 1),
(@last_id, 'La Princesse Mononoké', 0),
(@last_id, 'Nausicaä de la vallée du vent', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel pays a remporté la Coupe du Monde de football 2018 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Allemagne', 0),
(@last_id, 'France', 1),
(@last_id, 'Brésil', 0),
(@last_id, 'Argentine', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel est le sport pratiqué au Tour de France ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Basketball', 0),
(@last_id, 'Cyclisme', 1),
(@last_id, 'Natation', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Dans quel sport utilise-t-on un ballon orange ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Basketball', 1),
(@last_id, 'Tennis', 0),
(@last_id, 'Rugby', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Qui a remporté la médaille d\'or aux Jeux Olympiques de 100 mètres en 2008 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Usain Bolt', 1),
(@last_id, 'Carl Lewis', 0),
(@last_id, 'Tyson Gay', 0),
(@last_id, 'Asafa Powell', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel est le nom du championnat de football en Angleterre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Serie A', 0),
(@last_id, 'La Liga', 0),
(@last_id, 'Premier League', 1),
(@last_id, 'Bundesliga', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Dans quel sport se pratique la Coupe Stanley ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Hockey sur glace', 1),
(@last_id, 'Basketball', 0),
(@last_id, 'Rugby', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel pays a remporté la Coupe du Monde de Rugby en 2019 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Nouvelle-Zélande', 0),
(@last_id, 'Afrique du Sud', 1),
(@last_id, 'Angleterre', 0),
(@last_id, 'Australie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Combien de joueurs y a-t-il dans une équipe de football ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '7', 0),
(@last_id, '9', 0),
(@last_id, '11', 1),
(@last_id, '13', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel est le nom de l\'équipe de football de la capitale de France ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Olympique de Marseille', 0),
(@last_id, 'Paris Saint-Germain', 1),
(@last_id, 'AS Monaco', 0),
(@last_id, 'Lyon', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 1, 'Quel est le sport pratiqué dans la NBA ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Basketball', 1),
(@last_id, 'Baseball', 0),
(@last_id, 'Football américain', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel joueur a remporté le plus de titres en NBA ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Kobe Bryant', 0),
(@last_id, 'Michael Jordan', 0),
(@last_id, 'Lebron James', 1),
(@last_id, 'Bill Russell', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel pays a remporté la première Coupe du Monde de football en 1930 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'France', 0),
(@last_id, 'Uruguay', 1),
(@last_id, 'Brésil', 0),
(@last_id, 'Argentine', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel est le record de victoires en Grand Chelem pour Roger Federer ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '18', 0),
(@last_id, '20', 1),
(@last_id, '22', 0),
(@last_id, '24', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel pays a remporté les Jeux Olympiques d\'hiver en 2018 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'États-Unis', 0),
(@last_id, 'Norvège', 1),
(@last_id, 'Canada', 0),
(@last_id, 'Allemagne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel est le sport de l\'épreuve du 100 mètres haies ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Natation', 0),
(@last_id, 'Athlétisme', 1),
(@last_id, 'Basketball', 0),
(@last_id, 'Football', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Dans quel sport les All Blacks sont-ils célèbres ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Rugby', 1),
(@last_id, 'Basketball', 0),
(@last_id, 'Cyclisme', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Qui a remporté le dernier Tour de France (2024) ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Tadej Pogacar', 0),
(@last_id, 'Jonas Vingegaard', 1),
(@last_id, 'Primož Roglič', 0),
(@last_id, 'Chris Froome', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel est le record du monde actuel du saut en hauteur ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '2.37 m', 0),
(@last_id, '2.45 m', 1),
(@last_id, '2.56 m', 0),
(@last_id, '2.60 m', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Dans quel sport se pratique le "Super Bowl" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Football', 0),
(@last_id, 'Football américain', 1),
(@last_id, 'Basketball', 0),
(@last_id, 'Baseball', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 2, 'Quel est le nom du tournoi de tennis joué chaque année à Wimbledon ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'French Open', 0),
(@last_id, 'Australian Open', 0),
(@last_id, 'US Open', 0),
(@last_id, 'Wimbledon', 1);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel est le record du monde du 100 mètres en athlétisme ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '9.58 secondes', 1),
(@last_id, '9.69 secondes', 0),
(@last_id, '9.74 secondes', 0),
(@last_id, '9.80 secondes', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Combien de fois la France a-t-elle remporté la Coupe du Monde de football ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '1', 0),
(@last_id, '2', 1),
(@last_id, '3', 0),
(@last_id, '4', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Qui détient le record du monde du saut en longueur ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Carl Lewis', 0),
(@last_id, 'Bob Beamon', 1),
(@last_id, 'Michael Johnson', 0),
(@last_id, 'Usain Bolt', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel joueur de tennis a remporté le plus de titres du Grand Chelem en simple ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Roger Federer', 0),
(@last_id, 'Rafael Nadal', 0),
(@last_id, 'Novak Djokovic', 1),
(@last_id, 'Pete Sampras', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel est le plus grand nombre de titres remportés par un joueur en Ligue des Champions ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '5', 0),
(@last_id, '6', 0),
(@last_id, '7', 1),
(@last_id, '8', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel est le nom du tournoi de golf le plus prestigieux ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'The Open Championship', 0),
(@last_id, 'US Open', 0),
(@last_id, 'The Masters', 1),
(@last_id, 'PGA Championship', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel est le pays hôte des Jeux Olympiques d\'été de 2024 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paris', 1),
(@last_id, 'Tokyo', 0),
(@last_id, 'Londres', 0),
(@last_id, 'Los Angeles', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Dans quel sport s\'est illustré Michael Phelps ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Cyclisme', 0),
(@last_id, 'Natation', 1),
(@last_id, 'Basketball', 0),
(@last_id, 'Football', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Qui a remporté la Coupe du Monde de football féminin 2019 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'États-Unis', 1),
(@last_id, 'Allemagne', 0),
(@last_id, 'Japon', 0),
(@last_id, 'France', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(5, 3, 'Quel joueur a marqué le plus grand nombre de buts en Ligue 1 ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Zlatan Ibrahimović', 0),
(@last_id, 'Thierry Henry', 0),
(@last_id, 'Edinson Cavani', 1),
(@last_id, 'Jean-Pierre Papin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Qui a peint "Guernica" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Vincent van Gogh', 0),
(@last_id, 'Claude Monet', 0),
(@last_id, 'Salvador Dalí', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'élément chimique symbolisé par "H" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Hélium', 0),
(@last_id, 'Hydrogène', 1),
(@last_id, 'Holmium', 0),
(@last_id, 'Hafnium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand pays d\'Amérique du Sud ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Brésil', 1),
(@last_id, 'Argentine', 0),
(@last_id, 'Colombie', 0),
(@last_id, 'Venezuela', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le nom du premier président des États-Unis ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'George Washington', 1),
(@last_id, 'Abraham Lincoln', 0),
(@last_id, 'Thomas Jefferson', 0),
(@last_id, 'Franklin D. Roosevelt', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand continent du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Asie', 1),
(@last_id, 'Afrique', 0),
(@last_id, 'Amérique', 0),
(@last_id, 'Europe', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'élément chimique symbolisé par "Na" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Néon', 0),
(@last_id, 'Natrium', 1),
(@last_id, 'Nickel', 0),
(@last_id, 'Nobium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Qui a inventé le téléphone ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Thomas Edison', 0),
(@last_id, 'Alexander Graham Bell', 1),
(@last_id, 'Nikola Tesla', 0),
(@last_id, 'Michael Faraday', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'animal national de l\'Australie ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Koala', 0),
(@last_id, 'Kangourou', 1),
(@last_id, 'Dingo', 0),
(@last_id, 'Emu', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand désert du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Sahara', 0),
(@last_id, 'Antarctique', 1),
(@last_id, 'Gobi', 0),
(@last_id, 'Atacama', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Dans quel pays se trouve la ville de Machu Picchu ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pérou', 1),
(@last_id, 'Mexique', 0),
(@last_id, 'Colombie', 0),
(@last_id, 'Brésil', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Qui a écrit "Les Fleurs du mal" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Paul Verlaine', 0),
(@last_id, 'Arthur Rimbaud', 0),
(@last_id, 'Charles Baudelaire', 1),
(@last_id, 'Gérard de Nerval', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Qui a été le premier homme à marcher sur la Lune ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Buzz Aldrin', 0),
(@last_id, 'Neil Armstrong', 1),
(@last_id, 'Yuri Gagarin', 0),
(@last_id, 'John Glenn', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le plus grand lac d\'eau douce du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Lac Baïkal', 1),
(@last_id, 'Lac Supérieur', 0),
(@last_id, 'Lac Victoria', 0),
(@last_id, 'Lac de Genève', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Qui a écrit "Les Trois Mousquetaires" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Jules Verne', 0),
(@last_id, 'Emile Zola', 0),
(@last_id, 'Alexandre Dumas', 1),
(@last_id, 'Victor Hugo', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quelle est la capitale de l\'Inde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mumbai', 0),
(@last_id, 'New Delhi', 1),
(@last_id, 'Bangalore', 0),
(@last_id, 'Kolkata', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le plus haut sommet du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'K2', 0),
(@last_id, 'Everest', 1),
(@last_id, 'Makalu', 0),
(@last_id, 'Lhotse', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Quel est le premier livre de la Bible ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Genèse', 1),
(@last_id, 'Exode', 0),
(@last_id, 'Lévitique', 0),
(@last_id, 'Psaumes', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Qui a inventé le moteur à vapeur ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'James Watt', 1),
(@last_id, 'George Stephenson', 0),
(@last_id, 'Thomas Edison', 0),
(@last_id, 'Isaac Newton', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 2, 'Qui a composé "La 9e Symphonie" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Ludwig van Beethoven', 1),
(@last_id, 'Wolfgang Amadeus Mozart', 0),
(@last_id, 'Johann Sebastian Bach', 0),
(@last_id, 'Frédéric Chopin', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Qui a peint "Guernica" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Vincent van Gogh', 0),
(@last_id, 'Claude Monet', 0),
(@last_id, 'Salvador Dalí', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'élément chimique symbolisé par "H" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Hélium', 0),
(@last_id, 'Hydrogène', 1),
(@last_id, 'Holmium', 0),
(@last_id, 'Hafnium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand pays d\'Amérique du Sud ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Brésil', 1),
(@last_id, 'Argentine', 0),
(@last_id, 'Colombie', 0),
(@last_id, 'Venezuela', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le nom du premier président des États-Unis ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'George Washington', 1),
(@last_id, 'Abraham Lincoln', 0),
(@last_id, 'Thomas Jefferson', 0),
(@last_id, 'Franklin D. Roosevelt', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand continent du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Asie', 1),
(@last_id, 'Afrique', 0),
(@last_id, 'Amérique', 0),
(@last_id, 'Europe', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'élément chimique symbolisé par "Na" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Néon', 0),
(@last_id, 'Natrium', 1),
(@last_id, 'Nickel', 0),
(@last_id, 'Nobium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Qui a inventé le téléphone ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Thomas Edison', 0),
(@last_id, 'Alexander Graham Bell', 1),
(@last_id, 'Nikola Tesla', 0),
(@last_id, 'Michael Faraday', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est l\'animal national de l\'Australie ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Koala', 0),
(@last_id, 'Kangourou', 1),
(@last_id, 'Dingo', 0),
(@last_id, 'Emu', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Quel est le plus grand désert du monde ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Sahara', 0),
(@last_id, 'Antarctique', 1),
(@last_id, 'Gobi', 0),
(@last_id, 'Atacama', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(6, 1, 'Dans quel pays se trouve la ville de Machu Picchu ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pérou', 1),
(@last_id, 'Mexique', 0),
(@last_id, 'Colombie', 0),
(@last_id, 'Brésil', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Qui a écrit "La Recherche du temps perdu" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Marcel Proust', 1),
(@last_id, 'François Mauriac', 0),
(@last_id, 'Albert Camus', 0),
(@last_id, 'André Gide', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel artiste est l’auteur du "Guernica" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Henri Matisse', 0),
(@last_id, 'Salvador Dalí', 0),
(@last_id, 'Mark Rothko', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Qui a réalisé "L’Enfant du siècle" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Sylvie Verheyde', 1),
(@last_id, 'Claude Chabrol', 0),
(@last_id, 'Jean-Luc Godard', 0),
(@last_id, 'François Truffaut', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel compositeur a écrit l\'opéra "La Bohème" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Giacomo Puccini', 1),
(@last_id, 'Richard Wagner', 0),
(@last_id, 'Wolfgang Amadeus Mozart', 0),
(@last_id, 'Giuseppe Verdi', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 3, 'Quel peintre a réalisé "Les Demoiselles d\'Avignon" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Henri Matisse', 0),
(@last_id, 'Marcel Duchamp', 0),
(@last_id, 'Georges Braque', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Qui a écrit le roman "1984" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'George Orwell', 1),
(@last_id, 'Aldous Huxley', 0),
(@last_id, 'F. Scott Fitzgerald', 0),
(@last_id, 'Ray Bradbury', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel est le peintre de "Guernica" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Pablo Picasso', 1),
(@last_id, 'Salvador Dalí', 0),
(@last_id, 'Claude Monet', 0),
(@last_id, 'Georges Braque', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Qui a réalisé "Inception" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Christopher Nolan', 1),
(@last_id, 'Steven Spielberg', 0),
(@last_id, 'Martin Scorsese', 0),
(@last_id, 'Quentin Tarantino', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel compositeur est l’auteur de "La Traviata" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Giuseppe Verdi', 1),
(@last_id, 'Richard Wagner', 0),
(@last_id, 'Wolfgang Amadeus Mozart', 0),
(@last_id, 'Giacomo Puccini', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel est le nom du réalisateur de "La Liste de Schindler" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Steven Spielberg', 1),
(@last_id, 'Quentin Tarantino', 0),
(@last_id, 'Christopher Nolan', 0),
(@last_id, 'Martin Scorsese', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Qui a écrit "Moby Dick" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Herman Melville', 1),
(@last_id, 'Mark Twain', 0),
(@last_id, 'F. Scott Fitzgerald', 0),
(@last_id, 'Charles Dickens', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel réalisateur a créé "Le Seigneur des Anneaux" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Peter Jackson', 1),
(@last_id, 'François Truffaut', 0),
(@last_id, 'Stanley Kubrick', 0),
(@last_id, 'George Lucas', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel peintre a réalisé "La Naissance de Vénus" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Sandro Botticelli', 1),
(@last_id, 'Michel-Ange', 0),
(@last_id, 'Leonardo da Vinci', 0),
(@last_id, 'Raphaël', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(4, 2, 'Quel est le nom du compositeur de "Le Sacre du printemps" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Igor Stravinsky', 1),
(@last_id, 'Claude Debussy', 0),
(@last_id, 'Pierre Boulez', 0),
(@last_id, 'Sergei Rachmaninoff', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Combien de chromosomes l\'être humain possède-t-il en moyenne ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, '46', 1),
(@last_id, '48', 0),
(@last_id, '50', 0),
(@last_id, '44', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l\'organe principal du système nerveux ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le cerveau', 1),
(@last_id, 'La moelle épinière', 0),
(@last_id, 'Les yeux', 0),
(@last_id, 'Le cœur', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l\'élément chimique dont le symbole est "H" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Hydrogène', 1),
(@last_id, 'Hélium', 0),
(@last_id, 'Hafnium', 0),
(@last_id, 'Holmium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l\'organe responsable de la digestion des aliments ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L\'estomac', 1),
(@last_id, 'Le foie', 0),
(@last_id, 'Les reins', 0),
(@last_id, 'Les poumons', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est le plus grand mammifère terrestre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L\'éléphant', 1),
(@last_id, 'La baleine bleue', 0),
(@last_id, 'Le rhinocéros', 0),
(@last_id, 'Le girafe', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est le plus grand océan de la Terre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Océan Pacifique', 1),
(@last_id, 'Océan Atlantique', 0),
(@last_id, 'Océan Indien', 0),
(@last_id, 'Océan Arctique', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l\'élément chimique dont le symbole est "Cl" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Chlore', 1),
(@last_id, 'Calcium', 0),
(@last_id, 'Carbone', 0),
(@last_id, 'Cuivre', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel gaz est le plus abondant dans l\'atmosphère terrestre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Azote', 1),
(@last_id, 'Oxygène', 0),
(@last_id, 'Dioxyde de carbone', 0),
(@last_id, 'Argon', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est le plus grand organe interne du corps humain ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le foie', 1),
(@last_id, 'Les poumons', 0),
(@last_id, 'Le cœur', 0),
(@last_id, 'Les reins', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 1, 'Quel est l\'élément chimique dont le symbole est "C" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Carbone', 1),
(@last_id, 'Calcium', 0),
(@last_id, 'Cobalt', 0),
(@last_id, 'Chromium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom du processus par lequel les organismes vivants produisent de l\'énergie en absence d\'oxygène ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Fermentation', 1),
(@last_id, 'Respiration aérobie', 0),
(@last_id, 'Métabolisme', 0),
(@last_id, 'Transpiration', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Qu\'est-ce qu\'une protéine ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Une molécule composée d\'acides aminés', 1),
(@last_id, 'Un type de vitamine', 0),
(@last_id, 'Une structure de lipides', 0),
(@last_id, 'Une molécule de sucre', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle est la planète la plus proche du soleil ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Mercure', 1),
(@last_id, 'Vénus', 0),
(@last_id, 'Terre', 0),
(@last_id, 'Mars', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est l\'élément chimique dont le symbole est "Na" ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Sodium', 1),
(@last_id, 'Néon', 0),
(@last_id, 'Nickel', 0),
(@last_id, 'Natrium', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Qui est le scientifique connu pour ses travaux sur la relativité ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Albert Einstein', 1),
(@last_id, 'Isaac Newton', 0),
(@last_id, 'Marie Curie', 0),
(@last_id, 'Nikola Tesla', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le principal gaz responsable de l\'effet de serre ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Dioxyde de carbone (CO2)', 1),
(@last_id, 'Méthane (CH4)', 0),
(@last_id, 'Oxygène (O2)', 0),
(@last_id, 'Azote (N2)', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quelle particule subatomique porte une charge positive ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le proton', 1),
(@last_id, 'L\'électron', 0),
(@last_id, 'Le neutron', 0),
(@last_id, 'Le positron', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom de la première molécule d\'ADN synthétisée en laboratoire ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'ADN artificiel', 1),
(@last_id, 'ARN', 0),
(@last_id, 'ADN recombinant', 0),
(@last_id, 'Protéine', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 2, 'Quel est le nom de l\'énergie produite par une réaction nucléaire ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Énergie nucléaire', 1),
(@last_id, 'Énergie chimique', 0),
(@last_id, 'Énergie thermique', 0),
(@last_id, 'Énergie éolienne', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Qui a formulé la théorie des quanta en physique ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Max Planck', 1),
(@last_id, 'Albert Einstein', 0),
(@last_id, 'Niels Bohr', 0),
(@last_id, 'Erwin Schrödinger', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quelle découverte a été attribuée à Marie Curie ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'La radioactivité', 1),
(@last_id, 'La théorie de la relativité', 0),
(@last_id, 'La découverte de l\'ADN', 0),
(@last_id, 'La lumière polarisée', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel est le nom du paradoxe lié à la physique quantique qui met en évidence la nature duale de la matière ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Le paradoxe de la double fente', 1),
(@last_id, 'Le paradoxe de Schrödinger', 0),
(@last_id, 'Le paradoxe de l\'information', 0),
(@last_id, 'Le paradoxe d\'EPR', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel est l\'élément le plus abondant dans l\'univers ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'L\'hydrogène', 1),
(@last_id, 'L\'hélium', 0),
(@last_id, 'L\'oxygène', 0),
(@last_id, 'Le carbone', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel scientifique est associé à la découverte des lois de la génétique ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Gregor Mendel', 1),
(@last_id, 'Charles Darwin', 0),
(@last_id, 'Louis Pasteur', 0),
(@last_id, 'Marie Curie', 0);

INSERT INTO `questions` (`categorie_id`, `difficulte_id`, `question`) VALUES
(2, 3, 'Quel est le nom de la première cellule vivante créée en laboratoire ?');
SET @last_id = LAST_INSERT_ID();
INSERT INTO `options` (`question_id`, `texte`, `est_correcte`) VALUES
(@last_id, 'Une cellule de souris', 1),
(@last_id, 'Une cellule humaine', 0),
(@last_id, 'Une cellule de levure', 0),
(@last_id, 'Une cellule bactérienne', 0);