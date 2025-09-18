<?= view('header') ?>

<div class="my-4">
    <?php if (isset($currentUser)): ?>
        <h1>Bonjour <strong><?= $currentUser['name'] ?></strong>, bienvenue sur le blog !</h1>
    <?php else: ?>
        <h1>Bienvenue sur le blog !</h1>
    <?php endif; ?>
</div>
<h3 class="my-4">Derniers articles</h3>
<div class="row row-cols-1 row-cols-md-2 g-4">
    <?php foreach($news as $new):?>
        <div class="col d-flex align-items-stretch">
            <a href="<?= route_to('articleIndex', $new['slug']) ?>" class="text-decoration-none w-100">
            <div class="card card-blog h-100">
                <div class="card-body">
                    <h3 class="card-title"><?= $new['title']; ?></h3>
                    <p class="card-text"><?= substr($new['content'], 0, 100) . '...'; ?></p>
                </div>
                <div class="card-footer bg-transparent mt-auto">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> <?= ucfirst($formatter->format(strtotime($new['created_at']))); ?>
                        <br>
                        <i class="bi bi-person"></i> <?=$new['author']?></em>
                    </small>
                </div>
            </div>
            </a>
        </div>
    <?php endforeach;?>
</div>
<?= view('footer') ?>


