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
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
    $difficulte_id = (int)($_POST['difficulte_id'] ?? 0);
    $format = $_POST['format'] ?? '';
    
    // Vérification des données de base
    if ($categorie_id <= 0) {
        $erreur = 'Veuillez sélectionner une catégorie';
    } elseif ($difficulte_id <= 0) {
        $erreur = 'Veuillez sélectionner une difficulté';
    } elseif (empty($format)) {
        $erreur = 'Veuillez sélectionner un format d\'importation';
    } else {
        // Vérifier si un fichier a été téléchargé
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $erreur = 'Erreur lors du téléchargement du fichier';
        } else {
            $file_tmp = $_FILES['import_file']['tmp_name'];
            $file_name = $_FILES['import_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Vérifier l'extension du fichier en fonction du format sélectionné
            if ($format === 'csv' && $file_ext !== 'csv') {
                $erreur = 'Le fichier doit être au format CSV';
            } elseif ($format === 'json' && $file_ext !== 'json') {
                $erreur = 'Le fichier doit être au format JSON';
            } else {
                // Traitement en fonction du format
                $questions_importees = [];
                $questions_erronees = [];
                
                try {
                    if ($format === 'csv') {
                        // Traitement CSV
                        $handle = fopen($file_tmp, 'r');
                        $header = fgetcsv($handle); // Ignorer l'en-tête
                        
                        while (($data = fgetcsv($handle)) !== false) {
                            if (count($data) < 3) continue; // Ignorer les lignes incomplètes
                            
                            $question = $data[0];
                            $options = [];
                            
                            // Les options commencent à partir de l'index 2
                            // L'index 1 contient l'indice de la bonne réponse (0-based)
                            $correct_index = (int)$data[1];
                            
                            for ($i = 2; $i < count($data); $i++) {
                                if (!empty($data[$i])) {
                                    $options[] = [
                                        'texte' => $data[$i],
                                        'est_correcte' => ($i - 2 === $correct_index) ? 1 : 0
                                    ];
                                }
                            }
                            
                            if (!empty($question) && count($options) >= 2) {
                                $question_id = ajouterQuestion($question, $categorie_id, $difficulte_id, $options);
                                if ($question_id) {
                                    $questions_importees[] = $question;
                                } else {
                                    $questions_erronees[] = $question;
                                }
                            } else {
                                $questions_erronees[] = $question;
                            }
                        }
                        
                        fclose($handle);
                    } elseif ($format === 'json') {
                        // Traitement JSON
                        $json_data = file_get_contents($file_tmp);
                        $data = json_decode($json_data, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('JSON invalide: ' . json_last_error_msg());
                        }
                        
                        foreach ($data as $item) {
                            $question = $item['question'] ?? '';
                            $options = [];
                            
                            if (isset($item['options']) && is_array($item['options'])) {
                                $correct_index = $item['correct_index'] ?? 0;
                                
                                foreach ($item['options'] as $i => $option_text) {
                                    $options[] = [
                                        'texte' => $option_text,
                                        'est_correcte' => ($i === $correct_index) ? 1 : 0
                                    ];
                                }
                            }
                            
                            if (!empty($question) && count($options) >= 2) {
                                $question_id = ajouterQuestion($question, $categorie_id, $difficulte_id, $options);
                                if ($question_id) {
                                    $questions_importees[] = $question;
                                } else {
                                    $questions_erronees[] = $question;
                                }
                            } else {
                                $questions_erronees[] = $question;
                            }
                        }
                    }
                    
                    $nb_success = count($questions_importees);
                    $nb_errors = count($questions_erronees);
                    
                    if ($nb_success > 0) {
                        $success = "Importation réussie! $nb_success question(s) importée(s).";
                        if ($nb_errors > 0) {
                            $success .= " $nb_errors question(s) n'ont pas pu être importées.";
                        }
                    } else {
                        $erreur = "Aucune question n'a pu être importée.";
                    }
                } catch (Exception $e) {
                    $erreur = 'Erreur lors de l\'importation: ' . $e->getMessage();
                }
            }
        }
    }
}

// Inclure l'en-tête
$titre_page = "Importer des questions";
include '../includes/header.php';
?>

<div class="content-header">
    <h1>Importer des questions</h1>
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
    
    <?php if (!empty($questions_importees)): ?>
        <div class="imported-questions">
            <h3>Questions importées</h3>
            <ul>
                <?php foreach ($questions_importees as $q): ?>
                    <li><?= htmlspecialchars($q) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($questions_erronees)): ?>
        <div class="error-questions">
            <h3>Questions non importées</h3>
            <ul>
                <?php foreach ($questions_erronees as $q): ?>
                    <li><?= htmlspecialchars($q) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="content-body">
    <div class="import-templates mb-3">
        <h3>Formats d'importation disponibles</h3>
        <p>Téléchargez un modèle et remplissez-le avec vos questions :</p>
        <div class="template-links">
            <a href="templates/template.csv" download class="btn btn-outline">
                <i class="fas fa-file-csv"></i> Modèle CSV
            </a>
            <a href="templates/template.json" download class="btn btn-outline">
                <i class="fas fa-file-code"></i> Modèle JSON
            </a>
        </div>
        
        <div class="template-help mt-3">
            <h4>Format CSV</h4>
            <p>Le fichier CSV doit avoir le format suivant :</p>
            <pre>question,correct_index,option1,option2,option3,option4
"Quelle est la capitale de la France?",0,"Paris","Lyon","Marseille","Toulouse"</pre>
            <p><strong>correct_index</strong> indique l'index (commençant à 0) de la bonne réponse.</p>
            
            <h4>Format JSON</h4>
            <p>Le fichier JSON doit avoir le format suivant :</p>
            <pre>[
  {
    "question": "Quelle est la capitale de la France?",
    "options": ["Paris", "Lyon", "Marseille", "Toulouse"],
    "correct_index": 0
  },
  {
    "question": "Qui a peint la Joconde?",
    "options": ["Léonard de Vinci", "Pablo Picasso", "Vincent van Gogh", "Claude Monet"],
    "correct_index": 0
  }
]</pre>
        </div>
    </div>
    
    <form method="post" enctype="multipart/form-data" class="form-large">
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
        
        <div class="form-group">
            <label for="format">Format du fichier</label>
            <select id="format" name="format" class="form-control" required>
                <option value="">Sélectionner un format</option>
                <option value="csv" <?= (isset($_POST['format']) && $_POST['format'] == 'csv') ? 'selected' : '' ?>>CSV</option>
                <option value="json" <?= (isset($_POST['format']) && $_POST['format'] == 'json') ? 'selected' : '' ?>>JSON</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="import_file">Fichier à importer</label>
            <input type="file" id="import_file" name="import_file" class="form-control-file" required>
            <small class="text-muted">Formats acceptés : CSV, JSON</small>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Importer les questions</button>
        </div>
    </form>
</div>

<style>
.template-links {
    display: flex;
    gap: 10px;
}

.template-help pre {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
}

.imported-questions, .error-questions {
    margin-top: 20px;
}

.imported-questions h3 {
    color: var(--success-color);
}

.error-questions h3 {
    color: var(--danger-color);
}

.form-control-file {
    padding: 10px 0;
}

.mt-3 {
    margin-top: 15px;
}

.mb-3 {
    margin-bottom: 15px;
}
</style>

<?php include '../includes/footer.php'; ?>