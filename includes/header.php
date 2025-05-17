<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
// Récupérer le nombre de notifications non lues pour les utilisateurs connectés
$unread_notifications_count = estConnecte() ? countUnreadNotifications($_SESSION['utilisateur_id']) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titre_page) ? $titre_page . ' - ' . APP_NAME : APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/quizmaster/assets/css/style.css">
    <?php if (isset($styles_supplementaires)): ?>
        <?php foreach ($styles_supplementaires as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="/quizmaster/index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h1><?= APP_NAME ?></h1>
                </a>
                
                <nav class="main-nav">
                    <button class="menu-toggle" aria-label="Menu">
                        <span class="menu-icon"></span>
                    </button>
                    
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/quizmaster/index.php" class="nav-link">
                                <i class="fas fa-home nav-icon"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/quizmaster/categorie.php" class="nav-link">
                                <i class="fas fa-th-large nav-icon"></i>
                                <span>Catégories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/quizmaster/top.php" class="nav-link">
                                <i class="fas fa-trophy nav-icon"></i>
                                <span>Classement</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/quizmaster/create_quiz.php" class="nav-link">
                                <i class="fas fa-plus-circle nav-icon"></i>
                                <span>Création</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/quizmaster/duels/index.php" class="nav-link">
                                <i class="fas fa-gamepad nav-icon"></i>
                                <span>Duels</span>
                            </a>
                        </li>
                        
                        <?php if (estConnecte()): ?>
                            <li class="nav-item">
                                <a href="/quizmaster/profil.php" class="nav-link">
                                    <i class="fas fa-user nav-icon"></i>
                                    <span>Profil</span>
                                </a>
                            </li>
                            <li class="nav-item notifications-item">
                                <button class="notifications-button" aria-label="Notifications">
                                    <i class="fas fa-bell nav-icon"></i>
                                    <?php if ($unread_notifications_count > 0): ?>
                                        <span class="notification-badge"><?= $unread_notifications_count ?></span>
                                    <?php endif; ?>
                                </button>
                                
                                <div class="notifications-dropdown" id="notifications-dropdown">
                                    <div class="dropdown-header">
                                        <h3>Notifications</h3>
                                        <button class="close-dropdown" aria-label="Fermer">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="notifications-list" id="notifications-list">
                                        <!-- Les notifications seront chargées via AJAX -->
                                        <div class="loading-spinner">
                                            <div class="spinner"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="dropdown-footer">
                                        <a href="/quizmaster/notifications.php" class="view-all-link">
                                            Voir toutes les notifications
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="/quizmaster/register.php" class="nav-link nav-link-highlight">
                                    <i class="fas fa-sign-in-alt nav-icon"></i>
                                    <span>Connexion</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <main>

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

/* Header Styles */
.site-header {
    background-color: var(--card-background);
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 1rem 0;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Logo Styles */
.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: var(--text-color);
    transition: var(--transition);
}

.logo:hover {
    transform: scale(1.05);
}

.logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-color), #818cf8);
    color: white;
    border-radius: 10px;
    margin-right: 0.75rem;
    font-size: 1.25rem;
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color); /* Fallback */
    background: linear-gradient(to right, var(--primary-color), #818cf8);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
}

/* Navigation Styles */
.main-nav {
    display: flex;
    align-items: center;
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

.menu-icon {
    display: block;
    width: 24px;
    height: 2px;
    background-color: var(--text-color);
    position: relative;
    transition: var(--transition);
}

.menu-icon::before,
.menu-icon::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 2px;
    background-color: var(--text-color);
    transition: var(--transition);
}

.menu-icon::before {
    top: -8px;
}

.menu-icon::after {
    bottom: -8px;
}

.menu-toggle.active .menu-icon {
    background-color: transparent;
}

.menu-toggle.active .menu-icon::before {
    transform: rotate(45deg);
    top: 0;
}

.menu-toggle.active .menu-icon::after {
    transform: rotate(-45deg);
    bottom: 0;
}

.nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
}

.nav-item {
    margin: 0 0.5rem;
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    border-radius: 8px;
    transition: var(--transition);
}

.nav-link:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.nav-icon {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.nav-link-highlight {
    background-color: var(--primary-color);
    color: white;
}

.nav-link-highlight:hover {
    background-color: var(--primary-hover);
    color: white;
}

/* Notifications Styles */
.notifications-item {
    position: relative;
}

.notifications-button {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: none;
    border: none;
    color: var(--text-color);
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
}

.notifications-button:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.notifications-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    margin-top: 0.5rem;
    overflow: hidden;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.dropdown-header h3 {
    margin: 0;
    font-size: 1rem;
    color: var(--text-color);
}

.close-dropdown {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 1rem;
    transition: var(--transition);
}

.close-dropdown:hover {
    color: var(--text-color);
}

.notifications-list {
    max-height: 350px;
    overflow-y: auto;
    padding: 0.5rem;
}

.notification-item {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background-color: var(--background-color);
    transition: var(--transition);
    position: relative;
}

.notification-item:hover {
    background-color: var(--primary-light);
}

.notification-item p {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
    color: var(--text-color);
}

.notification-item .time {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.notification-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

.mark-read,
.delete-notif {
    background: none;
    border: none;
    font-size: 0.75rem;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: var(--transition);
}

.mark-read {
    color: var(--primary-color);
}

.mark-read:hover {
    background-color: var(--primary-light);
}

.delete-notif {
    color: #ef4444;
}

.delete-notif:hover {
    background-color: rgba(239, 68, 68, 0.1);
}

.view-notif {
    background-color: var(--primary-color);
    color: white;
    border: none;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-decoration: none;
    transition: var(--transition);
}

.view-notif:hover {
    background-color: var(--primary-hover);
}

.no-notifications {
    padding: 2rem;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.dropdown-footer {
    padding: 0.75rem;
    text-align: center;
    border-top: 1px solid var(--border-color);
}

.view-all-link {
    display: inline-flex;
    align-items: center;
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: var(--transition);
}

.view-all-link i {
    margin-left: 0.25rem;
    font-size: 0.75rem;
}

.view-all-link:hover {
    color: var(--primary-hover);
}

/* Loading Spinner */
.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

.spinner {
    width: 30px;
    height: 30px;
    border: 3px solid rgba(79, 70, 229, 0.2);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 992px) {
    .nav-list {
        gap: 0.25rem;
    }
    
    .nav-link {
        padding: 0.5rem;
    }
    
    .nav-icon {
        margin-right: 0.25rem;
    }
}

@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }
    
    .nav-list {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background-color: var(--card-background);
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
        box-shadow: var(--shadow);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
    }
    
    .nav-list.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .nav-item {
        width: 100%;
        margin: 0.25rem 0;
    }
    
    .nav-link {
        width: 100%;
        padding: 0.75rem 1rem;
    }
    
    .notifications-dropdown {
        position: fixed;
        top: 70px;
        left: 1rem;
        right: 1rem;
        width: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menu toggle functionality
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('.nav-list');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navList.classList.toggle('active');
        });
    }
    
    // Notifications functionality
    const notificationsButton = document.querySelector('.notifications-button');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const closeDropdown = document.querySelector('.close-dropdown');
    const viewAllLink = document.querySelector('.view-all-link');
    let isOpen = false;
    
    function loadNotifications() {
        const notificationsList = document.getElementById('notifications-list');
        notificationsList.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        fetch('/quizmaster/get-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.notifications.length === 0) {
                    notificationsList.innerHTML = '<div class="no-notifications"><i class="fas fa-bell-slash"></i><p>Aucune nouvelle notification</p></div>';
                } else {
                    notificationsList.innerHTML = '';
                    data.notifications.forEach(notif => {
                        const timeAgo = new Date(notif.created_at).toLocaleString('fr-FR', {
                            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                        
                        let viewButton = '';
                        if (notif.type === 'new_quiz' && notif.related_id) {
                            viewButton = `<a href="/quizmaster/quiz.php?id=${notif.related_id}" class="view-notif" onclick="event.stopPropagation();">Voir</a>`;
                        } else if (notif.type === 'high_score' && notif.related_id) {
                            viewButton = `<a href="/quizmaster/profil.php#history" class="view-notif" onclick="event.stopPropagation();">Voir</a>`;
                        } else if (notif.type === 'duel_invitation' && notif.related_id) {
                            viewButton = `<a href="/quizmaster/duels/index.php" class="view-notif" onclick="event.stopPropagation();">Voir</a>`;
                        } else if (notif.type === 'duel_result' && notif.related_id) {
                            viewButton = `<a href="/quizmaster/profil.php#duels" class="view-notif" onclick="event.stopPropagation();">Voir</a>`;
                        }
                        
                        notificationsList.innerHTML += `
                            <div class="notification-item" data-id="${notif.id}">
                                <p>${notif.message}</p>
                                <span class="time">${timeAgo}</span>
                                <div class="notification-actions">
                                    ${viewButton}
                                    <button class="mark-read" data-id="${notif.id}">Marquer comme lu</button>
                                    <button class="delete-notif" data-id="${notif.id}"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                attachEventListeners();
            })
            .catch(error => {
                console.error('Erreur lors du chargement des notifications :', error);
                notificationsList.innerHTML = '<div class="no-notifications"><i class="fas fa-exclamation-circle"></i><p>Erreur lors du chargement des notifications</p></div>';
            });
    }
    
    function attachEventListeners() {
        document.querySelectorAll('.mark-read').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                
                fetch('/quizmaster/manage-notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=mark_read&id=${id}`
                })
                .then(() => {
                    loadNotifications();
                    updateNotificationCount();
                })
                .catch(error => {
                    console.error('Erreur lors du marquage de la notification :', error);
                });
            });
        });
        
        document.querySelectorAll('.delete-notif').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                
                fetch('/quizmaster/manage-notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&id=${id}`
                })
                .then(() => {
                    loadNotifications();
                    updateNotificationCount();
                })
                .catch(error => {
                    console.error('Erreur lors de la suppression de la notification :', error);
                });
            });
        });
        
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                fetch('/quizmaster/manage-notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=mark_read&id=${id}`
                })
                .then(() => {
                    updateNotificationCount();
                    this.style.backgroundColor = 'var(--background-color)';
                })
                .catch(error => {
                    console.error('Erreur lors du marquage de la notification :', error);
                });
            });
        });
    }
    
    function updateNotificationCount() {
        fetch('/quizmaster/get-notifications.php')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                const countElement = document.querySelector('.notification-badge');
                
                if (count > 0) {
                    if (!countElement) {
                        const badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.textContent = count;
                        notificationsButton.appendChild(badge);
                    } else {
                        countElement.textContent = count;
                    }
                } else if (countElement) {
                    countElement.remove();
                }
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour du compteur :', error);
            });
    }
    
    if (notificationsButton) {
        notificationsButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!isOpen) {
                loadNotifications();
                notificationsDropdown.style.display = 'block';
                isOpen = true;
            } else {
                notificationsDropdown.style.display = 'none';
                isOpen = false;
            }
        });
        
        if (closeDropdown) {
            closeDropdown.addEventListener('click', function() {
                notificationsDropdown.style.display = 'none';
                isOpen = false;
            });
        }
        
        if (viewAllLink) {
            viewAllLink.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        document.addEventListener('click', function(e) {
            if (isOpen && !notificationsButton.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.style.display = 'none';
                isOpen = false;
            }
        });
        
        // Initial notification count update
        updateNotificationCount();
    }
    
    // Fix for links opening in new tabs
    document.querySelectorAll('a').forEach(link => {
        // Remove any target attribute that might cause links to open in new tabs
        link.removeAttribute('target');
    });
});
</script>