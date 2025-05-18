<?php
require_once '../includes/db.php';
require_once 'includes/functions.php';

$erreur = '';

// Si l'utilisateur est déjà connecté en tant qu'admin, le rediriger vers le tableau de bord
if (isset($_SESSION['est_admin']) && $_SESSION['est_admin'] === true) {
    header('Location: index.php');
    exit;
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs';
    } else {
        if (loginAdmin($email, $mot_de_passe)) {
            header('Location: index.php');
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect, ou vous n\'avez pas les droits d\'administration';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Connexion</title>
    <link rel="stylesheet" href="/quizmaster/assets/css/style.css"> <!-- Ajusté pour correspondre au chemin public -->
</head>
<body>
    <section class="auth-section">
        <div class="container">
            <div class="auth-card">
                <h1>Connexion Admin</h1>
                <p>Entrez vos identifiants pour accéder au tableau de bord d'administration de Quizmaster</p>
                
                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-error">
                        <?= $erreur ?>
                    </div>
                <?php endif; ?>
                
                <form action="/quizmaster/admin/login.php" method="post" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="votre@email.com" required>
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
                    <p>Retourner au site ? <a href="/quizmaster/index.php">Accueil</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Inclure Font Awesome pour l'icône de l'œil -->
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <!-- Script pour toggler le mot de passe (copié de connexion.php si utilisé) -->
    <script>
        document.querySelector('.toggle-password')?.addEventListener('click', function() {
            const passwordInput = document.querySelector('#mot_de_passe');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>