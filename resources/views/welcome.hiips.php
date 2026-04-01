<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $this->clean($title) ?>
    </title>
</head>
<body>
    <section class="hero-section">
        <div class="hero-content">
            <span class="badge">Session 2025-2026</span>
            <h1>Organisez votre parcours académique avec <span class="text-glow">EduPlanner</span></h1>
            <p>Une interface fluide pour consulter vos cours, gérer vos salles et optimiser votre temps de travail.</p>
            
            <div class="hero-actions">
                <button class="button button-primary js-login">Commencer maintenant</button>
                <a href="/about" class="button button-secondary">En savoir plus</a>
            </div>
        </div>
    </section>
    <div style="text-align: center;">
        <h1>Bonjour <?= $this->clean($user) ?></h1>
        <h2><?= $this->clean($title) ?></h2>
        <br>
        <br>
        <br>
        <br>
        <div>
            <a href="/sessions/index">Voir les sessions</a>
        </div>
    </div>
</body>
</html>