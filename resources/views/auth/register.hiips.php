<?php require __DIR__ . '/../components/navigation.hiips.php'; ?>
<div class="page-content login-page">
    <section class="login-panel">
        <div class="login-card">
            <span class="eyebrow">Création de compte</span>
            <h2>Inscrivez-vous sur Sys.G.E.T</h2>
            <p>
                Créez votre compte pour accéder à votre planning et gérer les sessions.
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
        <form method="post" action="/register" class="form-grid">
            <label for="name">Nom complet</label>
            <input
                type="text"
                name="name"
                id="name"
                value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                placeholder="Votre nom"
                required
            />

            <label for="email">Email</label>
            <input
                type="email"
                name="email"
                id="email"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                placeholder="votre.email@edu.test"
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

            <label for="password_confirm">Confirmer le mot de passe</label>
            <input
                type="password"
                name="password_confirm"
                id="password_confirm"
                placeholder="••••••••"
                required
            />

            <label for="role">Rôle</label>
            <select name="role" id="role">
                <option value="student" <?= ($old['role'] ?? '') === 'student' ? 'selected' : '' ?>>Étudiant</option>
                <option value="teacher" <?= ($old['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Enseignant</option>
                <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>

            <label for="field_id">Filière (étudiant uniquement)</label>
            <select name="field_id" id="field_id">
                <option value="">Choisir une filière</option>
                <?php foreach ($fields as $field): ?>
                    <option value="<?= $field['id'] ?>" <?= ($old['field_id'] ?? '') == $field['id'] ? 'selected' : '' ?>><?= htmlspecialchars($field['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button button-primary">
                S'inscrire
            </button>
        </form>
        <p class="form-note">
            Déjà un compte ? <a href="/login">Connexion</a>
        </p>
    </div>
</section>
</div>
