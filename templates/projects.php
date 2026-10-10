<?php
/** @var array $ongoing @var array $completed */
$banner = page_banner('projects');
partial('page-banner', [
    'heading'    => $banner['title'],
    'subheading' => $banner['subtitle'],
    'image'      => $banner['image'],
    'crumbs'     => ['Our Projects' => 'projects'],
]);
$all = array_merge($ongoing, $completed);
?>

<section class="section projects-page">
    <div class="container">
        <div class="projects-intro" data-reveal>
            <div>
                <span class="eyebrow">Where your support goes</span>
                <h2 class="force-font">Projects that change children's lives</h2>
                <p>Each project meets a real need of the children in our care. Pick one to support, or give to the general fund and we will use it where it is needed most.</p>
            </div>
            <div class="projects-counts">
                <div><strong><?= count($ongoing) ?></strong><span>Ongoing</span></div>
                <div><strong><?= count($completed) ?></strong><span>Completed</span></div>
            </div>
        </div>

        <?php if ($ongoing && $completed): ?>
            <div class="project-filter" role="tablist" aria-label="Show projects">
                <button type="button" class="is-active" data-filter="all" role="tab" aria-selected="true">All <span><?= count($all) ?></span></button>
                <button type="button" data-filter="ongoing" role="tab" aria-selected="false">Ongoing <span><?= count($ongoing) ?></span></button>
                <button type="button" data-filter="completed" role="tab" aria-selected="false">Completed <span><?= count($completed) ?></span></button>
            </div>
        <?php endif; ?>

        <?php if ($all): ?>
            <div class="row g-4 project-grid">
                <?php foreach ($all as $project): ?>
                    <div class="col-md-6 col-xl-4" data-status="<?= e($project['status']) ?>">
                        <?php partial('project-tile', ['project' => $project]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-hourglass-split"></i>New projects will be announced soon.</div>
        <?php endif; ?>
    </div>
</section>

<section class="section pt-0">
    <div class="container">
        <?php $cta = cta('projects'); partial('cta-band', [
            'eyebrow'  => $cta['eyebrow'],
            'heading'  => $cta['heading'],
            'text'     => $cta['text'],
            'image'    => $cta['image'],
            'position' => $cta['position'],
            'buttons'  => [
                ['Donate to the general fund', url('donate'), 'primary', 'heart-fill'],
                ['Talk to us', url('contact?type=sponsor'), 'outline'],
            ],
        ]); ?>
    </div>
</section>

<?php ob_start(); ?>
<script>
    // Ongoing / Completed filter
    document.querySelectorAll('.project-filter button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var f = btn.getAttribute('data-filter');
            document.querySelectorAll('.project-filter button').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });
            document.querySelectorAll('.project-grid > [data-status]').forEach(function (col) {
                col.hidden = f !== 'all' && col.getAttribute('data-status') !== f;
            });
        });
    });
</script>
<?php $scripts = ob_get_clean(); ?>
