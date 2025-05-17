<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';
require_once '../../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Initialiser les variables pour la recherche et les filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$limit = 10; // Nombre de duels par page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Récupérer les catégories pour le filtre
$categories = obtenirToutesLesCategories();

// Construire la requête pour récupérer les duels
$database = new Database();
$db = $database->connect();
$query = "
    SELECT 
        d.id,
        d.type,
        d.status,
        d.question_count,
        d.time_limit,
        d.started_at,
        d.completed_at,
        c.nom AS categorie_nom,
        u1.nom AS challenger_nom,
        u2.nom AS opponent_nom,
        (SELECT COUNT(*) FROM duel_results dr WHERE dr.duel_id = d.id) AS result_count
    FROM duels d
    LEFT JOIN categories c ON d.categorie_id = c.id
    LEFT JOIN utilisateurs u1 ON d.challenger_id = u1.id
    LEFT JOIN utilisateurs u2 ON d.opponent_id = u2.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (d.id = :search_id OR u1.nom LIKE :search OR u2.nom LIKE :search)";
    $params[':search_id'] = $search;
    $params[':search'] = "%$search%";
}
if ($status) {
    $query .= " AND d.status = :status";
    $params[':status'] = $status;
}
if ($type) {
    $query .= " AND d.type = :type";
    $params[':type'] = $type;
}
if ($category) {
    $query .= " AND d.categorie_id = :category";
    $params[':category'] = $category;
}

// Compter le nombre total de duels pour la pagination
$count_query = "SELECT COUNT(*) FROM duels d WHERE 1=1";
if ($search) {
    $count_query .= " AND (d.id = :search_id OR EXISTS (
        SELECT 1 FROM utilisateurs u1 WHERE u1.id = d.challenger_id AND u1.nom LIKE :search
    ) OR EXISTS (
        SELECT 1 FROM utilisateurs u2 WHERE u2.id = d.opponent_id AND u2.nom LIKE :search
    ))";
}
if ($status) {
    $count_query .= " AND d.status = :status";
}
if ($type) {
    $count_query .= " AND d.type = :type";
}
if ($category) {
    $count_query .= " AND d.categorie_id = :category";
}

$stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_duels = $stmt->fetchColumn();
$total_pages = ceil($total_duels / $limit);

// Récupérer les duels
$query .= " ORDER BY d.started_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$duels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions (suppression, modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['duel_id'])) {
        $duel_id = (int)$_POST['duel_id'];
        $stmt = $db->prepare("DELETE FROM duel_results WHERE duel_id = :duel_id");
        $stmt->execute([':duel_id' => $duel_id]);
        $stmt = $db->prepare("DELETE FROM duel_questions WHERE duel_id = :duel_id");
        $stmt->execute([':duel_id' => $duel_id]);
        $stmt = $db->prepare("DELETE FROM duel_invitations WHERE duel_id = :duel_id");
        $stmt->execute([':duel_id' => $duel_id]);
        $stmt = $db->prepare("DELETE FROM duels WHERE id = :duel_id");
        $stmt->execute([':duel_id' => $duel_id]);
        $_SESSION['message'] = "Duel supprimé avec succès.";
        $_SESSION['message_type'] = "success";
        header('Location: index.php');
        exit;
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['duel_id'])) {
        $duel_id = (int)$_POST['duel_id'];
        $status = $_POST['status'] ?? '';
        $type = $_POST['type'] ?? '';
        $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
        $question_count = (int)$_POST['question_count'];
        $time_limit = $_POST['time_limit'] ? (int)$_POST['time_limit'] : null;

        $stmt = $db->prepare("
            UPDATE duels 
            SET 
                status = :status,
                type = :type,
                categorie_id = :category_id,
                question_count = :question_count,
                time_limit = :time_limit
            WHERE id = :duel_id
        ");
        $stmt->execute([
            ':status' => $status,
            ':type' => $type,
            ':category_id' => $category_id,
            ':question_count' => $question_count,
            ':time_limit' => $time_limit,
            ':duel_id' => $duel_id
        ]);
        $_SESSION['message'] = "Duel modifié avec succès.";
        $_SESSION['message_type'] = "success";
        header('Location: index.php');
        exit;
    }
}

