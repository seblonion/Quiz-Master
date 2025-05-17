<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->connect();

$erreur = '';
$success = '';

// Traiter la connexion avant d'inclure header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = securiser($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $query = "SELECT id, nom, email, mot_de_passe, est_admin FROM utilisateurs WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
            $_SESSION['est_admin'] = $utilisateur['est_admin'];
            // Rediriger avant tout rendu HTML
            rediriger('index.php'); // Ou 'profil.php' selon vos besoins
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}

// Traiter l'inscription (si nécessaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $nom = securiser($_POST['nom'] ?? '');
    $email = securiser($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'] ?? '';

    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'email n\'est pas valide.';
    } elseif ($mot_de_passe !== $confirmation_mot_de_passe) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mot_de_passe) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        $query = "SELECT id FROM utilisateurs WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (:nom, :email, :mot_de_passe)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mot_de_passe', $mot_de_passe_hache);

            if ($stmt->execute()) {
                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            } else {
                $erreur = 'Une erreur est survenue lors de l\'inscription.';
            }
        }
    }
}

$titre_page = "Connexion / Inscription";
require_once 'includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-tabs" role="tablist">
                    <button class="tab-btn active" data-tab="login" role="tab" aria-selected="true" aria-controls="login">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span>Connexion</span>
                    </button>
                    <button class="tab-btn" data-tab="register" role="tab" aria-selected="false" aria-controls="register">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                        <span>Inscription</span>
                    </button>
                </div>

                <div class="auth-content">
                    <!-- Formulaire de connexion -->
                    <div class="auth-form active" id="login" role="tabpanel">
                        <div class="form-header">
                            <h2>Connexion</h2>
                            <p>Connectez-vous pour accéder à votre compte</p>
                        </div>
                        
                        <?php if (!empty($erreur) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
                            <div class="alert alert-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span><?= $erreur ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="post" class="form">
                            <input type="hidden" name="action" value="login">
                            
                            <div class="form-group">
                                <label for="login-email">Email</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <input type="email" id="login-email" name="email" placeholder="Votre adresse email" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="login-mot_de_passe">Mot de passe</label>
                                <div class="input-wrapper password-input">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    <input type="password" id="login-mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe" required>
                                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <span>Se connecter</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                        <polyline points="10 17 15 12 10 7"></polyline>
                                        <line x1="15" y1="12" x2="3" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Formulaire d'inscription -->
                    <div class="auth-form" id="register" role="tabpanel">
                        <div class="form-header">
                            <h2>Inscription</h2>
                            <p>Créez un compte pour accéder à toutes les fonctionnalités</p>
                        </div>
                        
                        <?php if (!empty($erreur) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
                            <div class="alert alert-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span><?= $erreur ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span><?= $success ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="post" class="form">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="form-group">
                                <label for="register-nom">Nom</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <input type="text" id="register-nom" name="nom" placeholder="Votre nom" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-email">Email</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <input type="email" id="register-email" name="email" placeholder="Votre adresse email" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-mot_de_passe">Mot de passe</label>
                                <div class="input-wrapper password-input">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    <input type="password" id="register-mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe" required>
                                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-meter">
                                        <div class="strength-segment"></div>
                                        <div class="strength-segment"></div>
                                        <div class="strength-segment"></div>
                                    </div>
                                    <span class="strength-text">Force du mot de passe</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmation_mot_de_passe">Confirmer le mot de passe</label>
                                <div class="input-wrapper password-input">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="input-icon">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" placeholder="Confirmez votre mot de passe" required>
                                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <span>S'inscrire</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="auth-info">
                <div class="info-card">
                    <div class="info-header">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="info-icon">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <h3>Pourquoi créer un compte ?</h3>
                    </div>
                    <ul class="info-list">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="list-icon">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Suivez votre progression et vos scores</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="list-icon">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Gagnez des badges et des récompenses</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="list-icon">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Participez au classement des meilleurs joueurs</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="list-icon">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Créez vos propres quiz et partagez-les</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --primary-light: rgba(79, 70, 229, 0.1);
    --secondary-color: #10b981;
    --text-color: #1f2937;
    --text-muted: #6b7280;
    --background-color: #f9fafb;
    --card-background: #ffffff;
    --border-color: #e5e7eb;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
    --font-sans: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Base Styles */
body {
    font-family: var(--font-sans);
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.auth-page {
    padding: 3rem 0;
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
}

/* Auth Container */
.auth-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

/* Auth Card */
.auth-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

/* Auth Tabs */
.auth-tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color);
}

.tab-btn {
    flex: 1;
    padding: 1rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted);
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 2px solid transparent;
}

