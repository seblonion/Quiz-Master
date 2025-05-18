-- Script pour supprimer les 20 utilisateurs insérés et leurs données associées
-- Optimisé pour éviter l'erreur 1175 en mode de mise à jour sécurisé
USE `quizmaster`;

-- Étape 1 : Créer une table temporaire pour stocker les quiz_complete_id
CREATE TEMPORARY TABLE `temp_quiz_complete_ids` (
    `quiz_complete_id` INT PRIMARY KEY
);

-- Remplir la table temporaire avec les quiz_complete_id pour les utilisateurs 1 à 20
INSERT INTO `temp_quiz_complete_ids` (`quiz_complete_id`)
SELECT `id` FROM `quiz_completes` WHERE `utilisateur_id` IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20);

-- Étape 2 : Supprimer les réponses des utilisateurs en utilisant la table temporaire
DELETE FROM `reponses_utilisateurs`
WHERE `quiz_complete_id` IN (SELECT `quiz_complete_id` FROM `temp_quiz_complete_ids`);

-- Étape 3 : Supprimer les quiz complétés pour chaque utilisateur
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 1;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 2;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 3;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 4;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 5;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 6;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 7;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 8;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 9;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 10;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 11;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 12;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 13;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 14;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 15;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 16;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 17;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 18;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 19;
DELETE FROM `quiz_completes` WHERE `utilisateur_id` = 20;

-- Étape 4 : Supprimer les notifications pour chaque utilisateur
DELETE FROM `notifications` WHERE `utilisateur_id` = 1;
DELETE FROM `notifications` WHERE `utilisateur_id` = 2;
DELETE FROM `notifications` WHERE `utilisateur_id` = 3;
DELETE FROM `notifications` WHERE `utilisateur_id` = 4;
DELETE FROM `notifications` WHERE `utilisateur_id` = 5;
DELETE FROM `notifications` WHERE `utilisateur_id` = 6;
DELETE FROM `notifications` WHERE `utilisateur_id` = 7;
DELETE FROM `notifications` WHERE `utilisateur_id` = 8;
DELETE FROM `notifications` WHERE `utilisateur_id` = 9;
DELETE FROM `notifications` WHERE `utilisateur_id` = 10;
DELETE FROM `notifications` WHERE `utilisateur_id` = 11;
DELETE FROM `notifications` WHERE `utilisateur_id` = 12;
DELETE FROM `notifications` WHERE `utilisateur_id` = 13;
DELETE FROM `notifications` WHERE `utilisateur_id` = 14;
DELETE FROM `notifications` WHERE `utilisateur_id` = 15;
DELETE FROM `notifications` WHERE `utilisateur_id` = 16;
DELETE FROM `notifications` WHERE `utilisateur_id` = 17;
DELETE FROM `notifications` WHERE `utilisateur_id` = 18;
DELETE FROM `notifications` WHERE `utilisateur_id` = 19;
DELETE FROM `notifications` WHERE `utilisateur_id` = 20;

-- Étape 5 : Supprimer les badges pour chaque utilisateur
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 1;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 2;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 3;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 4;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 5;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 6;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 7;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 8;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 9;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 10;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 11;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 12;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 13;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 14;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 15;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 16;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 17;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 18;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 19;
DELETE FROM `badges_utilisateurs` WHERE `utilisateur_id` = 20;

-- Étape 6 : Supprimer les utilisateurs
DELETE FROM `utilisateurs` WHERE `id` = 1;
DELETE FROM `utilisateurs` WHERE `id` = 2;
DELETE FROM `utilisateurs` WHERE `id` = 3;
DELETE FROM `utilisateurs` WHERE `id` = 4;
DELETE FROM `utilisateurs` WHERE `id` = 5;
DELETE FROM `utilisateurs` WHERE `id` = 6;
DELETE FROM `utilisateurs` WHERE `id` = 7;
DELETE FROM `utilisateurs` WHERE `id` = 8;
DELETE FROM `utilisateurs` WHERE `id` = 9;
DELETE FROM `utilisateurs` WHERE `id` = 10;
DELETE FROM `utilisateurs` WHERE `id` = 11;
DELETE FROM `utilisateurs` WHERE `id` = 12;
DELETE FROM `utilisateurs` WHERE `id` = 13;
DELETE FROM `utilisateurs` WHERE `id` = 14;
DELETE FROM `utilisateurs` WHERE `id` = 15;
DELETE FROM `utilisateurs` WHERE `id` = 16;
DELETE FROM `utilisateurs` WHERE `id` = 17;
DELETE FROM `utilisateurs` WHERE `id` = 18;
DELETE FROM `utilisateurs` WHERE `id` = 19;
DELETE FROM `utilisateurs` WHERE `id` = 20;

-- Étape 7 : Supprimer la table temporaire
DROP TEMPORARY TABLE `temp_quiz_complete_ids`;