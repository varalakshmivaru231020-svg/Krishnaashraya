<?php /** @var int $selectedProject */ $banner = page_banner('donate'); ?>
<section class="donate-hero" style="background-image: linear-gradient(120deg, rgba(15,23,42,.88), rgba(15,23,42,.6)), url('<?= e(img($banner['image'])) ?>')">
    <div class="container">
        <div class="breadcrumb-lite"><a href="<?= e(url('')) ?>">Home</a><span class="mx-1">/</span>Donate</div>
        <h1 class="force-font"><?= e($banner['title']) ?></h1>
        <p><?= e($banner['subtitle']) ?></p>
    </div>
</section>

<section class="donate-page">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="donate-card">
                    <?php partial('donate-form', ['selectedProject' => $selectedProject, 'selectedAmount' => $selectedAmount ?? 0]); ?>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="donate-summary">
                    <div class="summary-card">
                        <span class="summary-label">Your donation</span>
                        <div class="summary-amount" id="sumAmount">₹0</div>
                        <div class="summary-row"><span>Towards</span><strong id="sumProject">General fund</strong></div>
                        <div class="summary-row"><span>Donor</span><strong id="sumName">—</strong></div>
                        <div class="summary-impact" id="sumImpact"></div>
                    </div>

                    <ul class="trust-list">
                        <li><i class="bi bi-receipt"></i><div><strong>80G tax benefit</strong><span>Up to 50% tax exemption. We hold 80G &amp; 12A certificates.</span></div></li>
                        <li><i class="bi bi-shield-lock"></i><div><strong>Safe &amp; secure</strong><span>Payments are processed by <?= e(active_gateway() ? gateway_label(active_gateway()) : 'a secure payment gateway') ?>. We never see your card details.</span></div></li>
                        <li><i class="bi bi-heart"></i><div><strong>Direct impact</strong><span>Your gift goes to food, shelter and education for our children.</span></div></li>
                    </ul>

                    <div class="bank-alt">
                        <h3 class="bank-alt-title"><i class="bi bi-bank"></i> Prefer bank transfer or UPI?</h3>
                        <table class="bank-mini">
                            <?php foreach ([
                                'Bank'        => setting('bank_name'),
                                'Account name' => setting('bank_account_name'),
                                'Account no.' => setting('bank_account_no'),
                                'IFSC'        => setting('bank_ifsc'),
                                'Branch'      => setting('bank_branch'),
                                'UPI / GPay'  => setting('upi'),
                            ] as $k => $v): if ($v === '') continue; ?>
                                <tr><td><?= e($k) ?></td><td><?= e($v) ?></td></tr>
                            <?php endforeach; ?>
                        </table>
                        <p class="small mb-0">After transferring, please <a href="<?= e(url('contact?type=donation')) ?>">let us know</a> so we can send your receipt.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
