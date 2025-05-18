<?php
$titre_page = "Accueil";
require_once 'includes/header.php';

// Récupérer quelques catégories pour l'affichage sur la page d'accueil
$database = new Database();
$db = $database->connect();
$query = "SELECT * FROM categories ORDER BY nom LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$categories_populaires = $stmt->fetchAll();
?>

<main class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">QuizMaster</h1>
                <p class="hero-description">Testez vos connaissances avec notre quiz de culture générale</p>
                <div class="hero-buttons">
                    <a href="categorie.php" class="btn btn-primary">
                        <span>Voir toutes les catégories</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <?php if (!estConnecte()): ?>
                        <a href="register.php" class="btn btn-outline">
                            <span>S'inscrire</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hero-icon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>
    </section>

    <!-- Featured Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Catégories populaires</h2>
                <p class="section-description">Explorez nos catégories les plus populaires et testez vos connaissances</p>
            </div>

            <div class="categories-grid">
                <?php foreach ($categories_populaires as $categorie): ?>
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

            <div class="view-all">
                <a href="categorie.php" class="btn btn-primary">
                    <span>Voir toutes les catégories</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Comment ça marche ?</h2>
                <p class="section-description">Suivez ces étapes simples pour commencer à utiliser QuizMaster</p>
            </div>

            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <h3 class="step-title">Choisissez une catégorie</h3>
                    <p class="step-description">Sélectionnez parmi nos nombreuses catégories de quiz</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <h3 class="step-title">Répondez aux questions</h3>
                    <p class="step-description">Testez vos connaissances avec nos questions à choix multiples</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                    </div>
                    <h3 class="step-title">Suivez votre progression</h3>
                    <p class="step-description">Consultez vos résultats et améliorez vos connaissances</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <?php if (!estConnecte()): ?>
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 class="cta-title">Vous avez déjà un compte?</h2>
                    <p class="cta-description">Connectez-vous pour suivre votre progression et accéder à toutes les fonctionnalités</p>
                </div>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-outline">Connexion</a>
                    <a href="register.php" class="btn btn-primary">
                        <span>Inscription</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('a.btn, a.category-link');
    navLinks.forEach(link => {
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);
        newLink.addEventListener('click', function(e) {
            window.location.href = this.getAttribute('href');
        });
    });
});
</script>

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

.hero-section {
    padding: 4rem 0;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 0 0 2rem 2rem;
    margin-bottom: 4rem;
}

.hero-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.hero-content {
    max-width: 600px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    color: var(--primary-color);
    background: linear-gradient(to right, var(--primary-color), #818cf8);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
}

.hero-description {
    font-size: 1.25rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
}

.hero-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 300px;
    height: 300px;
}

.hero-icon {
    width: 100%;
    height: 100%;
    color: var(--primary-color);
    opacity: 0.8;
}

.categories-section {
    padding: 4rem 0;
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
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
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
    margin: -30px auto 1rem;
    font-size: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.category-content {
    padding: 1.5rem;
    text-align: center;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.category-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    min-height: 60px;
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

.view-all {
    text-align: center;
}

.how-it-works-section {
    padding: 4rem 0;
    background-color: #f3f4f6;
    border-radius: 2rem;
    margin: 4rem 0;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.step-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 2rem;
    text-align: center;
    position: relative;
    transition: var(--transition);
}

.step-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.step-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background-color: var(--primary-color);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.step-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1.5rem;
    color: var(--primary-color);
}

.step-icon svg {
    width: 100%;
    height: 100%;
}

.step-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.step-description {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.cta-section {
    padding: 4rem 0;
    background-color: #f3f4f6;
    border-radius: 2rem;
}

.cta-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, #818cf8 100%);
    border-radius: var(--border-radius);
    padding: 3rem;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: var(--shadow-lg);
}

.cta-content {
    margin-bottom: 2rem;
}

.cta-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-description {
    font-size: 1.125rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

.cta-section .btn {
    width: 100%;
}

.cta-section .btn-outline {
    border-color: white;
    color: white;
}

.cta-section .btn-outline:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.cta-section .btn-primary {
    background-color: white;
    color: var(--primary-color);
}

.cta-section .btn-primary:hover {
    background-color: rgba(255, 255, 255, 0.9);
}

@media (max-width: 992px) {
    .hero-container {
        flex-direction: column;
        text-align: center;
    }

    .hero-content {
        margin-bottom: 2rem;
    }

    .hero-buttons {
        justify-content: center;
    }

    .hero-image {
        width: 200px;
        height: 200px;
    }

    .cta-card {
        flex-direction: column;
        text-align: center;
    }

    .cta-content {
        max-width: 100%;
        margin-bottom: 2rem;
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.75rem;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-description {
        font-size: 1.125rem;
    }

    .cta-title {
        font-size: 1.75rem;
    }

    .cta-description {
        font-size: 1rem;
    }

    .cta-buttons {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .hero-buttons {
        flex-direction: column;
        width: 100%;
    }

    .hero-buttons .btn {
        width: 100%;
    }

    .step-card {
        padding: 1.5rem;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.hero-content, .category-card, .step-card {
    animation: fadeIn 0.6s ease-out forwards;
}

.category-card:nth-child(2) {
    animation-delay: 0.2s;
}

.category-card:nth-child(3) {
    animation-delay: 0.4s;
}

.step-card:nth-child(2) {
    animation-delay: 0.2s;
}

.step-card:nth-child(3) {
    animation-delay: 0.4s;
}
</style>

<?php require_once 'includes/footer.php'; ?>
