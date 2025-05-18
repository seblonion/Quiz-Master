<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/functions/duel_functions.php';

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    header('Location: ../connexion.php');
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Vérifier si l'ID du duel est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID de duel manquant';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$duel_id = (int)$_GET['id'];

// Récupérer les informations du duel
$duel = getDuelById($duel_id);

if (!$duel) {
    $_SESSION['message'] = 'Duel non trouvé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier que l'utilisateur est un participant
if ($duel['challenger_id'] != $user_id && $duel['opponent_id'] != $user_id) {
    $_SESSION['message'] = 'Vous n\'êtes pas autorisé à signaler ce duel';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier que le duel est terminé
if ($duel['status'] != 'completed') {
    $_SESSION['message'] = 'Ce duel n\'est pas encore terminé';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Vérifier si un rapport existe déjà
$database = new Database();
$db = $database->connect();

$query = "SELECT * FROM duel_reports WHERE duel_id = :duel_id AND reporter_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':duel_id', $duel_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$existing_report = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $details = isset($_POST['details']) ? trim($_POST['details']) : '';
    
    if (empty($reason)) {
        $errors[] = 'Veuillez sélectionner une raison';
    }
    
    if (empty($details)) {
        $errors[] = 'Veuillez fournir des détails sur le problème';
    } elseif (strlen($details) < 20) {
        $errors[] = 'Les détails doivent contenir au moins 20 caractères';
    }
    
    // Si pas d'erreurs, enregistrer le rapport
    if (empty($errors)) {
        if ($existing_report) {
            // Mettre à jour le rapport existant
            $query = "UPDATE duel_reports 
                    SET reason = :reason, details = :details, date_updated = NOW(), status = 'pending' 
                    WHERE id = :report_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':details', $details);
            $stmt->bindParam(':report_id', $existing_report['id']);
        } else {
            // Créer un nouveau rapport
            $query = "INSERT INTO duel_reports (duel_id, reporter_id, reported_id, reason, details, date_created, status) 
                    VALUES (:duel_id, :reporter_id, :reported_id, :reason, :details, NOW(), 'pending')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':duel_id', $duel_id);
            $stmt->bindParam(':reporter_id', $user_id);
            $reported_id = ($duel['challenger_id'] == $user_id) ? $duel['opponent_id'] : $duel['challenger_id'];
            $stmt->bindParam(':reported_id', $reported_id);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':details', $details);
        }
        
        if ($stmt->execute()) {
            $success = true;
            
            // Rediriger après 3 secondes
            header("refresh:3;url=index.php");
        } else {
            $errors[] = 'Une erreur est survenue lors de l\'enregistrement du rapport';
        }
    }
}

// Récupérer les détails des joueurs
$challenger = obtenirUtilisateur($duel['challenger_id']);
$opponent = obtenirUtilisateur($duel['opponent_id']);

// Inclure l'en-tête
$titre_page = "Signaler un problème";
include '../includes/header.php';
?>

