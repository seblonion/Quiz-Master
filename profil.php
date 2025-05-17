<?php
$titre_page = "Mon Profil";
$scripts_supplementaires = ['https://cdn.jsdelivr.net/npm/chart.js', 'assets/js/profil.js'];
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    rediriger('register.php');
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$utilisateur_nom = $_SESSION['utilisateur_nom'];

// Récupérer les statistiques de l'utilisateur
$stats = obtenirStatistiquesUtilisateur($utilisateur_id);

// Récupérer les informations de l'utilisateur
$database = new Database();
$db = $database->connect();
$query = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $utilisateur_id);
$stmt->execute();
$utilisateur = $stmt->fetch();

// Vérifier si l'utilisateur est admin
$est_admin = isset($_SESSION['est_admin']) && $_SESSION['est_admin'] === true;

// Vérifier si l'utilisateur est contributeur
$est_contributeur = $utilisateur['est_contributeur'] == 1;

// Récupérer les quiz créés par l'utilisateur
$query_quizzes = "
    SELECT uq.id, uq.titre, uq.description, uq.status, uq.created_at, 
           c.nom AS categorie_nom, d.nom AS difficulte_nom
    FROM user_quizzes uq
    LEFT JOIN categories c ON uq.categorie_id = c.id
    LEFT JOIN difficultes d ON uq.difficulte_id = d.id
    WHERE uq.utilisateur_id = :utilisateur_id
    ORDER BY uq.created_at DESC
";
$stmt_quizzes = $db->prepare($query_quizzes);
$stmt_quizzes->bindParam(':utilisateur_id', $utilisateur_id);
$stmt_quizzes->execute();
$created_quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la mise à jour du profil
$erreur_profil = '';
$success_profil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nouveau_nom = securiser($_POST['nom'] ?? '');
    $nouveau_email = securiser($_POST['email'] ?? '');
    $mot_de_passe_actuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'] ?? '';

    if (empty($nouveau_nom) || empty($nouveau_email)) {
        $erreur_profil = 'Le nom et l\'email sont obligatoires.';
    } elseif (!filter_var($nouveau_email, FILTER_VALIDATE_EMAIL)) {
        $erreur_profil = 'L\'email n\'est pas valide.';
    } else {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $nouveau_email);
        $stmt->bindParam(':id', $utilisateur_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $erreur_profil = 'Cet email est déjà utilisé par un autre compte.';
        } else {
            // Vérifier le mot de passe actuel si un nouveau mot de passe est fourni
            $update_password = !empty($nouveau_mot_de_passe);
            if ($update_password) {
                if ($nouveau_mot_de_passe !== $confirmation_mot_de_passe) {
                    $erreur_profil = 'Les nouveaux mots de passe ne correspondent pas.';
                } elseif (strlen($nouveau_mot_de_passe) < 8) {
                    $erreur_profil = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                } elseif (!password_verify($mot_de_passe_actuel, $utilisateur['mot_de_passe'])) {
                    $erreur_profil = 'Le mot de passe actuel est incorrect.';
                }
            }

            if (empty($erreur_profil)) {
                // Mettre à jour le profil
                $query = "UPDATE utilisateurs SET nom = :nom, email = :email";
                $params = [':nom' => $nouveau_nom, ':email' => $nouveau_email, ':id' => $utilisateur_id];

                if ($update_password) {
                    $mot_de_passe_hache = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
                    $query .= ", mot_de_passe = :mot_de_passe";
                    $params[':mot_de_passe'] = $mot_de_passe_hache;
                }

                $query .= " WHERE id = :id";
                $stmt = $db->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }

                if ($stmt->execute()) {
                    $success_profil = 'Profil mis à jour avec succès.';
                    $_SESSION['utilisateur_nom'] = $nouveau_nom;
                    $utilisateur['nom'] = $nouveau_nom;
                    $utilisateur['email'] = $nouveau_email;
                    if ($update_password) {
                        // Forcer une reconnexion si le mot de passe a changé
                        session_destroy();
                        header('Location: register.php');
                        exit;
                    }
                } else {
                    $erreur_profil = 'Une erreur est survenue lors de la mise à jour du profil.';
                }
            }
        }
    }
}
?>

