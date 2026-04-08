<?php require __DIR__ . '/../components/navigation.hiips.php'; ?>
<div class="page-content login-page">
    <section class="login-panel">
        <div class="login-card">
            <span class="eyebrow">Connexion sécurisée</span>
            <h2>Accédez à votre espace EduPlanner</h2>
            <p>
                Entrez vos identifiants pour consulter votre emploi du temps et gérer les sessions.
            </p>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="/login" class="form-grid">
            <label for="email">Email</label>
            <input
                type="email"
                name="email"
                id="email"
                placeholder="votre.email@edu.test"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                required
            />
            <label for="password">Mot de passe</label>
            <input
                type="password"
                name="password"
                id="password"
                placeholder="••••••••"
                required
            />
            <button type="submit" class="button button-primary">
                Se connecter
            </button>
        </form>
        <div class="credential-box">
            <strong>Comptes démo :</strong>
            <div>Admin: admin@edu.test / admin123</div>
            <div>Teacher: teacher@edu.test / teacher123</div>
            <div>Student: student@edu.test / student123</div>
        </div>
        <p class="form-note">
            Pas encore de compte ? <a href="/register">Inscrivez-vous</a>
        </p>
    </div>
</section>
</div>
