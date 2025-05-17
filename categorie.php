<?php
$titre_page = "Catégories";
require_once 'includes/header.php';

// Récupérer toutes les catégories
$categories = obtenirCategories();

// Récupérer tous les niveaux de difficulté
$difficultes = obtenirDifficultes();

// Si un ID de catégorie est spécifié, afficher les détails de cette catégorie
$categorie_selectionnee = null;
if (isset($_GET['id'])) {
    $categorie_id = (int)$_GET['id'];
    $categorie_selectionnee = obtenirCategorie($categorie_id);

    if ($categorie_selectionnee) {
        $titre_page = "Catégorie: " . $categorie_selectionnee['nom'];
    }
}

// Récupérer les quiz communautaires approuvés avec le nom du créateur
require_once 'includes/db.php';
$database = new Database();
$db = $database->connect();
$query_community = "
    SELECT uq.*, c.nom as categorie_nom, c.icone as categorie_icone, c.couleur as categorie_couleur, u.nom as createur_nom, u.est_contributeur
    FROM user_quizzes uq
    JOIN categories c ON uq.categorie_id = c.id
    JOIN utilisateurs u ON uq.utilisateur_id = u.id
    WHERE uq.status = 'approved'
    ORDER BY uq.created_at DESC
";
$stmt_community = $db->prepare($query_community);
$stmt_community->execute();
$community_quizzes = $stmt_community->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="categories-page">
    <div class="container">
        <?php if ($categorie_selectionnee): ?>
            <!-- Affichage d'une catégorie spécifique -->
            <section class="category-detail">
                <div class="category-header" style="--category-color: <?= $categorie_selectionnee['couleur'] ?>">
                    <div class="category-icon-wrapper">
                        <div class="category-icon">
                            <i class="fas <?= $categorie_selectionnee['icone'] ?>"></i>
                        </div>
                    </div>
                    <div class="category-info">
                        <h1 class="category-title"><?= htmlspecialchars($categorie_selectionnee['nom']) ?></h1>
                        <p class="category-description"><?= htmlspecialchars($categorie_selectionnee['description']) ?></p>
                    </div>
                </div>

                <div class="difficulty-section">
                    <div class="section-header">
                        <h2 class="section-title">Choisissez un niveau de difficulté</h2>
                        <p class="section-description">Sélectionnez le niveau qui correspond à vos connaissances</p>
                    </div>

                    <div class="difficulty-grid">
                        <?php foreach ($difficultes as $difficulte): ?>
                            <div class="difficulty-card">
                                <div class="difficulty-level" style="--difficulty-color: <?= getDifficultyColor($difficulte['id']) ?>">
                                    <span class="difficulty-badge"><?= htmlspecialchars($difficulte['nom']) ?></span>
                                </div>
                                <div class="difficulty-content">
                                    <h3 class="difficulty-title"><?= htmlspecialchars($difficulte['nom']) ?></h3>
                                    <p class="difficulty-description">
                                        <?php if ($difficulte['id'] == 1): ?>
                                            Questions simples pour débuter dans cette catégorie.
                                        <?php elseif ($difficulte['id'] == 2): ?>
                                            Questions intermédiaires pour tester vos connaissances.
                                        <?php else: ?>
                                            Questions avancées pour les experts de la catégorie.
                                        <?php endif; ?>
                                    </p>
                                    <a href="quiz.php?categorie=<?= $categorie_selectionnee['id'] ?>&difficulte=<?= $difficulte['id'] ?>" class="btn btn-primary">
                                        <span>Commencer le quiz</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="back-link">
                    <a href="categorie.php" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                        <span>Retour aux catégories</span>
                    </a>
                </div>
            </section>
        <?php else: ?>
            <!-- Affichage de toutes les catégories -->
            <section class="categories-list">
                <div class="section-header">
                    <h1 class="section-title">Catégories de Quiz</h1>
                    <p class="section-description">Choisissez une catégorie pour commencer un quiz</p>
                </div>

                <div class="categories-grid">
                    <?php foreach ($categories as $categorie): ?>
                        <div class="category-card" style="--category-color: <?= $categorie['couleur'] ?>">
                            <div class="category-icon">
                                <i class="fas <?= $categorie['icone'] ?>"></i>
                            </div>
                            <div class="category-content">
                                <h3 class="category-title"><?= htmlspecialchars($categorie['nom']) ?></h3>
                                <p class="category-description"><?= htmlspecialchars($categorie['description']) ?></p>
                                <a href="categorie.php?id=<?= $categorie['id'] ?>" class="category-link">
                                    <span>Voir les quiz</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="category-link-icon">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Affichage des quiz communautaires -->
            <?php if (!empty($community_quizzes)): ?>
                <section class="community-quizzes">
                    <div class="section-header">
                        <h2 class="section-title">Quiz Communautaires Approuvés</h2>
                        <p class="section-description">Découvrez les quiz créés par la communauté</p>
                    </div>

                    <div class="categories-grid">
                        <?php foreach ($community_quizzes as $quiz): ?>
                            <div class="category-card" style="--category-color: <?= $quiz['categorie_couleur'] ?>">
                                <div class="category-icon">
                                    <i class="fas <?= $quiz['categorie_icone'] ?>"></i>
                                </div>
                                <div class="category-content">
                                    <h3 class="category-title"><?= htmlspecialchars($quiz['titre']) ?></h3>
                                    <p class="category-description">
                                        <?= htmlspecialchars(substr($quiz['description'], 0, 100)) . (strlen($quiz['description']) > 100 ? '...' : '') ?>
                                    </p>
                                    <p class="category-meta">
                                        <span>Catégorie: <span style="color: <?= $quiz['categorie_couleur'] ?>"><?= htmlspecialchars($quiz['categorie_nom']) ?></span></span>
                                        <span>Créé par: <?= htmlspecialchars($quiz['createur_nom']) ?>
                                            <?php if ($quiz['est_contributeur']): ?>
                                                <i class="fas fa-check-circle certified-icon" title="Contributeur certifié"></i>
                                            <?php endif; ?>
                                        </span>
                                    </p>
                                    <a href="community_quiz.php?id=<?= $quiz['id'] ?>" class="category-link">
                                        <span>Jouer</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="category-link-icon">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
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

