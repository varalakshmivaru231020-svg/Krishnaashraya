<?php /** @var array $p @var array $status @var array $errors */
$isPoster = $p['layout'] === 'poster';
$quick = ['donate' => 'Donate', 'contact' => 'Contact', 'about' => 'About Us', 'projects' => 'Projects', 'our-activities' => 'Activities'];
?>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<div class="rc-status is-<?= $status[0] ?>">
    <i class="bi bi-<?= $status[0] === 'full' ? 'window-stack' : ($status[0] === 'half' ? 'calendar-event' : 'eye-slash') ?>"></i>
    <div><strong><?= e($status[1]) ?></strong><span><?= e($status[2]) ?></span></div>
    <?php if ($p['on'] === '1' && $p['image'] . $p['title'] . $p['text'] !== ''): ?>
        <a class="btn btn-sm btn-outline-secondary ms-auto text-nowrap" target="_blank" href="<?= e(url(($p['pages'] === 'all' ? 'about' : '') . '?popup_preview=1')) ?>"><i class="bi bi-box-arrow-up-right"></i> See it on the website</a>
    <?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-6 order-xl-2">
            <div class="card preview-card popup-preview-card">
                <div class="card-header d-flex align-items-center">
                    <span><i class="bi bi-display me-1"></i> Live preview</span>
                    <span class="small text-muted fw-normal ms-auto">How visitors see it</span>
                </div>
                <div class="popup-stage">
                    <div class="pp-box<?= $isPoster ? ' is-poster' : '' ?>" id="ppBox">
                        <span class="pp-close"><i class="bi bi-x-lg"></i></span>
                        <div class="pp-media" id="ppMedia"<?= $p['image'] ? '' : ' hidden' ?>><img id="ppImg" src="<?= e($p['image'] ? img($p['image']) : '') ?>" alt=""></div>
                        <div class="pp-body" id="ppBody">
                            <div class="pp-title" id="ppTitle"><?= e($p['title']) ?></div>
                            <div class="pp-text" id="ppText" data-show="card"<?= $isPoster ? ' hidden' : '' ?>><?= nl2br(e($p['text'])) ?></div>
                            <span class="pp-btn" id="ppBtn"<?= $p['button'] && $p['link'] ? '' : ' hidden' ?>><?= e($p['button']) ?></span>
                        </div>
                        <div class="pp-empty" id="ppEmpty" hidden><i class="bi bi-image"></i> Choose a photo for the poster</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 order-xl-1">
            <div class="card mb-4">
                <div class="card-header">Popup</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="on" name="on" <?= $p['on'] === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="on">Show the popup on the website</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Style</label>
                        <div class="media-switch" role="radiogroup" aria-label="Popup style">
                            <input type="radio" class="btn-check" name="layout" id="ly_card" value="card"<?= $isPoster ? '' : ' checked' ?>>
                            <label for="ly_card"><i class="bi bi-card-text"></i> Photo with text</label>
                            <input type="radio" class="btn-check" name="layout" id="ly_poster" value="poster"<?= $isPoster ? ' checked' : '' ?>>
                            <label for="ly_poster"><i class="bi bi-file-image"></i> Poster only</label>
                        </div>
                        <div class="form-text" id="layoutHelp"><?= $isPoster ? 'Your poster or flyer, with a heading and a button under it. The poster is clickable too when a link is set.' : 'A photo on top, then your heading, message and button.' ?></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="image">Photo <span class="opt" data-show="card"<?= $isPoster ? ' hidden' : '' ?>>optional</span></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">
                            <span data-show="card"<?= $isPoster ? ' hidden' : '' ?>>A landscape photo works best (about 1200 × 750 px).</span>
                            <span data-show="poster"<?= $isPoster ? '' : ' hidden' ?>>Any shape — an event poster or flyer works well. Keep important text away from the edges.</span>
                            JPG, PNG or WebP up to 10 MB.<?= $p['image'] ? ' Leave empty to keep the current photo.' : '' ?>
                        </div>
                        <?php if ($p['image']): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                <label class="form-check-label small" for="remove_image">Remove the current photo</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title">Heading</label>
                        <input class="form-control" id="title" name="title" maxlength="120" value="<?= e($p['title']) ?>">
                    </div>
                    <div class="mb-3" data-show="card"<?= $isPoster ? ' hidden' : '' ?>>
                        <label class="form-label" for="text">Message</label>
                        <textarea class="form-control" id="text" name="text" rows="3" maxlength="500"><?= e($p['text']) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label" for="button">Button text</label>
                            <input class="form-control" id="button" name="button" maxlength="40" value="<?= e($p['button']) ?>" placeholder="Donate Now">
                        </div>
                        <div class="col">
                            <label class="form-label" for="link">Button link <span class="opt" data-show="poster"<?= $isPoster ? '' : ' hidden' ?>>also used when the poster is clicked</span></label>
                            <input class="form-control" id="link" name="link" maxlength="255" value="<?= e($p['link']) ?>" placeholder="donate">
                        </div>
                    </div>
                    <div class="link-chips mt-2">
                        <?php foreach ($quick as $path => $label): ?>
                            <button type="button" class="link-chip" data-link="<?= e($path) ?>"><?= e($label) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text">A page of this website (like <code>donate</code>) or a full address starting with <code>https://</code> — a WhatsApp or event-registration link works too.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">When it shows</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="pages">Where</label>
                            <select class="form-select" id="pages" name="pages">
                                <option value="home"<?= $p['pages'] === 'home' ? ' selected' : '' ?>>Home page only</option>
                                <option value="all"<?= $p['pages'] === 'all' ? ' selected' : '' ?>>Every page</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="frequency">How often</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="session"<?= $p['frequency'] === 'session' ? ' selected' : '' ?>>Once per visit</option>
                                <option value="day"<?= $p['frequency'] === 'day' ? ' selected' : '' ?>>Once a day</option>
                                <option value="always"<?= $p['frequency'] === 'always' ? ' selected' : '' ?>>Every time a page opens</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="delay">Wait before opening</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="delay" name="delay" min="0" max="30" value="<?= (int) $p['delay'] ?>">
                                <span class="input-group-text">sec</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="start">Show from <span class="opt">optional</span></label>
                            <input type="date" class="form-control" id="start" name="start" value="<?= e($p['start']) ?>">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="end">Show until <span class="opt">optional</span></label>
                            <input type="date" class="form-control" id="end" name="end" value="<?= e($p['end']) ?>">
                        </div>
                    </div>
                    <p class="small text-muted mb-4"><i class="bi bi-info-circle"></i> The popup never opens on the donate page, so it can't interrupt a payment. When you change the photo or wording, visitors who closed the old popup see the new one.</p>
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save popup</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    (function () {
        var box = document.getElementById('ppBox'), media = document.getElementById('ppMedia'), pimg = document.getElementById('ppImg');
        var body = document.getElementById('ppBody'), empty = document.getElementById('ppEmpty'), btn = document.getElementById('ppBtn');
        var file = document.getElementById('image'), remove = document.getElementById('remove_image');
        var saved = pimg.getAttribute('src');
        var help = { card: 'A photo on top, then your heading, message and button.', poster: 'Your poster or flyer, with a heading and a button under it. The poster is clickable too when a link is set.' };

        function layout() { return document.getElementById('ly_poster').checked ? 'poster' : 'card'; }
        function photo() { return pimg.getAttribute('src') || ''; }
        function refresh() {
            var l = layout();
            btn.hidden = !(document.getElementById('button').value.trim() && document.getElementById('link').value.trim());
            document.querySelectorAll('[data-show]').forEach(function (el) { el.hidden = el.getAttribute('data-show') !== l; });
            document.getElementById('layoutHelp').textContent = help[l];
            box.classList.toggle('is-poster', l === 'poster');
            media.hidden = !photo();
            body.hidden = l === 'poster' && !document.getElementById('title').value.trim() && btn.hidden;
            empty.hidden = !(l === 'poster' && !photo());
        }
        document.getElementById('title').addEventListener('input', function (e) { document.getElementById('ppTitle').textContent = e.target.value; refresh(); });
        document.getElementById('text').addEventListener('input', function (e) {
            var t = document.getElementById('ppText'); t.textContent = '';
            e.target.value.split('\n').forEach(function (line, i) { if (i) t.appendChild(document.createElement('br')); t.appendChild(document.createTextNode(line)); });
        });
        document.getElementById('button').addEventListener('input', function (e) { btn.textContent = e.target.value; refresh(); });
        document.getElementById('link').addEventListener('input', refresh);
        document.querySelectorAll('input[name="layout"]').forEach(function (r) { r.addEventListener('change', refresh); });
        file.addEventListener('change', function () {
            if (file.files[0]) { pimg.src = URL.createObjectURL(file.files[0]); if (remove) remove.checked = false; }
            else if (saved) { pimg.src = saved; } else { pimg.removeAttribute('src'); }
            refresh();
        });
        if (remove) remove.addEventListener('change', function () {
            if (remove.checked) { file.value = ''; pimg.removeAttribute('src'); } else { pimg.src = saved; }
            refresh();
        });
        document.querySelectorAll('.link-chip').forEach(function (c) {
            c.addEventListener('click', function () { document.getElementById('link').value = c.getAttribute('data-link'); refresh(); });
        });
        refresh();
    })();
</script>
