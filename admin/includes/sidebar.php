<!-- Inclure Font Awesome pour l'icône fas fa-brain -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/index.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h1>QuizMaster</h1>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <button class="menu-toggle" aria-label="Menu">
            <span class="menu-icon"></span>
        </button>
        
        <ul class="nav-list">
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/index.php') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/users/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/users/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/quiz/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/quiz/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2z" clip-rule="evenodd" />
                    </svg>
                    <span>Questions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/categories/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/categories/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M2 4a2 2 0 012-2h6a2 2 0 012 2v2h2a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V4z" />
                    </svg>
                    <span>Catégories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/duels/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/duels/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                    </svg>
                    <span>Duels</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/creation/validate_create.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/creation/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    <span>Créations</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>admin/stats/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/stats/') !== false ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 001 1h2a1 1 0 001-1v-5a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 001 1h2a1 1 0 001-1v-5a3 3 0 00-3-3V7a3 3 0 00-3-3H7a3 3 0 00-3 3v1a3 3 0 00-3 3v5a1 1 0 001 1z" />
                    </svg>
                    <span>Statistiques</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="nav-icon">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    <span>Retour au site</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

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
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0,  throw new Error('Invalid artifact_version_id');0, 0, 0.05);
    --transition: all 0.3s ease;
    --font-sans: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --sidebar-dark: #1f2937; /* Fond sombre pour toute la sidebar */
    --sidebar-dark-light: rgba(31, 41, 55, 0.05); /* Pour les hover */
}

/* Sidebar Styles */
.sidebar {
    background-color: var(--sidebar-dark);
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    box-shadow: var(--shadow);
    z-index: 100;
    transition: var(--transition);
    overflow-y: auto;
}

.sidebar-header {
    padding: 1.5rem 1rem;
}

/* Logo Styles */
.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
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
}

.logo-icon i {
    font-size: 1.5rem;
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(to right, var(--primary-color), #818cf8);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
}

/* Navigation Styles */
.sidebar-nav {
    padding: 1rem;
    flex-grow: 1;
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
    background-color: white;
    position: relative;
    transition: var(--transition);
}

.menu-icon::before,
.menu-icon::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 2px;
    background-color: white;
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
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: white;
    text-decoration: none;
    font-weight: 500;
    border-radius: 8px;
    transition: var(--transition);
    animation: fadeIn 0.5s ease-out forwards;
}

.nav-link:hover {
    background-color: var(--sidebar-dark-light);
    color: var(--primary-color);
}

.nav-link.active {
    background-color: var(--primary-color);
    color: white;
}

.nav-link.active:hover {
    background-color: var(--primary-hover);
}

.nav-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.75rem;
    fill: currentColor;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 992px) {
    .sidebar {
        width: 200px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 100;
    }

    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .menu-toggle {
        display: block;
    }

    .nav-list {
        display: none;
        padding: 1rem;
        background-color: var(--sidebar-dark);
        box-shadow: var(--shadow);
    }

    .nav-list.active {
        display: block;
    }

    .nav-item {
        margin-bottom: 0.25rem;
    }

    .nav-link {
        padding: 0.75rem;
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

    // Close menu when clicking a link on mobile
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                menuToggle.classList.remove('active');
                navList.classList.remove('active');
            }
        });
    });
});
</script>