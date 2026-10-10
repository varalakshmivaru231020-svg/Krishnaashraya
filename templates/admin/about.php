<?php /** @var array $v @var string $image @var array $errors */ ?>
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <p class="page-lead mb-0">The About section appears on the <strong>home page</strong> and the <strong>About Us page</strong>. Leave an optional box empty to hide that line.</p>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= e(url('#about')) ?>" target="_blank" class="btn btn-light btn-sm"><i class="bi bi-house"></i> View on home page</a>
        <a href="<?= e(url('about')) ?>" target="_blank" class="btn btn-light btn-sm"><i class="bi bi-box-arrow-up-right"></i> View About Us page</a>
    </div>
</div>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data" class="about-form">
    <?= csrf_field() ?>

    <!-- 1. Section heading -->
    <div class="card mb-4">
        <div class="card-header"><span class="step-no">1</span> Section heading <span class="sub">The centred title above the About section</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="about_badge">Small label <span class="opt">optional</span></label>
                    <input class="form-control" id="about_badge" name="about_badge" maxlength="30" value="<?= e($v['about_badge']) ?>" placeholder="About Us">
                </div>
                <div class="col-md-9">
                    <label class="form-label" for="about_title">Title <span class="text-danger">*</span></label>
                    <input class="form-control" id="about_title" name="about_title" maxlength="150" value="<?= e($v['about_title']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="about_subtitle">Subtitle <span class="opt">optional</span></label>
                    <input class="form-control" id="about_subtitle" name="about_subtitle" maxlength="200" value="<?= e($v['about_subtitle']) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Story + photo -->
    <div class="card mb-4">
        <div class="card-header"><span class="step-no">2</span> Our story <span class="sub">Text on the left, photo on the right</span></div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label class="form-label" for="about_heading">Heading <span class="opt">optional</span></label>
                        <input class="form-control" id="about_heading" name="about_heading" maxlength="150" value="<?= e($v['about_heading']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="about_text">Text</label>
                        <textarea class="form-control" id="about_text" name="about_text" rows="8"><?= e($v['about_text']) ?></textarea>
                        <div class="form-text">Leave an empty line between paragraphs.</div>
                    </div>
                    <div>
                        <label class="form-label" for="about_quote">Highlighted quote <span class="opt">optional</span></label>
                        <textarea class="form-control" id="about_quote" name="about_quote" rows="2" maxlength="300"><?= e($v['about_quote']) ?></textarea>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Photo</label>
                    <img id="aboutPreview" src="<?= e(img($image)) ?>" class="about-photo-preview" alt="">
                    <input type="file" class="form-control mt-2" name="about_image" accept="image/jpeg,image/png,image/webp" data-preview="#aboutPreview">
                    <div class="form-text">Leave empty to keep the current photo.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Key numbers -->
    <div class="card mb-4">
        <div class="card-header"><span class="step-no">3</span> Key numbers <span class="sub">Shown under the text. Numbers like “12+” or “1,000+” count up; text like “24/7” is shown as it is.</span></div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ([1, 2, 3] as $n): ?>
                    <div class="col-md-4">
                        <div class="figure-box">
                            <div class="figure-box-no">Number <?= $n ?></div>
                            <label class="form-label small" for="about_stat<?= $n ?>_value">Value</label>
                            <input class="form-control form-control-lg fw-bold" id="about_stat<?= $n ?>_value" name="about_stat<?= $n ?>_value" maxlength="20" value="<?= e($v["about_stat{$n}_value"]) ?>" placeholder="e.g. 12+">
                            <label class="form-label small mt-2" for="about_stat<?= $n ?>_label">Label</label>
                            <input class="form-control" id="about_stat<?= $n ?>_label" name="about_stat<?= $n ?>_label" maxlength="60" value="<?= e($v["about_stat{$n}_label"]) ?>" placeholder="e.g. Years of Service">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">Leave both boxes of a number empty to hide it.</div>
        </div>
    </div>

    <!-- 4. Mission card -->
    <div class="card mb-4">
        <div class="card-header"><span class="step-no">4</span> Mission card <span class="sub">The small white card on the photo</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="about_mission_label">Card title</label>
                    <input class="form-control" id="about_mission_label" name="about_mission_label" maxlength="40" value="<?= e($v['about_mission_label']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="mission_short">Card text <span class="opt">empty hides the card</span></label>
                    <input class="form-control" id="mission_short" name="mission_short" maxlength="200" value="<?= e($v['mission_short']) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Vision, mission, our work -->
    <div class="card mb-4">
        <div class="card-header"><span class="step-no">5</span> Vision, mission, goals &amp; our work <span class="sub">The sections below the story</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="vm_title">Section title</label>
                    <input class="form-control" id="vm_title" name="vm_title" maxlength="150" value="<?= e($v['vm_title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="vision">Our Vision</label>
                    <textarea class="form-control" id="vision" name="vision" rows="4"><?= e($v['vision']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mission">Our Mission</label>
                    <textarea class="form-control" id="mission" name="mission" rows="4"><?= e($v['mission']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="goals">Our Goals <span class="opt">one goal per line · empty hides the card</span></label>
                    <textarea class="form-control" id="goals" name="goals" rows="4"><?= e($v['goals']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="our_work">Our Work</label>
                    <textarea class="form-control" id="our_work" name="our_work" rows="6"><?= e($v['our_work']) ?></textarea>
                    <div class="form-text">Leave an empty line between paragraphs.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-savebar">
        <button class="btn btn-primary px-4"><i class="bi bi-check-lg"></i> Save About section</button>
    </div>
</form>
