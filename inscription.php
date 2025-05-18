<?php
$titre_page = "Inscription";
require_once 'includes/header.php';

$erreur = '';
$nom = '';
$email = '';

// Si l'utilisateur est déjà connecté, le rediriger vers la page d'accueil
if (estConnecte()) {
    rediriger('index.php');
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = securiser($_POST['nom'] ?? '');
    $email = securiser($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($confirmation)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mot_de_passe) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        $database = new Database();
        $db = $database->connect();
        
        // Vérifier si l'email est déjà utilisé
        $query = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        } else {
            // Hacher le mot de passe
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur
            $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (:nom, :email, :mot_de_passe)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe_hache);
            
            if ($stmt->execute()) {
                // Récupérer l'ID de l'utilisateur nouvellement créé
                $utilisateur_id = $db->lastInsertId();
                
                // Connecter l'utilisateur
                $_SESSION['utilisateur_id'] = $utilisateur_id;
                $_SESSION['utilisateur_nom'] = $nom;
                
                // Rediriger vers la page d'accueil
                rediriger('index.php');
            } else {
                $erreur = 'Une erreur est survenue lors de l\'inscription.';
            }
        }
    }
}
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <h1>Inscription</h1>
            <p>Créez votre compte pour suivre votre progression et enregistrer vos scores</p>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-error">
                    <?= $erreur ?>
                </div>
            <?php endif; ?>
            
            <form action="inscription.php" method="post" class="auth-form">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= $nom ?>" placeholder="Votre nom" required>
                </div>
                
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
                
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe</label>
                    <div class="password-input">
                        <input type="password" id="confirmation" name="confirmation" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </form>
            
            <div class="auth-links">
                <p>Vous avez déjà un compte? <a href="connexion.php">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>