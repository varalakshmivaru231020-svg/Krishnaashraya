<?php
/** Donation form. Only the first name and an amount are required; everything else is optional. */
$presets = array_values(array_filter(array_map('intval', explode(',', setting('donate_presets', '1000,2000,5000,10000'))), fn($a) => $a > 0));
$minAmount = donation_min_rupees();
$maxAmount = (int) config('donation.max_rupees', 1000000);
// A link like /donate?amount=2000 preselects that amount (a preset card, or Custom Amount for anything else).
$askedAmount = (int) ($selectedAmount ?? 0);
$askedAmount = $askedAmount >= $minAmount && $askedAmount <= $maxAmount ? $askedAmount : 0;
$customFirst = $askedAmount && !in_array($askedAmount, $presets, true);
$defaultAmount = $askedAmount ?: ($presets[0] ?? 1000);
$purposes = donation_purposes();
$ongoingProjects = projects_list('ongoing');
$selectedProject = (int) ($selectedProject ?? 0);

// What the "Donate towards" choices show underneath when picked.
$towards = [];
foreach ($purposes as $p) {
    $towards['purpose:' . $p['id']] = ['title' => $p['title'], 'summary' => (string) $p['description'], 'image' => null];
}
foreach ($ongoingProjects as $p) {
    $towards['project:' . $p['id']] = ['title' => $p['title'], 'summary' => mb_strimwidth((string) $p['summary'], 0, 150, '…'), 'image' => img($p['image'])];
}
$selectedTowards = $selectedProject && isset($towards['project:' . $selectedProject]) ? 'project:' . $selectedProject : (array_key_first($towards) ?? '');
$required = '<span class="field-tag is-req">Required</span>';
$optional = '<span class="field-tag">Optional</span>';
?>
<form class="donate-form" id="donateForm" data-cost="<?= (int) setting('stat_cost_per_child', '60000') ?>"
      data-min="<?= $minAmount ?>" data-max="<?= $maxAmount ?>" novalidate>
    <!-- Step 1 -->
    <div class="donate-section">
        <div class="donate-section-head">
            <span class="donate-step-no">1</span>
            <div>
                <h2>Your details</h2>
                <p>Only your first name is needed. Add a phone or email if you'd like a receipt.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="d-first">First name <?= $required ?></label>
                <input type="text" class="form-control" id="d-first" name="first_name" autocomplete="given-name" maxlength="60" placeholder="e.g. Priya" required>
                <div class="field-error" data-error-for="first_name"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="d-last">Last name <?= $optional ?></label>
                <input type="text" class="form-control" id="d-last" name="last_name" autocomplete="family-name" maxlength="60" placeholder="e.g. Sharma">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="d-phone">Phone number <?= $optional ?></label>
                <div class="input-group input-prefix">
                    <span class="input-group-text">+91</span>
                    <input type="tel" class="form-control" id="d-phone" name="phone" autocomplete="tel-national" inputmode="numeric" maxlength="10" placeholder="10-digit mobile number">
                </div>
                <div class="field-error" data-error-for="phone"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="d-email">Email address <?= $optional ?></label>
                <input type="email" class="form-control" id="d-email" name="email" autocomplete="email" maxlength="190" placeholder="For your donation receipt">
                <div class="field-error" data-error-for="email"></div>
            </div>
            <div class="col-12">
                <span class="form-label d-block">Gender <?= $optional ?></span>
                <div class="choice-pills" role="radiogroup" aria-label="Gender (optional)">
                    <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not' => 'Prefer not to say'] as $val => $label): ?>
                        <label class="choice-pill">
                            <input type="radio" name="gender" value="<?= e($val) ?>">
                            <span><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2 -->
    <div class="donate-section">
        <div class="donate-section-head">
            <span class="donate-step-no">2</span>
            <div>
                <h2>Your donation</h2>
                <p>Choose an amount, or pick Custom Amount to enter your own.</p>
            </div>
        </div>

        <span class="form-label d-block">Amount <?= $required ?></span>
        <div class="amount-cards" role="radiogroup" aria-label="Donation amount">
            <?php foreach ($presets as $amt): ?>
                <button type="button" class="amount-card<?= $amt === $defaultAmount ? ' is-selected' : '' ?>" data-amount="<?= $amt ?>" role="radio" aria-checked="<?= $amt === $defaultAmount ? 'true' : 'false' ?>">
                    <span class="amount-card-value"><?= e(inr($amt)) ?></span>
                </button>
            <?php endforeach; ?>
            <button type="button" class="amount-card amount-card-custom<?= $customFirst ? ' is-selected' : '' ?>" data-amount="custom" role="radio" aria-checked="<?= $customFirst ? 'true' : 'false' ?>">
                <i class="bi bi-pencil-square"></i>
                <span class="amount-card-value">Custom Amount</span>
            </button>
        </div>

        <div class="custom-amount-wrap" id="customAmountWrap"<?= $customFirst ? '' : ' hidden' ?>>
            <label class="form-label mt-3" for="d-amount">Enter your amount <?= $required ?></label>
            <div class="input-group amount-input">
                <span class="input-group-text">₹</span>
                <input type="number" class="form-control" id="d-amount" inputmode="numeric" min="<?= $minAmount ?>" max="<?= $maxAmount ?>" step="1"<?= $customFirst ? ' value="' . $askedAmount . '"' : '' ?>
                       placeholder="Minimum <?= e(inr($minAmount)) ?>">
                <span class="input-group-text amount-suffix">INR</span>
            </div>
        </div>
        <input type="hidden" name="amount" value="<?= $defaultAmount ?>">
        <div class="field-error" data-error-for="amount"></div>

        <label class="form-label mt-4" for="d-towards">Donate towards <?= $optional ?></label>
        <div class="select-wrap">
            <i class="bi bi-heart-fill select-icon" aria-hidden="true"></i>
            <select class="form-select project-select" id="d-towards" name="towards">
                <?php if (!$purposes): ?>
                    <option value="">General Donation — wherever it is needed most</option>
                <?php endif; ?>
                <?php if ($purposes && $ongoingProjects): ?><optgroup label="Purpose"><?php endif; ?>
                <?php foreach ($purposes as $p): ?>
                    <option value="purpose:<?= (int) $p['id'] ?>"<?= $selectedTowards === 'purpose:' . $p['id'] ? ' selected' : '' ?>><?= e($p['title']) ?></option>
                <?php endforeach; ?>
                <?php if ($purposes && $ongoingProjects): ?></optgroup><?php endif; ?>
                <?php if ($ongoingProjects): ?>
                    <optgroup label="Ongoing projects">
                        <?php foreach ($ongoingProjects as $p): ?>
                            <option value="project:<?= (int) $p['id'] ?>"<?= $selectedTowards === 'project:' . $p['id'] ? ' selected' : '' ?>><?= e($p['title']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
        </div>
        <div class="project-preview" id="projectPreview" hidden>
            <img src="" alt="">
            <i class="bi bi-heart-pulse preview-icon" aria-hidden="true"></i>
            <div>
                <strong></strong>
                <p></p>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label" for="d-pan">PAN number <?= $optional ?></label>
                <input type="text" class="form-control text-uppercase" id="d-pan" name="pan" maxlength="10" placeholder="ABCDE1234F" autocomplete="off">
                <div class="field-hint">Only needed for an 80G tax-exemption receipt.</div>
                <div class="field-error" data-error-for="pan"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="d-message">Message / In memory of <?= $optional ?></label>
                <input type="text" class="form-control" id="d-message" name="message" maxlength="500" placeholder="e.g. In loving memory of my father">
            </div>
        </div>
    </div>

    <div class="donate-error alert alert-danger d-none" role="alert"></div>

    <button type="submit" class="btn-primary-gradient donate-submit w-100">
        <i class="bi bi-lock-fill"></i> <span class="donate-submit-label">Donate</span>
    </button>
    <div class="pay-methods">
        <span><i class="bi bi-shield-check"></i> 100% secure payment<?= active_gateway() ? ' by ' . e(gateway_label(active_gateway())) : '' ?>. Your card and bank details are never stored by us.</span>
        <span class="pay-badges"><b>UPI</b><b>Cards</b><b>Net Banking</b><b>Wallets</b></span>
    </div>
</form>
<script>window.DONATE_TOWARDS = <?= json_encode($towards, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
