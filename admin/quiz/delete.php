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
$question = obtenirQuestion($id);

if (!$question) {
    $_SESSION['message'] = 'Question non trouvée';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Traitement de la suppression
$result = supprimerQuestion($id);

if ($result) {
    $_SESSION['message'] = 'La question a été supprimée avec succès';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Une erreur est survenue lors de la suppression';
    $_SESSION['message_type'] = 'error';
}

header('Location: index.php');
exit;