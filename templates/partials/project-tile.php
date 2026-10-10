<?php
/** Project card for grids (Projects page, "Other projects"). @var array $project */
$link = url('projects/' . $project['slug']);
$ongoing = $project['status'] === 'ongoing';
$points = array_slice(lines($project['features']), 0, 3);
$goal = (int) ($project['goal_amount'] ?? 0);
$raised = (int) ($project['raised'] ?? 0);
?>
<article class="project-tile<?= $ongoing ? '' : ' is-completed' ?>" data-status="<?= e($project['status']) ?>" data-reveal>
    <a href="<?= e($link) ?>" class="project-tile-media" tabindex="-1" aria-hidden="true">
        <img src="<?= e(img($project['image'])) ?>" alt="" loading="lazy">
        <span class="project-tile-status"><i class="bi bi-<?= $ongoing ? 'broadcast' : 'check2-circle' ?>"></i> <?= $ongoing ? 'Ongoing' : 'Completed' ?></span>
    </a>
    <div class="project-tile-body">
        <h3><a href="<?= e($link) ?>"><?= e($project['title']) ?></a></h3>
        <?php if ($project['summary']): ?>
            <p class="project-tile-summary"><?= e($project['summary']) ?></p>
        <?php endif; ?>
        <?php if ($points): ?>
            <ul class="project-tile-points">
                <?php foreach ($points as $pt): ?>
                    <li><i class="bi bi-check2"></i><?= e($pt) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($goal > 0): ?>
            <div class="project-tile-progress">
                <div class="d-flex justify-content-between"><span><strong><?= e(inr($raised)) ?></strong> raised</span><span>of <?= e(inr($goal)) ?></span></div>
                <div class="project-progress"><span style="width: <?= min(100, (int) round($raised * 100 / max(1, $goal))) ?>%"></span></div>
            </div>
        <?php endif; ?>
        <div class="project-tile-foot">
            <a href="<?= e($link) ?>" class="link-arrow"><?= $ongoing ? 'View project' : 'Read the story' ?> <i class="bi bi-arrow-right"></i></a>
            <?php if ($ongoing): ?>
                <a href="<?= e(url('donate?project=' . (int) $project['id'])) ?>" class="btn-primary-gradient btn-compact">Donate</a>
            <?php endif; ?>
        </div>
    </div>
</article>
