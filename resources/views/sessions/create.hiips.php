<?php require __DIR__ . '/../components/navigation.hiips.php'; ?>
<div class="page-content">
    <section class="login-panel">
        <div class="login-card">
            <span class="eyebrow">Planification</span>
            <h2>Créer une session de cours</h2>
            <p>
                Planifiez une nouvelle session en choisissant le cours, la salle et l'horaire.
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
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="/sessions/store" class="form-grid">
                <label for="course_id">Cours</label>
                <select name="course_id" id="course_id" required>
                    <option value="">Choisir un cours</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= ($old['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['teacher_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label for="classroom_id">Salle</label>
                <select name="classroom_id" id="classroom_id" required>
                    <option value="">Choisir une salle</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>" <?= ($old['classroom_id'] ?? '') == $room['id'] ? 'selected' : '' ?>><?= htmlspecialchars($room['name']) ?> (capacité: <?= $room['capacity'] ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label for="day_of_week">Jour</label>
                <select name="day_of_week" id="day_of_week" required>
                    <option value="">Choisir un jour</option>
                    <option value="Lundi" <?= ($old['day_of_week'] ?? '') === 'Lundi' ? 'selected' : '' ?>>Lundi</option>
                    <option value="Mardi" <?= ($old['day_of_week'] ?? '') === 'Mardi' ? 'selected' : '' ?>>Mardi</option>
                    <option value="Mercredi" <?= ($old['day_of_week'] ?? '') === 'Mercredi' ? 'selected' : '' ?>>Mercredi</option>
                    <option value="Jeudi" <?= ($old['day_of_week'] ?? '') === 'Jeudi' ? 'selected' : '' ?>>Jeudi</option>
                    <option value="Vendredi" <?= ($old['day_of_week'] ?? '') === 'Vendredi' ? 'selected' : '' ?>>Vendredi</option>
                    <option value="Samedi" <?= ($old['day_of_week'] ?? '') === 'Samedi' ? 'selected' : '' ?>>Samedi</option>
                </select>

                <label for="start_time">Heure de début</label>
                <input
                    type="time"
                    name="start_time"
                    id="start_time"
                    value="<?= htmlspecialchars($old['start_time'] ?? '') ?>"
                    required
                />

                <label for="end_time">Heure de fin</label>
                <input
                    type="time"
                    name="end_time"
                    id="end_time"
                    value="<?= htmlspecialchars($old['end_time'] ?? '') ?>"
                    required
                />

                <button type="submit" class="button button-primary">
                    Créer la session
                </button>
            </form>
            <p class="form-note">
                <a href="/admin">Retour au tableau de bord</a>
            </p>
        </div>
    </section>
</div>