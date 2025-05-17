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
    $description = $_POST['description'] ?? '';
    $icone = $_POST['icone'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    
    // Vérification des données
    if (empty($nom)) {
        $erreur = 'Le nom est requis';
    } elseif (empty($description)) {
        $erreur = 'La description est requise';
    } elseif (empty($icone)) {
        $erreur = 'L\'icône est requise';
    } elseif (empty($couleur)) {
        $erreur = 'La couleur est requise';
    } else {
        // Tout est bon, ajouter la catégorie
        $result = ajouterCategorie($nom, $description, $icone, $couleur);
        
        if ($result) {
            $_SESSION['message'] = 'La catégorie a été ajoutée avec succès';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $erreur = 'Une erreur est survenue lors de l\'ajout de la catégorie';
        }
    }
}

// Liste des icônes disponibles
$icones = [
    'fa-book' => 'Livre',
    'fa-flask' => 'Sciences',
    'fa-globe' => 'Globe',
    'fa-palette' => 'Arts',
    'fa-trophy' => 'Sport',
    'fa-star' => 'Étoile',
    'fa-music' => 'Musique',
    'fa-car' => 'Voiture',
    'fa-film' => 'Cinéma',
    'fa-history' => 'Histoire',
    'fa-utensils' => 'Cuisine',
    'fa-landmark' => 'Monument',
    'fa-users' => 'Personnes',
    'fa-leaf' => 'Nature',
    'fa-graduation-cap' => 'Éducation'
];

// Liste des couleurs prédéfinies
$couleurs = [
    '#ef4444' => 'Rouge',
    '#f97316' => 'Orange',
    '#eab308' => 'Jaune',
    '#22c55e' => 'Vert',
    '#3b82f6' => 'Bleu',
    '#8b5cf6' => 'Violet',
    '#ec4899' => 'Rose',
    '#6b7280' => 'Gris',
    '#1e293b' => 'Bleu foncé'
];

// Inclure l'en-tête
$titre_page = "Ajouter une catégorie";
include '../includes/header.php';
?>

<main class="categories-page">
    <div class="container">
        <section class="category-add">
            <div class="section-header">
                <h1 class="section-title">Ajouter une catégorie</h1>
                <p class="section-description">Créez une nouvelle catégorie pour les quiz</p>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                        <span>Retour à la liste</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($erreur)): ?>
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <?= $erreur ?>
                    <button type="button" class="close-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

            <form method="post" class="form-large">
                <div class="form-group">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="icone">Icône <span class="required">*</span></label>
                        <input type="hidden" id="icone" name="icone" value="<?= htmlspecialchars($_POST['icone'] ?? 'fa-star') ?>">
                        <div class="icon-preview">
                            <i id="icon-preview" class="fas <?= htmlspecialchars($_POST['icone'] ?? 'fa-star') ?>"></i>
                            <span id="icon-name"><?= $icones[$_POST['icone'] ?? 'fa-star'] ?? 'Étoile' ?></span>
                        </div>
                        <div class="icon-picker">
                            <?php foreach ($icones as $icone => $nom_icone): ?>
                                <div class="icon-option <?= ($icone === ($_POST['icone'] ?? 'fa-star')) ? 'selected' : '' ?>" data-icon="<?= $icone ?>" data-name="<?= $nom_icone ?>">
                                    <i class="fas <?= $icone ?>"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="couleur">Couleur <span class="required">*</span></label>
                        <input type="hidden" id="couleur" name="couleur" value="<?= htmlspecialchars($_POST['couleur'] ?? '#3b82f6') ?>">
                        <div class="color-preview">
                            <div id="color-preview" style="background-color: <?= htmlspecialchars($_POST['couleur'] ?? '#3b82f6') ?>;"></div>
                            <span id="color-name"><?= $couleurs[$_POST['couleur'] ?? '#3b82f6'] ?? 'Bleu' ?></span>
                        </div>
                        <div class="color-picker">
                            <?php foreach ($couleurs as $couleur => $nom_couleur): ?>
                                <div class="color-option <?= ($couleur === ($_POST['couleur'] ?? '#3b82f6')) ? 'selected' : '' ?>" 
                                     data-color="<?= $couleur ?>" data-name="<?= $nom_couleur ?>" 
                                     style="background-color: <?= $couleur ?>;"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="custom-color">
                            <label for="custom-color">Couleur personnalisée</label>
                            <input type="color" id="custom-color" class="form-control" value="<?= htmlspecialchars($_POST['couleur'] ?? '#3b82f6') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Aperçu</label>
                    <div class="category-preview">
                        <div class="category-card" style="--category-color: <?= htmlspecialchars($_POST['couleur'] ?? '#3b82f6') ?>;">
                            <div class="category-icon">
                                <i class="fas <?= htmlspecialchars($_POST['icone'] ?? 'fa-star') ?>"></i>
                            </div>
                            <div class="category-content">
                                <h3 class="category-title" id="preview-name"><?= htmlspecialchars($_POST['nom'] ?? 'Nouvelle catégorie') ?></h3>
                                <p class="category-description" id="preview-description"><?= htmlspecialchars($_POST['description'] ?? 'Description de la catégorie') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Ajouter la catégorie
                    </button>
                </div>
            </form>
        </section>
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

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Form Styles */
.form-large {
    max-width: 800px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-group .required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-light);
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.form-row .form-group {
    flex: 1;
    min-width: 250px;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
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
    border: none;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
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

/* Icon and Color Picker Styles */
.icon-preview, .color-preview {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background-color: var(--card-background);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.icon-preview i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    color: var(--text-color);
}

.color-preview div {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    margin-right: 0.75rem;
    border: 1px solid var(--border-color);
}

.icon-picker, .color-picker {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.icon-option, .color-option {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    background-color: var(--card-background);
    transition: var(--transition);
}

.icon-option:hover, .color-option:hover {
    background-color: var(--primary-light);
}

.icon-option.selected, .color-option.selected {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-light);
}

.icon-option i {
    font-size: 1.25rem;
}

.color-option {
    border: 1px solid var(--border-color);
}

.custom-color input[type="color"] {
    height: 48px;
    padding: 4px;
    width: 100%;
    border-radius: 8px;
}

/* Category Preview Styles */
.category-preview {
    max-width: 400px;
    margin: 0 auto;
}

.category-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    border-top: 4px solid var(--category-color, var(--primary-color));
    padding: 1.5rem;
    text-align: center;
}