<main class="report-page">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Signaler un problème</h1>
            <p class="section-description">
                Duel entre <?= htmlspecialchars($challenger['nom']) ?> et <?= htmlspecialchars($opponent['nom']) ?>
            </p>
            <div class="header-actions">
                <a href="results.php?id=<?= $duel_id ?>" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10 12.77 13.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Retour aux résultats
                </a>
            </div>
        </div>

        <div class="report-container">
            <div class="card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="alert-content">
                            <h4 class="alert-title">Rapport envoyé avec succès</h4>
                            <p class="alert-message">Votre signalement a été enregistré et sera examiné par notre équipe. Vous serez redirigé dans quelques secondes.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="alert-content">
                                <h4 class="alert-title">Erreur</h4>
                                <ul class="alert-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($existing_report): ?>
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="alert-icon">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="alert-content">
                                <h4 class="alert-title">Rapport existant</h4>
                                <p class="alert-message">Vous avez déjà signalé ce duel. Votre rapport précédent sera mis à jour.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="report-info">
                        <h3 class="report-info-title">Informations sur le duel</h3>
                        <div class="report-info-grid">
                            <div class="info-item">
                                <span class="info-label">Date du duel:</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($duel['date_completed'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Type de duel:</span>
                                <span class="info-value">
                                    <?php if ($duel['type'] == 'timed'): ?>
                                        <span class="badge badge-timed">Contre la montre</span>
                                    <?php elseif ($duel['type'] == 'accuracy'): ?>
                                        <span class="badge badge-accuracy">Précision</span>
                                    <?php else: ?>
                                        <span class="badge badge-mixed">Mixte</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Challenger:</span>
                                <span class="info-value"><?= htmlspecialchars($challenger['nom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Adversaire:</span>
                                <span class="info-value"><?= htmlspecialchars($opponent['nom']) ?></span>
                            </div>
                        </div>
                    </div>

                    <form class="report-form" method="post" action="">
                        <div class="form-group">
                            <label for="reason" class="form-label">Raison du signalement <span class="required">*</span></label>
                            <select id="reason" name="reason" class="form-control" required>
                                <option value="" disabled <?= !isset($_POST['reason']) ? 'selected' : '' ?>>Sélectionnez une raison</option>
                                <option value="cheating" <?= isset($_POST['reason']) && $_POST['reason'] == 'cheating' ? 'selected' : '' ?>>Triche</option>
                                <option value="inappropriate_behavior" <?= isset($_POST['reason']) && $_POST['reason'] == 'inappropriate_behavior' ? 'selected' : '' ?>>Comportement inapproprié</option>
                                <option value="technical_issue" <?= isset($_POST['reason']) && $_POST['reason'] == 'technical_issue' ? 'selected' : '' ?>>Problème technique</option>
                                <option value="unfair_advantage" <?= isset($_POST['reason']) && $_POST['reason'] == 'unfair_advantage' ? 'selected' : '' ?>>Avantage injuste</option>
                                <option value="other" <?= isset($_POST['reason']) && $_POST['reason'] == 'other' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="details" class="form-label">Détails du problème <span class="required">*</span></label>
                            <textarea id="details" name="details" class="form-control" rows="6" placeholder="Décrivez le problème en détail..." required><?= isset($_POST['details']) ? htmlspecialchars($_POST['details']) : (isset($existing_report['details']) ? htmlspecialchars($existing_report['details']) : '') ?></textarea>
                            <div class="form-help">Veuillez fournir autant de détails que possible pour nous aider à comprendre le problème.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Preuves (facultatif)</label>
                            <div class="evidence-info">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="evidence-icon">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p>Si vous avez des captures d'écran ou d'autres preuves, veuillez les envoyer à <a href="mailto:support@quizduels.com">support@quizduels.com</a> en mentionnant l'ID du duel: <strong><?= $duel_id ?></strong></p>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="btn-icon-left">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Envoyer le signalement
                            </button>
                            <a href="results.php?id=<?= $duel_id ?>" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card guidelines-card">
                <h3 class="guidelines-title">Directives de signalement</h3>
                <div class="guidelines-content">
                    <p>Nous prenons les signalements très au sérieux. Veuillez suivre ces directives:</p>
                    <ul class="guidelines-list">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="guidelines-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Soyez précis et factuel dans votre description</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="guidelines-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Incluez des preuves si possible (captures d'écran, etc.)</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="guidelines-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Évitez les accusations sans fondement</span>
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="guidelines-icon">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Restez respectueux et professionnel</span>
                        </li>
                    </ul>
                    <div class="guidelines-note">
                        <p>Les faux signalements peuvent entraîner des sanctions. Nous examinons chaque cas individuellement et prendrons les mesures appropriées si nécessaire.</p>
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
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --success-color: #10b981;
    --info-color: #3b82f6;
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

.report-page {
    padding: 2rem 0 4rem;
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

.header-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Card Styles */
.card {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

/* Report Container */
.report-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

/* Report Info */
.report-info {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.report-info-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.report-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.info-value {
    font-weight: 500;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-timed {
    background-color: rgba(79, 70, 229, 0.1);
    color: var(--primary-color);
}

.badge-accuracy {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--secondary-color);
}

.badge-mixed {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
}

/* Form Styles */
.report-form {
    margin-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.required {
    color: var(--danger-color);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background-color: var(--card-background);
    color: var(--text-color);
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

.form-help {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Evidence Info */
.evidence-info {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background-color: var(--primary-light);
    border-radius: 0.5rem;
}

.evidence-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--primary-color);
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.evidence-info p {
    margin: 0;
    font-size: 0.875rem;
}

.evidence-info a {
    color: var(--primary-color);
    font-weight: 500;
    text-decoration: none;
}

.evidence-info a:hover {
    text-decoration: underline;
}

/* Guidelines Card */
.guidelines-card {
    background-color: var(--primary-light);
    border: 1px solid rgba(79, 70, 229, 0.2);
}

.guidelines-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.guidelines-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
}

.guidelines-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.guidelines-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--success-color);
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.guidelines-note {
    padding-top: 1rem;
    border-top: 1px solid rgba(79, 70, 229, 0.2);
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Alert Styles */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.alert-icon {
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}

.alert-message {
    margin: 0;
}

.alert-list {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: var(--success-color);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--danger-color);
}

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    color: var(--info-color);
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

.btn-icon-left {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .report-container {
        grid-template-columns: 1fr;
    }
    
    .section-title {
        font-size: 1.75rem;
    }
}

@media (max-width: 768px) {
    .report-info-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .evidence-info {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.querySelector('.report-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const reason = document.getElementById('reason');
            const details = document.getElementById('details');
            
            // Vérifier la raison
            if (reason.value === '') {
                isValid = false;
                reason.classList.add('is-invalid');
            } else {
                reason.classList.remove('is-invalid');
            }
            
            // Vérifier les détails
            if (details.value.trim() === '') {
                isValid = false;
                details.classList.add('is-invalid');
            } else if (details.value.trim().length < 20) {
                isValid = false;
                details.classList.add('is-invalid');
            } else {
                details.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