<main class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <button class="sidebar-toggle" aria-label="Ouvrir/Fermer le menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sidebar-icon">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                
                <div class="sidebar-content">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <div class="avatar-circle">
                                    <?= substr(htmlspecialchars($utilisateur_nom), 0, 1) ?>
                                </div>
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name">
                                    <?= htmlspecialchars($utilisateur_nom) ?>
                                    <?php if ($est_contributeur): ?>
                                        <i class="fas fa-check-circle certified-icon" title="Contributeur certifié"></i>
                                    <?php endif; ?>
                                </h2>
                                <p class="profile-email"><?= htmlspecialchars($utilisateur['email']) ?></p>
                                <p class="profile-date">Membre depuis <?= date('F Y', strtotime($utilisateur['date_inscription'])) ?></p>
                                <?php if ($est_contributeur): ?>
                                    <p class="profile-status">Contributeur certifié</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button class="btn btn-outline edit-profile-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Modifier le Profil</span>
                            </button>
                            
                            <a href="deconnexion.php" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Se Déconnecter</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Formulaire de modification du profil -->
                    <div class="edit-profile-form hidden">
                        <div class="form-header">
                            <h3>Modifier le Profil</h3>
                            <button type="button" class="close-form" aria-label="Fermer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        
                        <?php if (!empty($erreur_profil)): ?>
                            <div class="alert alert-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span><?= $erreur_profil ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_profil)): ?>
                            <div class="alert alert-success">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span><?= $success_profil ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <form action="profil.php" method="post" class="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
                            </div>
                            
                            <div class="password-section">
                                <div class="password-header">
                                    <h4>Changer le mot de passe</h4>
                                    <button type="button" class="toggle-password-section">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-icon">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="password-fields hidden">
                                    <div class="form-group password-toggle">
                                        <label for="mot_de_passe_actuel">Mot de passe actuel</label>
                                        <div class="password-input">
                                            <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel">
                                            <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group password-toggle">
                                        <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                                        <div class="password-input">
                                            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe">
                                            <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group password-toggle">
                                        <label for="confirmation_mot_de_passe">Confirmer le nouveau mot de passe</label>
                                        <div class="password-input">
                                            <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe">
                                            <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($est_admin): ?>
                        <!-- Section Admin -->
                        <div class="admin-card">
                            <div class="admin-header">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="admin-icon">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <h3>Administration</h3>
                            </div>
                            <p>Gérez QuizMaster depuis le tableau de bord administrateur.</p>
                            <a href="/quizmaster/admin/index.php" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                    <line x1="9" y1="21" x2="9" y2="9"></line>
                                </svg>
                                <span>Tableau de Bord Admin</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Contenu principal -->
            <div class="profile-main">
                <h1 class="page-title">Mon Profil</h1>
                
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $stats['total_quiz'] ?></div>
                            <div class="stat-label">Quiz complétés</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $stats['score_moyen'] ?>%</div>
                            <div class="stat-label">Score moyen</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 15c6.627 0 12 1.343 12 3v3H0v-3c0-1.657 5.373-3 12-3z"></path>
                                <path d="M12 12c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= count($stats['badges']) ?></div>
                            <div class="stat-label">Badges obtenus</div>
                        </div>
                    </div>
                </div>

                <div class="profile-tabs">
                    <div class="tabs-header" role="tablist">
                        <button class="tab-btn active" data-tab="stats" role="tab" aria-selected="true" aria-controls="stats">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            <span>Statistiques</span>
                        </button>
                        
                        <button class="tab-btn" data-tab="history" role="tab" aria-selected="false" aria-controls="history">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Historique</span>
                        </button>
                        
                        <button class="tab-btn" data-tab="badges" role="tab" aria-selected="false" aria-controls="badges">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5"></path>
                                <path d="M2 12l10 5 10-5"></path>
                            </svg>
                            <span>Badges</span>
                        </button>
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane active" id="stats" role="tabpanel">
                            <div class="stats-grid">
                                <div class="stats-card">
                                    <div class="card-header">
                                        <h3>Performance par catégorie</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($stats['categories'])): ?>
                                            <div class="category-stats">
                                                <?php foreach ($stats['categories'] as $cat): ?>
                                                    <div class="category-stat">
                                                        <div class="category-info">
                                                            <div class="category-color" style="background-color: <?= $cat['couleur'] ?>"></div>
                                                            <div class="category-name"><?= htmlspecialchars($cat['nom']) ?></div>
                                                        </div>
                                                        <div class="category-progress">
                                                            <div class="progress-bar">
                                                                <div class="progress-fill" style="width: <?= $cat['score_moyen'] ?>%; background-color: <?= $cat['couleur'] ?>"></div>
                                                            </div>
                                                            <div class="progress-value"><?= round($cat['score_moyen']) ?>%</div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-data">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="no-data-icon">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                                </svg>
                                                <p>Aucune donnée disponible</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="stats-card">
                                    <div class="card-header">
                                        <h3>Quiz par difficulté</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="difficultyChart"></canvas>
                                        </div>
                                        <div class="chart-data" id="difficultyData" data-difficulties='<?= json_encode(array_column($stats['difficultes'] ?? [], 'nom')) ?>' data-counts='<?= json_encode(array_column($stats['difficultes'] ?? [], 'total_quiz')) ?>'></div>
                                    </div>
                                </div>
                                
                                <div class="stats-card">
                                    <div class="card-header">
                                        <h3>Progression mensuelle</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="monthlyChart"></canvas>
                                        </div>
                                        <div class="chart-data" id="monthlyData" data-months='<?= json_encode(array_map(function($item) {
                                            return date('M', strtotime($item['mois'] . '-01'));
                                        }, $stats['progression_mensuelle'] ?? [])) ?>' data-scores='<?= json_encode(array_map(function($item) {
                                            return round($item['score_moyen']);
                                        }, $stats['progression_mensuelle'] ?? [])) ?>'></div>
                                    </div>
                                </div>

                                <!-- Section : Quiz créés -->
                                <div class="stats-card">
                                    <div class="card-header">
                                        <h3>Quiz créés</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="create-quiz-button">
                                            <a href="create_quiz.php" class="btn btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                                    <path d="M12 5v14"></path>
                                                    <path d="M5 12h14"></path>
                                                </svg>
                                                <span>Créer un nouveau quiz</span>
                                            </a>
                                        </div>
                                        <?php if (!empty($created_quizzes)): ?>
                                            <div class="created-quizzes">
                                                <?php foreach ($created_quizzes as $quiz): ?>
                                                    <div class="created-quiz-item">
                                                        <div class="quiz-info">
                                                            <h4 class="quiz-title"><?= htmlspecialchars($quiz['titre']) ?></h4>
                                                            <div class="quiz-details">
                                                                <span class="quiz-category"><?= htmlspecialchars($quiz['categorie_nom'] ?? 'Non spécifiée') ?></span>
                                                                <span class="quiz-difficulty"><?= htmlspecialchars($quiz['difficulte_nom'] ?? 'Non spécifiée') ?></span>
                                                                <span class="quiz-status <?= strtolower($quiz['status']) ?>">
                                                                    <?= ucfirst($quiz['status']) ?>
                                                                </span>
                                                                <span class="quiz-date"><?= date('d/m/Y', strtotime($quiz['created_at'])) ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="quiz-actions">
                                                            <a href="edit_quiz.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-outline">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                                </svg>
                                                                <span>Modifier</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-data">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="no-data-icon">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                                </svg>
                                                <p>Aucun quiz créé</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="history" role="tabpanel">
                            <div class="history-card">
                                <div class="card-header">
                                    <h3>Historique des quiz</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($stats['quiz_recents'])): ?>
                                        <div class="quiz-history">
                                            <?php foreach ($stats['quiz_recents'] as $quiz): ?>
                                                <div class="quiz-history-item">
                                                    <div class="quiz-history-info">
                                                        <div class="quiz-category" style="background-color: <?= $quiz['categorie_couleur'] ?? '#4f46e5' ?>">
                                                            <span><?= htmlspecialchars($quiz['categorie_nom']) ?></span>
                                                        </div>
                                                        <div class="quiz-details">
                                                            <div class="quiz-difficulty">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="quiz-icon">
                                                                    <path d="M18 20V10"></path>
                                                                    <path d="M12 20V4"></path>
                                                                    <path d="M6 20v-6"></path>
                                                                </svg>
                                                                <span><?= htmlspecialchars($quiz['difficulte_nom']) ?></span>
                                                            </div>
                                                            <div class="quiz-date">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="quiz-icon">
                                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                                </svg>
                                                                <span><?= date('d/m/Y H:i', strtotime($quiz['date_completion'])) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="quiz-history-score <?= ($quiz['score'] / $quiz['total'] >= 0.7) ? 'high-score' : '' ?>">
                                                        <span class="score-value"><?= $quiz['score'] ?></span>
                                                        <span class="score-separator">/</span>
                                                        <span class="score-total"><?= $quiz['total'] ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-data">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="no-data-icon">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                            </svg>
                                            <p>Aucun quiz complété</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="badges" role="tabpanel">
                            <div class="badges-card">
                                <div class="card-header">
                                    <h3>Badges et récompenses</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($stats['badges'])): ?>
                                        <div class="badges-grid">
                                            <?php foreach ($stats['badges'] as $badge): ?>
                                                <div class="badge-item">
                                                    <div class="badge-icon" style="background-color: <?= $badge['categorie_couleur'] ?? '#4f46e5' ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 15l-8-8h16l-8 8z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="badge-info">
                                                        <h4 class="badge-name"><?= htmlspecialchars($badge['nom']) ?></h4>
                                                        <p class="badge-category"><?= htmlspecialchars($badge['categorie_nom'] ?? 'Général') ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-data">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="no-data-icon">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                            </svg>
                                            <p>Aucun badge obtenu</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
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