// Inclure l'en-tête
$titre_page = "Gestion des Duels";
include '../includes/header.php';
?>

<main class="admin-duels-page">
    <div class="container">
        <section class="section-header">
            <h1 class="section-title">Gestion des Duels</h1>
            <p class="section-description">Visualisez, modifiez ou supprimez les duels existants.</p>
        </section>

        <!-- Message de succès ou d'erreur -->
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

        <!-- Filtres et Recherche -->
        <section class="admin-filters">
            <form method="get" class="filters-form">
                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ID du duel ou nom du joueur">
                </div>
                <div class="filter-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="">Tous</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Terminé</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">Tous</option>
                        <option value="timed" <?= $type === 'timed' ? 'selected' : '' ?>>Contre la montre</option>
                        <option value="accuracy" <?= $type === 'accuracy' ? 'selected' : '' ?>>Précision</option>
                        <option value="mixed" <?= $type === 'mixed' ? 'selected' : '' ?>>Mixte</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category">
                        <option value="">Toutes</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </section>

        <!-- Liste des Duels -->
        <section class="admin-duels-list">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="card-icon">
                            <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                        </svg>
                        Liste des Duels
                    </h2>
                </div>
                <div class="duels-table">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Joueurs</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Catégorie</th>
                                <th>Questions</th>
                                <th>Temps limite</th>
                                <th>Début</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($duels)): ?>
                                <tr>
                                    <td colspan="9" class="no-data">Aucun duel trouvé.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($duels as $duel): ?>
                                    <tr>
                                        <td><?= $duel['id'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($duel['challenger_nom']) ?> vs
                                            <?= htmlspecialchars($duel['opponent_nom'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $duel['type'] ?>">
                                                <?= getDuelTypeLabel($duel['type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $duel['status'] ?>">
                                                <?= ucfirst($duel['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($duel['categorie_nom'] ?? 'Toutes') ?></td>
                                        <td><?= $duel['question_count'] ?></td>
                                        <td><?= $duel['time_limit'] ? $duel['time_limit'] . ' sec' : 'Pas de limite' ?></td>
                                        <td>
                                            <?= $duel['started_at'] ? date('d/m/Y H:i', strtotime($duel['started_at'])) : 'N/A' ?>
                                        </td>
                                        <td class="actions">
                                            <button class="btn btn-outline btn-sm view-details" data-duel-id="<?= $duel['id'] ?>">Détails</button>
                                            <button class="btn btn-primary btn-sm edit-duel" data-duel-id="<?= $duel['id'] ?>">Modifier</button>
                                            <form method="post" class="delete-form" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="duel_id" value="<?= $duel['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce duel ?');">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&type=<?= $type ?>&category=<?= $category ?>" class="btn btn-outline btn-sm">Précédent</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> sur <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&type=<?= $type ?>&category=<?= $category ?>" class="btn btn-outline btn-sm">Suivant</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Modal pour les détails du duel -->
    <div id="duel-details-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails du Duel</h2>
                <button class="modal-close">×</button>
            </div>
            <div class="modal-body" id="duel-details-content">
                <!-- Contenu chargé via AJAX -->
            </div>
        </div>
    </div>

    <!-- Modal pour modifier le duel -->
    <div id="edit-duel-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier le Duel</h2>
                <button class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <form id="edit-duel-form" method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="duel_id" id="edit-duel-id">
                    <div class="form-group">
                        <label for="edit-status">Statut</label>
                        <select id="edit-status" name="status" required>
                            <option value="pending">En attente</option>
                            <option value="active">Actif</option>
                            <option value="completed">Terminé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-type">Type</label>
                        <select id="edit-type" name="type" required>
                            <option value="timed">Contre la montre</option>
                            <option value="accuracy">Précision</option>
                            <option value="mixed">Mixte</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-category">Catégorie</label>
                        <select id="edit-category" name="category_id">
                            <option value="">Aucune</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-question-count">Nombre de questions</label>
                        <input type="number" id="edit-question-count" name="question_count" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-time-limit">Temps limite (secondes, facultatif)</label>
                        <input type="number" id="edit-time-limit" name="time_limit" min="0">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-outline modal-close">Annuler</button>
                    </div>
                </form>
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
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --success-color: #10b981;
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

.admin-duels-page {
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
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

.section-description {
    font-size: 1.125rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
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

/* Filtres */
.admin-filters {
    margin-bottom: 2rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    background-color: var(--card-background);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.875rem;
    background-color: var(--background-color);
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

/* Dashboard Card */
.dashboard-card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.card-title {
    display: flex;
    align-items: center;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.card-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    color: var(--primary-color);
}

/* Table */
.duels-table {
    padding: 1.5rem;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
}

.leaderboard-table th,
.leaderboard-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.leaderboard-table th {
    font-weight: 600;
    color: var(--text-color);
    background-color: var(--primary-light);
}

.leaderboard-table tbody tr:hover {
    background-color: var(--primary-light);
}

.leaderboard-table .no-data {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

.actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Badge */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-timed {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-accuracy {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-mixed {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-active {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-completed {
    background-color: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
}

.pagination span {
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    max-width: 700px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-close:hover {
    color: var(--text-color);
}

.modal-body {
    padding: 1.5rem;
}

/* Styles spécifiques pour la modale des détails du duel */
.duel-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.duel-details-section {
    background-color: var(--primary-light);
    padding: 1rem;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
}

.duel-details-section h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--primary-color);
}

.duel-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.duel-details-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.duel-details-item label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted);
}

.duel-details-item span {
    font-size: 0.875rem;
    color: var(--text-color);
}

.duel-details-results table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.5rem;
}

.duel-details-results th,
.duel-details-results td {
    padding: 0.5rem;
    border-bottom: 1px solid var(--border-color);
    text-align: left;
    font-size: 0.875rem;
}

.duel-details-results th {
    font-weight: 600;
    color: var(--text-color);
    background-color: rgba(79, 70, 229, 0.05);
}

.duel-details-questions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.duel-details-question {
    background-color: var(--card-background);
    padding: 1rem;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
}

.duel-details-question p {
    margin: 0;
    font-size: 0.875rem;
}

.duel-details-question .question-text {
    font-weight: 500;
    color: var(--text-color);
}

.duel-details-question .correct-answer {
    color: var(--success-color);
    font-style: italic;
}

.no-data-message {
    font-size: 0.875rem;
    color: var(--text-muted);
    text-align: center;
    padding: 1rem;
}

/* Form Styles */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: var(--transition);
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
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

.btn-danger {
    background-color: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
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

/* Responsive */
@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
    }

    .leaderboard-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .modal-content {
        width: 95%;
    }

    .duel-details-grid {
        grid-template-columns: 1fr;
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

    // Modal handling
    const detailsModal = document.getElementById('duel-details-modal');
    const editModal = document.getElementById('edit-duel-modal');
    const detailsContent = document.getElementById('duel-details-content');
    const closeButtons = document.querySelectorAll('.modal-close');

    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            detailsModal.style.display = 'none';
            editModal.style.display = 'none';
        });
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === detailsModal) {
            detailsModal.style.display = 'none';
        }
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // View details
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', () => {
            const duelId = button.dataset.duelId;
            fetchDuelDetails(duelId);
            detailsModal.style.display = 'flex';
        });
    });

    // Edit duel
    document.querySelectorAll('.edit-duel').forEach(button => {
        button.addEventListener('click', () => {
            const duelId = button.dataset.duelId;
            fetchDuelDetailsForEdit(duelId);
            editModal.style.display = 'flex';
        });
    });

    // Fetch duel details
    function fetchDuelDetails(duelId) {
        fetch('get_duel_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'duel_id=' + encodeURIComponent(duelId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                detailsContent.innerHTML = `<p class="no-data-message">${data.error}</p>`;
            } else {
                detailsContent.innerHTML = `
                    <div class="duel-details">
                        <!-- Détails du duel -->
                        <div class="duel-details-section">
                            <h3>Informations du Duel</h3>
                            <div class="duel-details-grid">
                                <div class="duel-details-item">
                                    <label>ID</label>
                                    <span>${data.id}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Joueurs</label>
                                    <span>${data.challenger_nom} vs ${data.opponent_nom || 'N/A'}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Type</label>
                                    <span class="badge badge-${data.type}">${getDuelTypeLabel(data.type)}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Statut</label>
                                    <span class="badge badge-${data.status}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Catégorie</label>
                                    <span>${data.categorie_nom || 'Toutes'}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Nombre de questions</label>
                                    <span>${data.question_count}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Temps limite</label>
                                    <span>${data.time_limit ? data.time_limit + ' sec' : 'Pas de limite'}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Début</label>
                                    <span>${data.started_at ? new Date(data.started_at).toLocaleString() : 'N/A'}</span>
                                </div>
                                <div class="duel-details-item">
                                    <label>Fin</label>
                                    <span>${data.completed_at ? new Date(data.completed_at).toLocaleString() : 'N/A'}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Résultats -->
                        <div class="duel-details-section duel-details-results">
                            <h3>Résultats</h3>
                            ${data.results && data.results.length > 0 ? `
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Joueur</th>
                                            <th>Score</th>
                                            <th>Temps</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.results.map(result => `
                                            <tr>
                                                <td>${result.user_nom}</td>
                                                <td>${result.score}</td>
                                                <td>${result.completion_time} sec</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            ` : '<p class="no-data-message">Aucun résultat disponible.</p>'}
                        </div>

                        <!-- Questions -->
                        <div class="duel-details-section duel-details-questions">
                            <h3>Questions</h3>
                            ${data.questions && data.questions.length > 0 ? `
                                ${data.questions.map(q => `
                                    <div class="duel-details-question">
                                        <p class="question-text">${q.texte}</p>
                                        <p class="correct-answer">Réponse correcte : ${q.correct_option}</p>
                                    </div>
                                `).join('')}
                            ` : '<p class="no-data-message">Aucune question disponible.</p>'}
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            detailsContent.innerHTML = `<p class="no-data-message">Erreur lors du chargement des détails.</p>`;
            console.error('Error:', error);
        });
    }

    // Fetch duel details for editing
    function fetchDuelDetailsForEdit(duelId) {
        fetch('get_duel_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'duel_id=' + encodeURIComponent(duelId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                document.getElementById('edit-duel-id').value = data.id;
                document.getElementById('edit-status').value = data.status;
                document.getElementById('edit-type').value = data.type;
                document.getElementById('edit-category').value = data.categorie_id || '';
                document.getElementById('edit-question-count').value = data.question_count;
                document.getElementById('edit-time-limit').value = data.time_limit || '';
            }
        })
        .catch(error => {
            alert('Erreur lors du chargement des données du duel.');
            console.error('Error:', error);
        });
    }

    // Helper function for duel types
    function getDuelTypeLabel(type) {
        switch(type) {
            case 'timed': return 'Contre la montre';
            case 'accuracy': return 'Précision';
            case 'mixed': return 'Mixte';
            default: return type;
        }
    }
});
</script>

<?php
// Helper function for duel types
function getDuelTypeLabel($type) {
    switch($type) {
        case 'timed': return 'Contre la montre';
        case 'accuracy': return 'Précision';
        case 'mixed': return 'Mixte';
        default: return $type;
    }
}

include '../includes/footer.php';
?>