<?= view('header') ?>

<div class="form-container">
    <h1 class="mb-4">Connexion</h1>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form action="<?= route_to('authIndex'); ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            Pas encore de compte ? <a href="<?= route_to('registerIndex'); ?>">S'inscrire</a>
        </div>
    </div>
</div>

<?= view('footer') ?>