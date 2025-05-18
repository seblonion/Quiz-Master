<?php
// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'quizmaster');
define('DB_USER', 'root');
define('DB_PASS', 'rootroot');

// Configuration de l'application
define('APP_NAME', 'QuizMaster');
define('APP_URL', 'http://localhost/QuizMaster');

// Configuration des sessions
session_start();

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Fonction pour afficher les erreurs en mode développement
function debug($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}
?>