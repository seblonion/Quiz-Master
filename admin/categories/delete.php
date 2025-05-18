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

// Récupérer la catégorie
$categorie = obtenirCategorieAdmin($id);

if (!$categorie) {
    $_SESSION['message'] = 'Catégorie non trouvée';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier si la catégorie est utilisée
$nb_questions = obtenirNombreQuestionsParCategorie($id);

if ($nb_questions > 0) {
    $_SESSION['message'] = 'Cette catégorie est utilisée par ' . $nb_questions . ' question(s) et ne peut pas être supprimée.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Traitement de la suppression
$result = supprimerCategorie($id);

if ($result) {
    $_SESSION['message'] = 'La catégorie a été supprimée avec succès';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Une erreur est survenue lors de la suppression';
    $_SESSION['message_type'] = 'error';
}

header('Location: index.php');
exit;