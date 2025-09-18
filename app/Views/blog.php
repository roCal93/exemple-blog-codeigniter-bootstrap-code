<?= view('header') ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Blog</h1>
    </div>
    <div class="col-md-4">
        <form method="get" action="<?= route_to('blogIndex') ?>" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label for="author" class="form-label">Filtrer par auteur</label>
                <select name="author" id="author" class="form-select">
                    <option value="">Tous</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['user_id'] ?>" <?= ($author['user_id'] == $selectedAuthor) ? 'selected' : '' ?>>
                            <?= ($currentUser && $author['user_id'] == $currentUser['id']) ? 'Mes articles' : $author['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="order" value="<?= $currentOrder ?>">
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <a href="<?= route_to('blogIndex') . '?order=' . ($currentOrder === 'DESC' ? 'ASC' : 'DESC') . ($selectedAuthor ? '&author=' . $selectedAuthor : '') ?>" class="btn btn-outline-secondary">
        <?= $currentOrder === 'DESC' ? '<i class="bi bi-sort-down"></i> Trier par plus ancien' : '<i class="bi bi-sort-up"></i> Trier par plus récent' ?>
    </a>
</div>

<?php if (!empty($noArticlesMessage)): ?>
    <div class="alert alert-info">
        <?= $noArticlesMessage ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($news as $new): ?>
            <div class="col">
                <a href="<?= route_to('articleIndex', $new['slug']); ?>" class="text-decoration-none">
                    <div class="card card-blog h-100">
                        <div class="card-body">
                            <h3 class="card-title">
                                <?= $new['title']; ?>
                            </h3>
                            <div class="mb-2">
                                <?php if (!$new['state']): ?>
                                    <span class="badge bg-danger">Non publié</span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text"><?= substr($new['content'], 0, 100); ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> <?= ucfirst($formatter->format(strtotime($new['created_at']))); ?>
                                <br>
                                <i class="bi bi-person"></i> <?= $new['author'] ?>
                            </small>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= view('footer') ?>