.profile-page {
    padding: 2rem 0 4rem;
}

/* Layout */
.profile-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

/* Sidebar */
.profile-sidebar {
    position: relative;
}

.sidebar-toggle {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
    padding: 0.5rem;
    border-radius: 0.5rem;
    color: var(--text-color);
    transition: var(--transition);
}

.sidebar-toggle:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.sidebar-icon {
    width: 1.5rem;
    height: 1.5rem;
}

.sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Profile Card */
.profile-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: var(--transition);
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.profile-avatar {
    margin-right: 1rem;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), #818cf8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
}

.profile-info {
    flex: 1;
}

.profile-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
    display: inline-flex;
    align-items: center;
}

.profile-email {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0 0 0.25rem;
}

.profile-date {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin: 0;
}

.profile-status {
    font-size: 0.75rem;
    color: var(--secondary-color);
    margin: 0;
    font-weight: 500;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Style pour l'icône de contributeur certifié */
.certified-icon {
    color: #4f46e5;
    margin-left: 5px;
    font-size: 0.85em;
    vertical-align: middle;
    animation: certifiedPulse 2s infinite;
}

@keyframes certifiedPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    transition: var(--transition);
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

.btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

/* Edit Profile Form */
.edit-profile-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: var(--transition);
}

.edit-profile-form.hidden {
    display: none;
}

