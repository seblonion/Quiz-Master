<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

$erreur = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $est_admin = isset($_POST['est_admin']) ? 1 : 0;
    
    // Vérification des données
    if (empty($nom)) {
        $erreur = 'Le nom est requis';
    } elseif (empty($email)) {
        $erreur = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'email n\'est pas valide';
    } elseif (empty($mot_de_passe)) {
        $erreur = 'Le mot de passe est requis';
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = 'Les mots de passe ne correspondent pas';
    } else {
        // Tout est bon, ajouter l'utilisateur
        $result = ajouterUtilisateur($nom, $email, $mot_de_passe, $est_admin);
        
        if ($result) {
            $_SESSION['message'] = 'L\'utilisateur a été ajouté avec succès';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $erreur = 'Une erreur est survenue lors de l\'ajout de l\'utilisateur ou cet email est déjà utilisé';
        }
    }
}

// Inclure l'en-tête
$titre_page = "Ajouter un utilisateur";
include '../includes/header.php';
?>

<div class="content-header">
    <h1>Ajouter un utilisateur</h1>
    <div class="actions">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= $erreur ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="content-body">
    <form method="post" class="form-large">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" value="<?= $_POST['nom'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= $_POST['email'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>
            <small class="text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
        </div>
        
        <div class="form-group">
            <label for="confirmation">Confirmer le mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" class="form-control" required>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="est_admin" name="est_admin" class="form-check-input" <?= isset($_POST['est_admin']) ? 'checked' : '' ?>>
                <label for="est_admin" class="form-check-label">Administrateur</label>
            </div>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>