<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Filtres
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$difficulte_id = isset($_GET['difficulte']) ? (int)$_GET['difficulte'] : 0;
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

$filtre = [
    'categorie_id' => $categorie_id,
    'difficulte_id' => $difficulte_id,
    'recherche' => $recherche
];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$par_page = 20;

// Récupérer toutes les questions avec pagination et filtres
$questions = obtenirToutesLesQuestions($page, $par_page, $filtre);
$total_questions = obtenirNombreTotalQuestions($filtre);
$total_pages = ceil($total_questions / $par_page);

// Récupérer toutes les catégories et difficultés pour les filtres
$categories = obtenirToutesLesCategories();
$difficultes = obtenirToutesLesDifficultes();

// Inclure l'en-tête
$titre_page = "Gestion des questions";
include '../includes/header.php';
?>

<main class="questions-page">
    <div class="container">
        <section class="questions-list">
            <div class="section-header">
                <h1 class="section-title">Gestion des Questions</h1>
                <p class="section-description">Gérez les questions disponibles pour les quiz</p>
                <div class="header-actions">
                    <a href="add.php" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Ajouter une question</span>
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="close-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="categorie">Catégorie</label>
                            <select id="categorie" name="categorie" class="form-control">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= $categorie['id'] ?>" <?= $categorie_id == $categorie['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categorie['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label for="difficulte">Difficulté</label>
                            <select id="difficulte" name="difficulte" class="form-control">
                                <option value="">Toutes les difficultés</option>
                                <?php foreach ($difficultes as $difficulte): ?>
                                    <option value="<?= $difficulte['id'] ?>" <?= $difficulte_id == $difficulte['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($difficulte['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label for="recherche">Recherche</label>
                            <input type="text" id="recherche" name="recherche" class="form-control" placeholder="Rechercher une question" value="<?= htmlspecialchars($recherche) ?>">
                        </div>
                        
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                                Filtrer
                            </button>
                            <?php if ($categorie_id || $difficulte_id || $recherche): ?>
                                <a href="index.php" class="btn btn-outline btn-sm ml-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Réinitialiser
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tableau des questions -->
            <?php if (empty($questions)): ?>
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Aucune question trouvée.
                    <button type="button" class="close-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="close-icon">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Catégorie</th>
                                <th>Difficulté</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $index => $question): ?>
                                <tr data-animation-delay="<?= $index * 0.1 ?>s">
                                    <td><?= $question['id'] ?></td>
                                    <td><?= htmlspecialchars($question['question']) ?></td>
                                    <td><?= htmlspecialchars($question['categorie_nom']) ?></td>
                                    <td><?= htmlspecialchars($question['difficulte_nom']) ?></td>
                                    <td>
                                        <a href="view.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-outline">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <a href="edit.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <a href="delete.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&categorie=<?= $categorie_id ?>&difficulte=<?= $difficulte_id ?>&recherche=<?= urlencode($recherche) ?>" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                </svg>
                                Page <?= $page - 1 ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&categorie=<?= $categorie_id ?>&difficulte=<?= $difficulte_id ?>&recherche=<?= urlencode($recherche) ?>" class="btn btn-outline">
                                Page <?= $page + 1 ?>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="stats-summary">
                <p>Total : <?= $total_questions ?> question<?= $total_questions > 1 ? 's' : '' ?></p>
            </div>
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

.questions-page {
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

/* Filter Section */
.filter-section {
    background-color: var(--card-background);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.filter-form {
    margin-bottom: 0;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
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

.form-group.d-flex {
    display: flex;
    gap: 0.5rem;
}

.ml-2 {
    margin-left: 0.5rem;
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

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
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

/* Table Styles */
.table-responsive {
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--card-background);
    margin-bottom: 0;
}

.table thead {
    background-color: var(--primary-light);
}

.table th {
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    color: var(--text-color);
}

.table td {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.table tbody tr {
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: var(--animation-delay, 0s);
}

.table tbody tr:hover {
    background-color: var(--primary-light);
}

.table td:last-child {
    display: flex;
    gap: 0.5rem;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

/* Stats Summary */
.stats-summary {
    margin-top: 2rem;
    text-align: right;
    color: var(--text-muted);
    font-size: 0.875rem;
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

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
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

/* Responsive Design */
@media (max-width: 992px) {
    .section-title {
        font-size: 1.75rem;
    }

    .form-row {
        flex-direction: column;
    }

    .form-group {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .table td, .table th {
        padding: 0.75rem;
    }

    .table td:last-child {
        flex-wrap: wrap;
    }
}

@media (max-width: 576px) {
    .btn {
        width: 100%;
    }

    .header-actions .btn, .form-group.d-flex .btn {
        width: auto;
    }

    .pagination {
        flex-wrap: wrap;
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
});
</script>

<?php include '../includes/footer.php'; ?>