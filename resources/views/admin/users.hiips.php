<?php require __DIR__ . '/../components/navigation.hiips.php'; ?>
<div class="page-content">
    <section class="page-section">
        <div class="hero-copy">
            <span class="eyebrow">Gestion du personnel</span>
            <h2>Enseignants et administrateurs</h2>
            <p>Ajoutez, supprimez ou consultez les comptes autorisés à gérer les sessions.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <div class="admin-card">
                <h3>Ajouter un utilisateur</h3>
                <form method="post" action="/admin/users" class="form-grid">
                    <label for="name">Nom</label>
                    <input type="text" name="name" id="name" required />

                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required />

                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required />

                    <label for="role">Rôle</label>
                    <select name="role" id="role">
                        <option value="teacher">Enseignant</option>
                        <option value="admin">Administrateur</option>
                    </select>

                    <button type="submit" class="button button-primary">Créer</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Liste du personnel</h3>
                <div class="entity-table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                                    <td>
                                        <a href="/admin/deleteUser/<?= intval($user['id']) ?>" class="action-button action-danger">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