.category-card .category-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: var(--category-color, var(--primary-color));
    color: white;
    border-radius: 50%;
    font-size: 1.5rem;
    margin: 0 auto 1rem;
}

.category-content {
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
}

/* Alert Styles */
.alert {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    position: relative;
    font-size: 0.875rem;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.alert-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
}

.close-alert {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: inherit;
    opacity: 0.7;
}

.close-alert:hover {
    opacity: 1;
}

.close-icon {
    width: 1rem;
    height: 1rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.category-add {
    animation: fadeIn 0.5s ease-out;
}

/* Responsive Design */
@media (max-width: 992px) {
    .section-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .form-row .form-group {
        min-width: 100%;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .header-actions .btn {
        width: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des alertes
    const closeAlerts = document.querySelectorAll('.close-alert');
    closeAlerts.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.style.display = 'none';
        });
    });

    // Aperçu en temps réel
    const nomInput = document.getElementById('nom');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    
    nomInput.addEventListener('input', function() {
        previewName.textContent = this.value || 'Nouvelle catégorie';
    });
    
    descriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value || 'Description de la catégorie';
    });
    
    // Sélection d'icône
    const iconInput = document.getElementById('icone');
    const iconPreviewElement = document.getElementById('icon-preview');
    const iconNameElement = document.getElementById('icon-name');
    const iconOptions = document.querySelectorAll('.icon-option');
    
    iconOptions.forEach(option => {
        option.addEventListener('click', function() {
            const icon = this.getAttribute('data-icon');
            const name = this.getAttribute('data-name');
            
            // Mettre à jour l'input caché
            iconInput.value = icon;
            
            // Mettre à jour l'aperçu
            iconPreviewElement.className = 'fas ' + icon;
            iconNameElement.textContent = name;
            
            // Mettre à jour l'icône dans l'aperçu de la catégorie
            document.querySelector('.category-card .category-icon i').className = 'fas ' + icon;
            
            // Mettre à jour la sélection
            iconOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Sélection de couleur
    const colorInput = document.getElementById('couleur');
    const colorPreviewElement = document.getElementById('color-preview');
    const colorNameElement = document.getElementById('color-name');
    const colorOptions = document.querySelectorAll('.color-option');
    const customColorInput = document.getElementById('custom-color');
    
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            const name = this.getAttribute('data-name');
            
            // Mettre à jour les inputs
            colorInput.value = color;
            customColorInput.value = color;
            
            // Mettre à jour l'aperçu
            colorPreviewElement.style.backgroundColor = color;
            colorNameElement.textContent = name;
            
            // Mettre à jour la couleur dans l'aperçu de la catégorie
            document.querySelector('.category-card').style.setProperty('--category-color', color);
            document.querySelector('.category-card .category-icon').style.backgroundColor = color;
            
            // Mettre à jour la sélection
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Couleur personnalisée
    customColorInput.addEventListener('input', function() {
        const color = this.value;
        
        // Mettre à jour l'input caché
        colorInput.value = color;
        
        // Mettre à jour l'aperçu
        colorPreviewElement.style.backgroundColor = color;
        colorNameElement.textContent = 'Personnalisée';
        
        // Mettre à jour la couleur dans l'aperçu de la catégorie
        document.querySelector('.category-card').style.setProperty('--category-color', color);
        document.querySelector('.category-card .category-icon').style.backgroundColor = color;
        
        // Désélectionner toutes les options prédéfinies
        colorOptions.forEach(opt => opt.classList.remove('selected'));
    });
});
</script>

<?php include '../includes/footer.php'; ?>