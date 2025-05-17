<?php
require_once '../../includes/db.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est un admin
verifierAdmin();

// Récupérer toutes les catégories et difficultés
$categories = obtenirToutesLesCategories();
$difficultes = obtenirToutesLesDifficultes();

$erreur = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'] ?? '';
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $difficulte_id = (int)($_POST['difficulte_id'] ?? 0);
    
    $options = [];
    $texte_options = $_POST['option_texte'] ?? [];
    $est_correcte = $_POST['option_correcte'] ?? [];
    
    // Vérification des données
    if (empty($question)) {
        $erreur = 'Le texte de la question est requis';
    } elseif ($categorie_id <= 0) {
        $erreur = 'Veuillez sélectionner une catégorie';
    } elseif ($difficulte_id <= 0) {
        $erreur = 'Veuillez sélectionner une difficulté';
    } elseif (count($texte_options) < 2) {
        $erreur = 'Vous devez fournir au moins deux options';
    } elseif (!isset($est_correcte[0])) {
        $erreur = 'Vous devez indiquer quelle option est correcte';
    } else {
        // Préparer les options
        for ($i = 0; $i < count($texte_options); $i++) {
            if (!empty($texte_options[$i])) {
                $options[] = [
                    'texte' => $texte_options[$i],
                    'est_correcte' => ($est_correcte[0] == $i) ? 1 : 0
                ];
            }
        }
        
        if (count($options) < 2) {
            $erreur = 'Vous devez fournir au moins deux options non vides';
        } else {
            // Tout est bon, on peut ajouter la question
            $result = ajouterQuestion($question, $categorie_id, $difficulte_id, $options);
            
            if ($result) {
                $_SESSION['message'] = 'La question a été ajoutée avec succès';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $erreur = 'Une erreur est survenue lors de l\'ajout de la question';
            }
        }
    }
}

// Inclure l'en-tête
$titre_page = "Ajouter une question";
include '../includes/header.php';
?>

<div class="content-header">
    <h1>Ajouter une question</h1>
    <div class="actions">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= $erreur ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="content-body">
    <form method="post" class="form-large">
        <div class="form-group">
            <label for="question">Question</label>
            <textarea id="question" name="question" class="form-control" rows="3" required><?= $_POST['question'] ?? '' ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="categorie_id">Catégorie</label>
                <select id="categorie_id" name="categorie_id" class="form-control" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie['id'] ?>" <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $categorie['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group col-md-6">
                <label for="difficulte_id">Difficulté</label>
                <select id="difficulte_id" name="difficulte_id" class="form-control" required>
                    <option value="">Sélectionner une difficulté</option>
                    <?php foreach ($difficultes as $difficulte): ?>
                        <option value="<?= $difficulte['id'] ?>" <?= (isset($_POST['difficulte_id']) && $_POST['difficulte_id'] == $difficulte['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($difficulte['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <h3>Options de réponse</h3>
        <p class="text-muted">Ajoutez au moins 2 options et sélectionnez la réponse correcte.</p>
        
        <div id="options-container">
            <?php 
            $nb_options = max(4, count($_POST['option_texte'] ?? []));
            for ($i = 0; $i < $nb_options; $i++): 
            ?>
                <div class="form-row option-row">
                    <div class="form-group col-md-10">
                        <input type="text" name="option_texte[]" class="form-control" placeholder="Texte de l'option" 
                               value="<?= $_POST['option_texte'][$i] ?? '' ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="option_correcte[]" value="<?= $i ?>"
                                   <?= (isset($_POST['option_correcte'][0]) && $_POST['option_correcte'][0] == $i) ? 'checked' : ($i === 0 ? 'checked' : '') ?>>
                            <label class="form-check-label">Correcte</label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        
        <div class="form-group">
            <button type="button" id="add-option" class="btn btn-outline">
                <i class="fas fa-plus"></i> Ajouter une option
            </button>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Ajouter la question</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addOptionBtn = document.getElementById('add-option');
    const optionsContainer = document.getElementById('options-container');
    
    addOptionBtn.addEventListener('click', function() {
        const optionRows = document.querySelectorAll('.option-row');
        const newIndex = optionRows.length;
        
        const newRow = document.createElement('div');
        newRow.className = 'form-row option-row';
        newRow.innerHTML = `
            <div class="form-group col-md-10">
                <input type="text" name="option_texte[]" class="form-control" placeholder="Texte de l'option">
            </div>
            <div class="form-group col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="option_correcte[]" value="${newIndex}">
                    <label class="form-check-label">Correcte</label>
                </div>
            </div>
        `;
        
        optionsContainer.appendChild(newRow);
    });
});
</script>

<?php include '../includes/footer.php'; ?>