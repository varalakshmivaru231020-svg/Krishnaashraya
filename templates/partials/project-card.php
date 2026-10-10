<?php
/** @var array $project */
$features = lines($project['features']);
$goal = (int) ($project['goal_amount'] ?? 0);
$raised = (int) ($project['raised'] ?? 0);
$link = url('projects/' . $project['slug']);
?>
<div class="project-card">
    <div class="row g-0">
        <div class="col-lg-6">
            <a href="<?= e($link) ?>" class="project-image-wrapper d-block">
                <img src="<?= e(img($project['image'])) ?>" alt="<?= e($project['title']) ?>" class="project-image" loading="lazy">
            </a>
        </div>
        <div class="col-lg-6">
            <div class="project-content">
                <div class="project-icon"><i class="bi bi-book-half"></i></div>
                <span class="project-badge"><?= $project['status'] === 'completed' ? 'Completed Project' : 'Active Project' ?></span>
                <h3 class="project-title"><a href="<?= e($link) ?>" class="text-reset"><?= e($project['title']) ?></a></h3>
                <?php if ($project['summary']): ?>
                    <p class="project-description"><?= nl2br(e($project['summary'])) ?></p>
                <?php endif; ?>
                <?php if ($features): ?>
                    <ul class="project-features">
                        <?php foreach (array_slice($features, 0, 5) as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($goal > 0): ?>
                    <div class="my-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><strong><?= e(inr($raised)) ?></strong> raised</span>
                            <span>Goal <?= e(inr($goal)) ?></span>
                        </div>
                        <div class="project-progress"><span style="width: <?= min(100, (int) round($raised * 100 / $goal)) ?>%"></span></div>
                    </div>
                <?php endif; ?>
                <div class="mt-4">
                    <?php if ($project['status'] === 'ongoing'): ?>
                        <a class="btn-primary-gradient mb-3 d-inline-block js-donate" data-project="<?= (int) $project['id'] ?>" href="<?= e(url('donate?project=' . (int) $project['id'])) ?>">Donate Now</a>
                    <?php endif; ?>
                    <a class="btn-secondary-outline ms-2 mb-3 text-decoration-none" href="<?= e($link) ?>">Read More</a>
                </div>
            </div>
        </div>
    </div>
</div>
