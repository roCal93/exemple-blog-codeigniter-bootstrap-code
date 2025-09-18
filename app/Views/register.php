<?= view('header') ?>

<div class="form-container w-100">
    <h1 class="mb-4">Rejoins-nous</h1>
    <div class="card">
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?php if (is_array(session()->getFlashdata('error'))): ?>
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('error') as $field => $message): ?>
                                <li><?= esc($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?= esc(session()->getFlashdata('error')) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= route_to('postUserIndex'); ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('email')) ? 'is-invalid' : '' ?>" 
                           value="<?= old('email') ?>" required autocomplete="username">
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('email')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('validation')->getError('email') ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nom *</label>
                    <input type="text" id="name" name="name" class="form-control <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('name')) ? 'is-invalid' : '' ?>" 
                           value="<?= old('name') ?>" required>
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('name')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('validation')->getError('name') ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe *</label>
                    <input type="password" id="password" name="password" class="form-control <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password')) ? 'is-invalid' : '' ?>" 
                           required autocomplete="new-password" minlength="8">
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('validation')->getError('password') ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        <strong>Exigences du mot de passe :</strong>
                        <ul class="mb-0 mt-1">
                            <li>Au moins 8 caractères</li>
                            <li>Une lettre majuscule (A-Z)</li>
                            <li>Une lettre minuscule (a-z)</li>
                            <li>Un chiffre (0-9)</li>
                            <li>Un caractère spécial (@$!%*?&-_)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="form-control <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')) ? 'is-invalid' : '' ?>" 
                           required autocomplete="new-password">
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')): ?>
                        <div class="invalid-feedback">
                            <?= session()->getFlashdata('validation')->getError('confirm_password') ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        S'inscrire
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <small class="text-muted">
                    Déjà un compte ? <a href="<?= route_to('loginIndex') ?>">Se connecter</a>
                </small>
            </div>
        </div>
    </div>
</div>

<?= view('footer') ?>