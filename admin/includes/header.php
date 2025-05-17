<?php
// Vérifier si l'utilisateur est un admin, sinon rediriger
verifierAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titre_page) ? $titre_page . ' - ' : '' ?>QuizMaster Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="breadcrumb">
                    <h1><?= $titre_page ?? 'Administration' ?></h1>
                </div>
                <div class="user-info">
                    <span><?= $_SESSION['utilisateur_nom'] ?></span>
                    <div class="user-actions">
                        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/logout.php" title="Se déconnecter">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Content goes here -->