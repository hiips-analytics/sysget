<?php
use App\Core\Auth;

Auth::start();
$user = Auth::user();
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$currentPage = $currentPath === '' ? 'home' : explode('/', $currentPath)[0];
?>
<header class="topbar">
    <div class="brand">
        <div class="brand-mark">SG</div>
        <div>
            <h1>Sys.G.E.T</h1>
            <p class="eyebrow">Gestion d'emploi du temps</p>
        </div>
    </div>
    <nav class="topnav">
        <a href="/home" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Accueil</a>
        <a href="/sessions" class="<?= $currentPage === 'sessions' ? 'active' : '' ?>">Sessions</a>
        <?php if ($user && in_array($user['role'], ['admin', 'teacher'], true)): ?>
            <a href="/sessions/create" class="<?= $currentPage === 'sessions' ? 'active' : '' ?>">Planifier</a>
        <?php endif; ?>
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="/admin" class="<?= $currentPage === 'admin' ? 'active' : '' ?>">Admin</a>
        <?php endif; ?>
    </nav>
    <div class="header-actions">
        <?php if ($user): ?>
            <span class="badge"><?= htmlspecialchars($user['name']) ?></span>
            <a href="/logout" class="button button-secondary">Déconnexion</a>
        <?php else: ?>
            <a href="/register" class="button button-outline">Inscription</a>
            <a href="/login" class="button button-primary">Connexion</a>
        <?php endif; ?>
    </div>
</header>
