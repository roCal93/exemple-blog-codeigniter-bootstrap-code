<?= view('header') ?>

<div class="form-container w-100">
    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <h1 class="mb-4"><?= isset($article) ? 'Modifier l\'article' : 'Créer un article' ?></h1>
    <div class="card">
        <div class="card-body">
            <form action="<?= isset($article) ? route_to('updateIndex', $article['id']) : route_to('postIndex') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="title" class="form-label">Titre :</label>
                    <input type="text" name="title" id="title" value="<?= isset($article) ? esc($article['title']) : '' ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Contenu :</label>
                    <textarea id="content" name="content" class="form-control article-content-textarea" rows="12" required><?= isset($article) ? esc($article['content']) : '' ?></textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="state" id="state" value="1" class="form-check-input" <?= isset($article) && $article['state'] ? 'checked' : '' ?>>
                    <label for="state" class="form-check-label">Publier directement</label>
                </div>
                <div class="d-grid">
                <button type="submit" class="btn btn-primary"><?= isset($article) ? 'Modifier l\'article' : 'Créer un article' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('footer') ?>