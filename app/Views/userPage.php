<?= view('header') ?>

<div class="container mt-5" style="max-width: 900px;">
    <h1 class="mb-4">
        <i class="bi bi-person-circle"></i> Mon profil
    </h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php $errors = session()->getFlashdata('error'); ?>
            <?php if ($errors): ?>
                <div style="color: red;">
                    <?php if (is_array($errors)): ?>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?= esc($errors) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <p style="color: green;"><?= esc(session()->getFlashdata('success')) ?></p>
            <?php endif; ?>

            <h2>Mettre à jour mes informations</h2>
            <form action="<?= route_to('updateUserIndex', $currentUser['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" value="<?= esc(old('email', $currentUser['email'] ?? '')) ?>" class="form-control" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer):</label>
                    <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                    <small class="text-muted">Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</small>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nom:</label>
                    <input type="text" id="name" name="name" value="<?= esc(old('name', $currentUser['name'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="old_password" class="form-label">Mot de passe actuel (requis pour toute modification):</label>
                    <input type="password" id="old_password" name="old_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">Mettre à jour mes informations</button>
                </div>
            </form>

            <hr>

            <h2>Supprimer mon profil</h2>
            <form action="<?= route_to('deleteUserIndex', $currentUser['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="delete_password" class="form-label">Mot de passe actuel (requis pour suppression):</label>
                    <input type="password" id="delete_password" name="delete_password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" id="confirm_delete" name="confirm_delete" class="form-check-input" required>
                    <label for="confirm_delete" class="form-check-label">Je confirme vouloir supprimer mon profil (cette action est irréversible).</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" id="delete_articles" name="delete_articles" value="1" class="form-check-input">
                    <label for="delete_articles" class="form-check-label">Supprimer également tous mes articles</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">Supprimer mon profil</button>
                </div>
            </form>

            <?php if ($currentUser['role'] === 'admin' && !empty($allUsers)): ?>
                <hr>
                <h2 class="h4 mb-3">
                    <i class="bi bi-people"></i> Gestion des utilisateurs
                </h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td><?= esc($user['id']) ?></td>
                                    <td><?= esc($user['name']) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                            <?= esc($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                            <div class="btn-group gap-4" role="group">
                                                <form action="<?= route_to('deleteUserIndex', $user['id']) ?>" method="post" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="confirm_delete" value="1">
                                                    <input type="hidden" name="delete_articles" value="0">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i> Profile
                                                    </button>
                                                </form>
                                                <form action="<?= route_to('deleteUserIndex', $user['id']) ?>" method="post" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="confirm_delete" value="1">
                                                    <input type="hidden" name="delete_articles" value="1">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i> Profile + articles
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-person-check"></i> Vous-même
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('footer') ?>