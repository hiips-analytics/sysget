<?php require __DIR__ . '/../components/navigation.hiips.php'; ?>
<div class="page-content">
    <section class="dashboard-hero">
        <div class="hero-copy">
            <span class="eyebrow">Administration</span>
            <h2>Tableau de bord admin</h2>
            <p>Programmez les sessions de cours, gérez les enseignants et les administrateurs depuis une seule interface.</p>
            <div class="hero-actions">
                <a href="/sessions/create" class="button button-primary">Planifier un cours</a>
                <a href="/admin/users" class="button button-secondary">Gérer le personnel</a>
                <a href="/sessions" class="button button-outline">Voir le planning</a>
            </div>
        </div>
        <div class="hero-panel">
            <div class="hero-card dark-card">
                <h3>Sessions</h3>
                <p>Créez et modifiez les sessions pour éviter les conflits de salle et d’enseignants.</p>
            </div>
            <div class="hero-card">
                <h3>Profils</h3>
                <p>Ajoutez ou supprimez des enseignants et des administrateurs en quelques clics.</p>
            </div>
            <div class="hero-card">
                <h3>Accès sécurisé</h3>
                <p>Seuls les administrateurs peuvent accéder à ce tableau de bord.</p>
            </div>
        </div>
    </section>
</div>
