<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SYSGET | <?= $this->clean($title ?? 'Accueil') ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../css/app.css" />
</head>
<body>
  <header class="topbar fixed">
    <div class="brand">
      <div class="brand-mark">SG</div>
      <div>
        <h1>SYSGET</h1>
        <p class="eyebrow">Gestion d'emploi du temps</p>
      </div>
    </div>
    <nav class="topnav">
      <a href="/home">Accueil</a>
      <a href="/help">Aide</a>
      <a href="/about">À propos</a>
      <a href="/session/index" class="js-dashboard-link">Tableau de bord</a>
    </nav>
    <div class="header-actions">
      <span class="js-user-badge badge hidden"></span>
      <button class="button button-primary js-login">Connexion</button>
      <button class="button button-secondary js-logout hidden">Déconnexion</button>
    </div>
  </header>

  <main>
    <?= $content ?>
  </main>

  <script src="../../js/app.js"></script>
</body>
</html>