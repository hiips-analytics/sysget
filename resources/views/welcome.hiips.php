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