.tab-btn:hover {
    color: var(--text-color);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Auth Content */
.auth-content {
    padding: 2rem;
}

.auth-form {
    display: none;
}

.auth-form.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Header */
.form-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.form-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: var(--text-color);
}

.form-header p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Alert */
.alert {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.alert-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

/* Form */
.form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-color);
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 0.75rem;
    width: 1.25rem;
    height: 1.25rem;
    color: var(--text-muted);
}

.input-wrapper input {
    width: 100%;
    padding: 0.75rem 0.75rem 0.75rem 2.5rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: var(--transition);
}

.input-wrapper input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.password-input input {
    padding-right: 2.5rem;
}

.toggle-password {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    transition: var(--transition);
}

.toggle-password:hover {
    color: var(--text-color);
}

.toggle-password svg {
    width: 1.25rem;
    height: 1.25rem;
}

/* Password Strength */
.password-strength {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.strength-meter {
    flex: 1;
    display: flex;
    gap: 0.25rem;
}

.strength-segment {
    height: 4px;
    flex: 1;
    background-color: var(--border-color);
    border-radius: 2px;
}

.strength-segment.weak {
    background-color: #ef4444;
}

.strength-segment.medium {
    background-color: #f59e0b;
}

.strength-segment.strong {
    background-color: #10b981;
}

.strength-text {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Form Actions */
.form-actions {
    margin-top: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    transition: var(--transition);
    cursor: pointer;
    border: none;
}

.btn-icon {
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

/* Auth Info */
.auth-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.info-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    border-left: 4px solid var(--primary-color);
}

.info-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.info-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: var(--primary-color);
    margin-right: 0.75rem;
}

.info-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-color);
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-list li {
    display: flex;
    align-items: center;
}

.list-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--primary-color);
    margin-right: 0.75rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .auth-container {
        grid-template-columns: 1fr;
    }
    
    .auth-info {
        order: -1;
        margin-bottom: 2rem;
    }
}

@media (max-width: 576px) {
    .auth-page {
        padding: 2rem 0;
    }
    
    .auth-content {
        padding: 1.5rem;
    }
    
    .form-header h2 {
        font-size: 1.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-btn');
    const authForms = document.querySelectorAll('.auth-form');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            
            authForms.forEach(form => {
                form.classList.remove('active');
            });
            
            button.classList.add('active');
            button.setAttribute('aria-selected', 'true');
            
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Gestion de l'affichage/masquage des mots de passe
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', () => {
            const input = button.previousElementSibling;
            const icon = button.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        });
    });
    
    // Vérification de la force du mot de passe
    const passwordInput = document.getElementById('register-mot_de_passe');
    const strengthSegments = document.querySelectorAll('.strength-segment');
    const strengthText = document.querySelector('.strength-text');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;
            
            // Longueur minimale
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Complexité (lettres, chiffres, caractères spéciaux)
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                strength += 1;
            }
            
            if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) {
                strength += 1;
            }
            
            // Mise à jour de l'indicateur
            strengthSegments.forEach((segment, index) => {
                segment.classList.remove('weak', 'medium', 'strong');
                
                if (index < strength) {
                    if (strength === 1) {
                        segment.classList.add('weak');
                    } else if (strength === 2) {
                        segment.classList.add('medium');
                    } else if (strength === 3) {
                        segment.classList.add('strong');
                    }
                }
            });
            
            // Mise à jour du texte
            if (password.length === 0) {
                strengthText.textContent = 'Force du mot de passe';
            } else if (strength === 1) {
                strengthText.textContent = 'Faible';
            } else if (strength === 2) {
                strengthText.textContent = 'Moyen';
            } else {
                strengthText.textContent = 'Fort';
            }
        });
    }
    
    // Vérification de la correspondance des mots de passe
    const confirmPasswordInput = document.getElementById('confirmation_mot_de_passe');
    
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value === passwordInput.value) {
                confirmPasswordInput.style.borderColor = '#10b981';
            } else {
                confirmPasswordInput.style.borderColor = '#ef4444';
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>