.form-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.form-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.close-form {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    transition: var(--transition);
}

.close-form:hover {
    color: var(--text-color);
}

.close-form svg {
    width: 1.25rem;
    height: 1.25rem;
}

.alert {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
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

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.password-section {
    margin-bottom: 1.25rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    overflow: hidden;
}

.password-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background-color: var(--background-color);
    cursor: pointer;
}

.password-header h4 {
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0;
}

.toggle-password-section {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    transition: var(--transition);
}

.toggle-password-section:hover {
    color: var(--text-color);
}

.toggle-icon {
    width: 1rem;
    height: 1rem;
}

.password-fields {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.password-fields.hidden {
    display: none;
}

.password-input {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
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

.form-actions {
    display: flex;
    justify-content: flex-end;
}

/* Admin Card */
.admin-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    border-left: 4px solid var(--primary-color);
}

.admin-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.admin-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: var(--primary-color);
    margin-right: 0.75rem;
}

.admin-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    color: var(--primary-color);
}

.admin-card p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0 0 1.25rem;
}

/* Main Content */
.profile-main {
    flex: 1;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 1.5rem;
    color: var(--text-color);
}

/* Stats Overview */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    background-color: var(--primary-light);
    color: var(--primary-color);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    color: var(--text-color);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Tabs */
.profile-tabs {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.tabs-header {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--background-color);
}

.tab-btn {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted);
    transition: var(--transition);
    display: flex;
    align-items: center;
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

.tab-content {
    padding: 1.5rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.stats-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--background-color);
}

.card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-color);
}

.card-body {
    padding: 1.5rem;
}

/* Category Stats */
.category-stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.category-stat {
    display: flex;
    align-items: center;
}

.category-info {
    display: flex;
    align-items: center;
    width: 150px;
    margin-right: 1rem;
}

.category-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.category-name {
    font-size: 0.875rem;
    font-weight: 500;
}

.category-progress {
    flex: 1;
    display: flex;
    align-items: center;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background-color: var(--border-color);
    border-radius: 4px;
    overflow: hidden;
    margin-right: 0.75rem;
}

.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-value {
    font-size: 0.875rem;
    font-weight: 500;
    width: 40px;
    text-align: right;
}

/* Charts */
.chart-container {
    height: 250px;
}

/* Created Quizzes */
.create-quiz-button {
    margin-bottom: 1.5rem;
    text-align: right;
}

.created-quizzes {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.created-quiz-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--background-color);
    border-radius: 0.5rem;
    padding: 1rem;
    transition: var(--transition);
}

.created-quiz-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.quiz-info {
    flex: 1;
}

.quiz-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.quiz-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.quiz-category, .quiz-difficulty, .quiz-status, .quiz-date {
    display: flex;
    align-items: center;
}