.categories-page {
    padding: 2rem 0 4rem;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.section-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 1rem;
    transition: var(--transition);
    text-decoration: none;
    cursor: pointer;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-outline {
    background-color: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-light);
    transform: translateY(-2px);
}

.btn-icon {
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
}

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Category Detail Styles */
.category-detail {
    animation: fadeIn 0.5s ease-out;
}

.category-header {
    display: flex;
    align-items: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.category-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background-color: var(--category-color, var(--primary-color));
}

.category-icon-wrapper {
    margin-right: 2rem;
}

.category-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: var(--category-color, var(--primary-color));
    color: white;
    border-radius: 50%;
    font-size: 1.5rem;
    margin: -30px auto 1.5rem;
}

.category-info {
    flex: 1;
}

.category-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.category-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 700px;
}

/* Difficulty Styles */
.difficulty-section {
    margin-bottom: 3rem;
}

.difficulty-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.difficulty-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
}

.difficulty-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.difficulty-level {
    padding: 1.5rem;
    background: linear-gradient(to right, var(--difficulty-color, var(--primary-color)), rgba(var(--difficulty-color, var(--primary-color)), 0.7));
    color: white;
    text-align: center;
}

.difficulty-badge {
    font-weight: 600;
    font-size: 1.25rem;
}

.difficulty-content {
    padding: 1.5rem;
}

.difficulty-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.difficulty-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    min-height: 60px;
}

/* Categories Grid Styles */
.categories-list {
    animation: fadeIn 0.5s ease-out;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.category-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    border-top: 4px solid var(--category-color, var(--primary-color));
    padding: 2rem;
    text-align: center;
    min-height: 280px; /* Hauteur minimale pour uniformiser les cartes */
    display: flex;
    flex-direction: column;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.category-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.category-card .category-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.category-card .category-description {
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    min-height: 60px;
    flex-grow: 1;
}

.category-link {
    display: inline-flex;
    align-items: center;
    color: var(--category-color, var(--primary-color));
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
}

.category-link:hover {
    text-decoration: underline;
}

.category-link-icon {
    width: 1rem;
    height: 1rem;
    margin-left: 0.25rem;
}

/* Community Quizzes Styles */
.community-quizzes {
    margin-top: 4rem;
}

.category-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

/* Back Link */
.back-link {
    margin-top: 2rem;
    text-align: center;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.category-card, .difficulty-card {
    animation: fadeIn 0.6s ease-out forwards;
}

.category-card:nth-child(2), .difficulty-card:nth-child(2) {
    animation-delay: 0.2s;
}

.category-card:nth-child(3), .difficulty-card:nth-child(3) {
    animation-delay: 0.4s;
}

/* Responsive Design */
@media (max-width: 992px) {
    .category-header {
        flex-direction: column;
        text-align: center;
    }

    .category-icon-wrapper {
        margin-right: 0;
        margin-bottom: 1.5rem;
    }

    .category-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.75rem;
    }

    .difficulty-description {
        min-height: auto;
    }

    .category-card .category-description {
        min-height: auto;
    }

    .category-card {
        min-height: auto;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .back-link .btn {
        width: auto;
    }
}
</style>

<?php
// Fonction pour obtenir la couleur en fonction du niveau de difficulté
function getDifficultyColor($difficulty_id) {
    switch ($difficulty_id) {
        case 1:
            return "#10b981"; // Vert pour facile
        case 2:
            return "#f59e0b"; // Orange pour intermédiaire
        case 3:
            return "#ef4444"; // Rouge pour difficile
        default:
            return "#4f46e5"; // Couleur par défaut
    }
}
?>

<script>
// Script pour s'assurer que les liens fonctionnent correctement
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les liens de navigation
    const navLinks = document.querySelectorAll('a.btn, a.category-link');

    // S'assurer qu'ils se comportent normalement
    navLinks.forEach(link => {
        // Supprimer tout gestionnaire d'événement existant qui pourrait interférer
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
