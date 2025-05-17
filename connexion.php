<?php
$titre_page = "Connexion";
require_once 'includes/header.php';

$erreur = '';
$email = '';

// Si l'utilisateur est déjà connecté, le rediriger vers la page d'accueil
if (estConnecte()) {
    rediriger('index.php');
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = securiser($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        $database = new Database();
        $db = $database->connect();
        
        $query = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $utilisateur = $stmt->fetch();
            
            if (password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['utilisateur_id'] = $utilisateur['id'];
                $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
                
                // Rediriger vers la page d'accueil
                rediriger('index.php');
            } else {
                $erreur = 'Mot de passe incorrect.';
            }
        } else {
            $erreur = 'Aucun compte n\'est associé à cette adresse email.';
        }
    }
}
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <h1>Connexion</h1>
            <p>Entrez vos identifiants pour accéder à votre profil et suivre votre progression</p>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-error">
                    <?= $erreur ?>
                </div>
            <?php endif; ?>
            
            <form action="connexion.php" method="post" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $email ?>" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="password-input">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
            
            <div class="auth-links">
                <p>Vous n'avez pas de compte? <a href="inscription.php">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>