.quiz-status.pending {
    color: #f59e0b;
}

.quiz-status.approved {
    color: #10b981;
}

.quiz-status.rejected {
    color: #ef4444;
}

.quiz-actions {
    display: flex;
    gap: 0.5rem;
}

/* History */
.quiz-history {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quiz-history-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background-color: var(--background-color);
    border-radius: 0.5rem;
    transition: var(--transition);
}

.quiz-history-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.quiz-history-info {
    flex: 1;
}

.quiz-category {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.quiz-details {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.quiz-difficulty, .quiz-date {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.quiz-icon {
    width: 0.875rem;
    height: 0.875rem;
    margin-right: 0.25rem;
}

.quiz-history-score {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.quiz-history-score.high-score {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.score-value {
    font-size: 1.125rem;
}

.score-separator {
    margin: 0 0.25rem;
    opacity: 0.7;
}

.score-total {
    opacity: 0.7;
}

/* Badges */
.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1.5rem;
}

.badge-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem 1rem;
    background-color: var(--background-color);
    border-radius: 0.75rem;
    transition: var(--transition);
}

.badge-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.badge-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: white;
}

.badge-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.badge-name {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
}

.badge-category {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin: 0;
}

/* No Data */
.no-data {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--text-muted);
}

.no-data-icon {
    width: 3rem;
    height: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-data p {
    font-size: 0.875rem;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 992px) {
    .profile-layout {
        grid-template-columns: 1fr;
    }
    
    .sidebar-toggle {
        display: block;
    }
    
    .sidebar-content {
        display: none;
    }
    
    .sidebar-content.active {
        display: flex;
    }
    
    .profile-card, .edit-profile-form, .admin-card {
        max-width: 500px;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
    
    .tabs-header {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1;
        justify-content: center;
    }
    
    .quiz-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .created-quiz-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .quiz-actions {
        margin-top: 1rem;
        align-self: flex-end;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quiz-history-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .quiz-history-score {
        margin-top: 1rem;
        align-self: flex-end;
    }
    
    .badges-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .create-quiz-button {
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            panes.forEach(p => p.classList.remove('active'));

            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    // Gestion du menu latéral sur mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebarContent = document.querySelector('.sidebar-content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebarContent.classList.toggle('active');
        });
    }

    // Gestion du formulaire de modification du profil
    const editProfileBtn = document.querySelector('.edit-profile-btn');
    const editProfileForm = document.querySelector('.edit-profile-form');
    const closeFormBtn = document.querySelector('.close-form');
    
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            editProfileForm.classList.remove('hidden');
        });
    }
    
    if (closeFormBtn) {
        closeFormBtn.addEventListener('click', function() {
            editProfileForm.classList.add('hidden');
        });
    }

    // Gestion de la section mot de passe
    const togglePasswordSection = document.querySelector('.toggle-password-section');
    const passwordFields = document.querySelector('.password-fields');
    
    if (togglePasswordSection) {
        togglePasswordSection.addEventListener('click', function() {
            passwordFields.classList.toggle('hidden');
            const icon = this.querySelector('.toggle-icon');
            if (passwordFields.classList.contains('hidden')) {
                icon.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>';
            } else {
                icon.innerHTML = '<polyline points="18 15 12 9 6 15"></polyline>';
            }
        });
    }

    // Gestion de l'affichage/masquage des mots de passe
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('svg');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        });
    });

    // Initialisation des graphiques
    if (typeof Chart !== 'undefined') {
        // Graphique de difficulté
        const difficultyData = document.getElementById('difficultyData');
        if (difficultyData) {
            const difficulties = JSON.parse(difficultyData.dataset.difficulties || '[]');
            const counts = JSON.parse(difficultyData.dataset.counts || '[]');
            
            if (difficulties.length > 0) {
                const difficultyChart = new Chart(document.getElementById('difficultyChart'), {
                    type: 'doughnut',
                    data: {
                        labels: difficulties,
                        datasets: [{
                            data: counts,
                            backgroundColor: [
                                '#10b981', // Facile - vert
                                '#f59e0b', // Intermédiaire - orange
                                '#ef4444'  // Difficile - rouge
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    padding: 20
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        }
        
        // Graphique mensuel
        const monthlyData = document.getElementById('monthlyData');
        if (monthlyData) {
            const months = JSON.parse(monthlyData.dataset.months || '[]');
            const scores = JSON.parse(monthlyData.dataset.scores || '[]');
            
            if (months.length > 0) {
                const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Score moyen',
                            data: scores,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>