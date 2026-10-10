<?php /** @var array $rows @var string $ga @var string $gsc */
$base = rtrim((string) config('app.base_url', ''), '/');
$len = fn(string $s) => mb_strlen($s);
$state = function (int $n, int $min, int $max): string {
    return $n === 0 ? 'bad' : ($n < $min || $n > $max ? 'warn' : 'good');
};
$groups = [];
foreach ($rows as $r) {
    $groups[$r['group']][] = $r;
}
?>
<ul class="nav admin-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-pages" data-bs-toggle="tab" data-bs-target="#pane-pages" type="button" role="tab" aria-controls="pane-pages" aria-selected="true"><i class="bi bi-search"></i> Pages <span class="tab-count"><?= count($rows) ?></span></button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-audit" data-bs-toggle="tab" data-bs-target="#pane-audit" type="button" role="tab" aria-controls="pane-audit" aria-selected="false"><i class="bi bi-clipboard2-pulse"></i> SEO audit</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" id="tab-google" data-bs-toggle="tab" data-bs-target="#pane-google" type="button" role="tab" aria-controls="pane-google" aria-selected="false"><i class="bi bi-google"></i> Google</button></li>
</ul>

<div class="tab-content">
    <!-- ============================================ Pages -->
    <div class="tab-pane fade show active" id="pane-pages" role="tabpanel" aria-labelledby="tab-pages" tabindex="0">
        <p class="page-lead">How each page appears in Google search results. Pages use good automatic text from their own content; edit any page to write your own.
            Best length: title <strong>30–60</strong> characters, description <strong>70–160</strong>.</p>
        <?php foreach ($groups as $group => $items): ?>
            <div class="card list-card mb-4">
                <div class="card-header"><div><?= e($group) ?> <span class="sub"><?= count($items) ?> page<?= count($items) === 1 ? '' : 's' ?></span></div></div>
                <div class="seo-list">
                    <?php foreach ($items as $r):
                        $tl = $len($r['final_title']); $dl = $len($r['final_description']);
                        $ts = $state($tl, 30, 60); $ds = $state($dl, 70, 160); ?>
                        <div class="seo-row" data-key="<?= e($r['key']) ?>">
                            <div class="serp">
                                <div class="serp-url"><span class="serp-favicon"></span><?= e(preg_replace('#^https?://#', '', $r['url'])) ?></div>
                                <div class="serp-title"><?= e($r['final_title']) ?></div>
                                <div class="serp-desc"><?= e($r['final_description']) ?></div>
                            </div>
                            <div class="seo-meta">
                                <span class="seo-chip is-<?= $ts ?>" title="Title length">Title <?= $tl ?></span>
                                <span class="seo-chip is-<?= $ds ?>" title="Description length">Description <?= $dl ?></span>
                                <span class="seo-chip <?= $r['custom'] ? 'is-custom' : 'is-auto' ?>"><?= $r['custom'] ? 'Custom' : 'Automatic' ?></span>
                                <span class="seo-audit-chip" data-audit-for="<?= e($r['key']) ?>" hidden></span>
                            </div>
                            <div class="row-actions">
                                <a class="icon-action" href="<?= e(url($r['path'])) ?>" target="_blank" title="View page" aria-label="View page"><i class="bi bi-box-arrow-up-right"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#seoModal"
                                        data-key="<?= e($r['key']) ?>" data-label="<?= e($r['label']) ?>" data-url="<?= e(preg_replace('#^https?://#', '', $r['url'])) ?>"
                                        data-title="<?= e($r['custom'] ? setting('seo_' . $r['key'] . '_title') : '') ?>" data-description="<?= e($r['custom'] ? setting('seo_' . $r['key'] . '_description') : '') ?>"
                                        data-auto-title="<?= e($r['title']) ?>" data-auto-description="<?= e($r['description']) ?>"><i class="bi bi-pencil"></i> Edit</button>
                                <?php if ($r['custom']): ?>
                                    <form method="post" class="d-inline" data-confirm="Go back to the automatic title and description for this page?">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="key" value="<?= e($r['key']) ?>">
                                        <button class="icon-action" title="Use automatic text" aria-label="Use automatic text"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ============================================ Audit -->
    <div class="tab-pane fade" id="pane-audit" role="tabpanel" aria-labelledby="tab-audit" tabindex="0">
        <div class="card mb-4">
            <div class="card-body audit-hero">
                <div class="audit-score" id="auditScore"><svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="52"/><circle class="arc" cx="60" cy="60" r="52"/></svg><span>—</span></div>
                <div class="flex-grow-1">
                    <h2 class="h5 mb-1">Check every page the way Google reads it</h2>
                    <p class="text-muted mb-3">Opens all <?= count($rows) ?> pages, the sitemap and robots.txt, and checks titles, descriptions, headings, image text, sharing tags and more.</p>
                    <button type="button" class="btn btn-primary" id="runAudit"><i class="bi bi-play-fill"></i> Run SEO audit</button>
                    <span class="small text-muted ms-2" id="auditStatus"></span>
                </div>
            </div>
        </div>
        <div id="auditResults" hidden>
            <div class="card mb-4">
                <div class="card-header">Whole website</div>
                <ul class="audit-list" id="siteChecks"></ul>
            </div>
            <div class="card">
                <div class="card-header">Page by page <span class="sub fw-normal ms-2">Click a page to see what to improve</span></div>
                <div id="pageChecks"></div>
            </div>
        </div>
    </div>

    <!-- ============================================ Google -->
    <div class="tab-pane fade" id="pane-google" role="tabpanel" aria-labelledby="tab-google" tabindex="0">
        <form method="post" class="card" id="google">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="google">
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label" for="ga_measurement_id">Google Analytics / Tag Manager</label>
                    <textarea class="form-control font-monospace" id="ga_measurement_id" name="ga_measurement_id" rows="3" placeholder="Paste the Google tag code here (or just the G-… ID)"><?= e($ga) ?></textarea>
                    <div class="form-text">Google Analytics → Admin → Data streams → your website → View tag instructions → Install manually. The site keeps only the ID and adds the code to every public page (never to the admin, never on your own computer).</div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="gsc_verification">Google Search Console verification</label>
                    <input class="form-control font-monospace" id="gsc_verification" name="gsc_verification" value="<?= e($gsc) ?>" placeholder='<meta name="google-site-verification" content="…">'>
                    <div class="form-text">Search Console → Add property → URL prefix → HTML tag. Then submit <code>sitemap.xml</code> under Sitemaps.</div>
                </div>
                <div class="seo-links mb-4">
                    <i class="bi bi-diagram-3"></i>
                    <div>Created automatically: <a href="<?= e(url('sitemap.xml')) ?>" target="_blank">sitemap.xml</a> (all pages, updated as you add projects and activities) and <a href="<?= e(url('robots.txt')) ?>" target="_blank">robots.txt</a>.</div>
                </div>
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Google settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit one page -->
<div class="modal fade" id="seoModal" tabindex="-1" aria-labelledby="seoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="page">
            <input type="hidden" name="key" value="">
            <div class="modal-header">
                <h2 class="modal-title" id="seoModalTitle">Search settings</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="serp serp-large mb-4" aria-live="polite">
                    <div class="serp-url"><span class="serp-favicon"></span><span data-p-url></span></div>
                    <div class="serp-title" data-p-title></div>
                    <div class="serp-desc" data-p-desc></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between"><label class="form-label" for="sm-title">Title in Google</label><span class="len-count" data-count-for="sm-title" data-min="30" data-max="60"></span></div>
                    <input class="form-control" id="sm-title" name="title" maxlength="120">
                    <div class="form-text">Leave empty to use the automatic title. Put the most important words first.</div>
                </div>
                <div>
                    <div class="d-flex justify-content-between"><label class="form-label" for="sm-desc">Description in Google</label><span class="len-count" data-count-for="sm-desc" data-min="70" data-max="160"></span></div>
                    <textarea class="form-control" id="sm-desc" name="description" rows="3" maxlength="320"></textarea>
                    <div class="form-text">One or two sentences that make people want to click. Leave empty to use the automatic description.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<script>window.SEO_PAGES = <?= json_encode(array_map(fn($r) => ['key' => $r['key'], 'label' => $r['label'], 'path' => $r['path']], $rows), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>; window.SEO_BASE = <?= json_encode($base) ?>;</script>
<script src="<?= e(url('assets/js/seo-audit.js')) ?>"></script>
