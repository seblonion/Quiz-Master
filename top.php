<?php
$titre_page = "Classement";
require_once 'includes/header.php';

// Connexion à la base de données
$database = new Database();
$db = $database->connect();

// Récupérer le classement général (top 10 utilisateurs par score moyen)
$query = "SELECT u.id, u.nom, u.est_contributeur, AVG(qc.score) as score_moyen
          FROM utilisateurs u
          JOIN quiz_completes qc ON u.id = qc.utilisateur_id
          GROUP BY u.id, u.nom, u.est_contributeur
          ORDER BY score_moyen DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$classement_general = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les catégories
$query = "SELECT id, nom, couleur, icone FROM categories ORDER BY nom";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les classements par catégorie
$classements_categories = [];
foreach ($categories as $categorie) {
    $query = "SELECT u.id, u.nom, u.est_contributeur, AVG(qc.score) as score_moyen
              FROM utilisateurs u
              JOIN quiz_completes qc ON u.id = qc.utilisateur_id
              WHERE qc.categorie_id = :categorie_id
              GROUP BY u.id, u.nom, u.est_contributeur
              ORDER BY score_moyen DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':categorie_id', $categorie['id'], PDO::PARAM_INT);
    $stmt->execute();
    $classements_categories[$categorie['id']] = [
        'nom' => $categorie['nom'],
        'couleur' => $categorie['couleur'],
        'icone' => $categorie['icone'],
        'classement' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];
}
?>

<main class="leaderboard-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Classement</h1>
            <p class="section-description">Découvrez les meilleurs joueurs de QuizMaster, globalement et par catégorie</p>
        </div>

        <!-- Classement général -->
        <div class="card">
            <div class="leaderboard-header">
                <h2 class="section-title">Classement Général</h2>
                <p class="section-description">Les 10 meilleurs joueurs en fonction de leur score moyen sur tous les quiz</p>
            </div>
            <?php if (!empty($classement_general)): ?>
                <div class="leaderboard-table-container">
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th class="rank-col">Rang</th>
                                <th class="player-col">Joueur</th>
                                <th class="score-col">Score Moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classement_general as $index => $joueur): ?>
                                <tr class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                    <td class="rank-col">
                                        <?php if ($index == 0): ?>
                                            <i class="fas fa-trophy" style="color: #FFD700;"></i>
                                        <?php elseif ($index == 1): ?>
                                            <i class="fas fa-trophy" style="color: #C0C0C0;"></i>
                                        <?php elseif ($index == 2): ?>
                                            <i class="fas fa-trophy" style="color: #CD7F32;"></i>
                                        <?php else: ?>
                                            #<?= $index + 1 ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="player-col">
                                        <a href="profil.php?id=<?= htmlspecialchars($joueur['id']) ?>" class="player-link">
                                            <?= htmlspecialchars($joueur['nom']) ?>
                                            <?php if ($joueur['est_contributeur']): ?>
                                                <i class="certified-icon fas fa-check-circle" title="Contributeur certifié"></i>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="score-col"><?= round($joueur['score_moyen']) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">Aucun classement disponible pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Classements par catégorie -->
        <div class="category-rankings">
            <div class="section-header">
                <h2 class="section-title">Classements par Catégorie</h2>
                <p class="section-description">Les 5 meilleurs joueurs dans chaque catégorie</p>
            </div>
            <div class="categories-grid">
                <?php foreach ($classements_categories as $categorie): ?>
                    <div class="card" style="--category-color: <?= $categorie['couleur'] ?>">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas <?= $categorie['icone'] ?>"></i>
                            </div>
                            <h3 class="category-title"><?= htmlspecialchars($categorie['nom']) ?></h3>
                        </div>
                        <?php if (!empty($categorie['classement'])): ?>
                            <div class="leaderboard-table-container">
                                <table class="leaderboard-table">
                                    <thead>
                                        <tr>
                                            <th class="rank-col">Rang</th>
                                            <th class="player-col">Joueur</th>
                                            <th class="score-col">Score Moyen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categorie['classement'] as $index => $joueur): ?>
                                            <tr class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                                <td class="rank-col">
                                                    <?php if ($index == 0): ?>
                                                        <i class="fas fa-trophy" style="color: #FFD700;"></i>
                                                    <?php elseif ($index == 1): ?>
                                                        <i class="fas fa-trophy" style="color: #C0C0C0;"></i>
                                                    <?php elseif ($index == 2): ?>
                                                        <i class="fas fa-trophy" style="color: #CD7F32;"></i>
                                                    <?php else: ?>
                                                        #<?= $index + 1 ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="player-col">
                                                    <a href="profil.php?id=<?= htmlspecialchars($joueur['id']) ?>" class="player-link">
                                                        <?= htmlspecialchars($joueur['nom']) ?>
                                                        <?php if ($joueur['est_contributeur']): ?>
                                                            <i class="certified-icon fas fa-check-circle" title="Contributeur certifié"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </td>
                                                <td class="score-col"><?= round($joueur['score_moyen']) ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Aucun classement disponible pour cette catégorie.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
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
.leaderboard-page {
    font-family: var(--font-sans);
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
    padding: 2rem 0 4rem;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 2rem;
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

/* Card Styles */
.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-top: 4px solid var(--category-color, var(--primary-color));
}

/* Leaderboard Header */
.leaderboard-header {
    margin-bottom: 1.5rem;
}

/* Category Header */
.category-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.category-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--category-color, var(--primary-color));
    color: white;
    border-radius: 50%;
    margin-right: 1rem;
    font-size: 1.5rem;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-color);
}

/* Leaderboard Table */
.leaderboard-table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.leaderboard-table th {
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-weight: 600;
    text-align: left;
    padding: 1rem;
    border-bottom: 2px solid var(--primary-color);
}

.leaderboard-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.leaderboard-table tbody tr {
    transition: var(--transition);
}

.leaderboard-table tbody tr:hover {
    background-color: var(--primary-light);
}

.leaderboard-table tbody tr.top-1 {
    background-color: rgba(255, 215, 0, 0.1);
    border-left: 4px solid #FFD700;
}

.leaderboard-table tbody tr.top-2 {
    background-color: rgba(192, 192, 192, 0.1);
    border-left: 4px solid #C0C0C0;
}

.leaderboard-table tbody tr.top-3 {
    background-color: rgba(205, 127, 50, 0.1);
    border-left: 4px solid #CD7F32;
}

.rank-col {
    width: 60px;
    text-align: center;
}

.player-col {
    min-width: 200px;
}

.score-col {
    width: 100px;
    text-align: center;
}

.player-link {
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.player-link:hover {
    color: var(--primary-color);
}

.certified-icon {
    color: #4f46e5;
    font-size: 0.85em;
}

.no-data {
    text-align: center;
    color: var(--text-muted);
    padding: 2rem !important;
}

/* Category Rankings */
.category-rankings {
    margin-top: 2rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }

    .leaderboard-table th,
    .leaderboard-table td {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 1.75rem;
    }

    .leaderboard-table th,
    .leaderboard-table td {
        font-size: 0.75rem;
    }

    .rank-col {
        width: 40px;
    }

    .player-col {
        min-width: 120px;
    }

    .score-col {
        width: 80px;
    }
}

@media (max-width: 576px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }

    .leaderboard-table th,
    .leaderboard-table td {
        padding: 0.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>