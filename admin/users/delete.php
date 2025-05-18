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

// Empêcher l'auto-suppression
if ($_SESSION['utilisateur_id'] == $id) {
    $_SESSION['message'] = 'Vous ne pouvez pas supprimer votre propre compte';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Récupérer l'utilisateur
$utilisateur = obtenirUtilisateur($id);

if (!$utilisateur) {
    $_SESSION['message'] = 'Utilisateur non trouvé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Traitement de la suppression
$result = supprimerUtilisateur($id);

if ($result) {
    $_SESSION['message'] = 'L\'utilisateur a été supprimé avec succès';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Une erreur est survenue lors de la suppression ou cet utilisateur est le dernier administrateur';
    $_SESSION['message_type'] = 'error';
}

header('Location: index.php');
exit;