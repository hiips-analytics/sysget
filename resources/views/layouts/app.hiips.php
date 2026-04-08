<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sys.G.E.T | <?= $this->clean($title ?? 'Accueil') ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" /> -->
  <link rel="stylesheet" href="../../css/app.css" />
</head>
<body>

  <main>
    <?= $content ?>
  </main>

  <script src="../../js/app.js"></script>
</body>
</html>