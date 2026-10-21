<?php
/**
 * Standalone page for a donation's Receipt or Detailed Statement (the A4 body comes from partials/receipt-*.php).
 * Desktop: the A4 sheet with an action panel at its side. Phone: the sheet first, the panel underneath.
 * @var array $d @var bool $paid @var string $doc @var array $pay @var string $paidAt @var string $year @var string $receiptNo @var int $seq @var float $rupees
 */
$money = fn(float $r, bool $decimals = false): string => inr($r, false) . ($decimals && floor($r) == $r ? '.00' : '');
$fileName = ($doc === 'statement' ? 'Donation Statement ' : 'Donation Receipt ') . str_replace('/', '-', $receiptNo);
$firstName = explode(' ', trim((string) $d['name']))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($paid ? $fileName : 'Donation receipt') ?></title>
    <?php partial('favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --ink: #1f2937; --muted: #6b7280; --row: #f3f4f6; --line: #e2e8f0; --brand: #ff6b00; --ui: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body { min-height: 100vh; background: #f1f5f9 linear-gradient(180deg, #fff4ec 0, #f1f5f9 340px) no-repeat; color: var(--ink); font-family: Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* ---- top bar */
        .rh { background: rgba(255, 255, 255, .92); border-bottom: 1px solid var(--line); backdrop-filter: blur(8px); }
        .rh-in { max-width: 1180px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; font-family: var(--ui); }
        .rh-brand img { height: 42px; width: auto; display: block; }
        .rh-secure { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 6px 12px; border-radius: 30px; }

        /* ---- page layout */
        .rl { display: grid; grid-template-columns: 210mm 300px; gap: 28px; align-items: start; justify-content: center; max-width: 1180px; margin: 0 auto; padding: 28px 24px 56px; }
        .rl-doc { min-width: 0; }
        .sheet { position: relative; width: 210mm; min-height: 296mm; margin: 0 auto; padding: 12mm 14mm 16mm; background: #fff; border-radius: 4px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 18px 50px rgba(15, 23, 42, .12); }
        .page-no { position: absolute; right: 14mm; bottom: 7mm; font-size: 11px; color: var(--muted); }

        /* ---- side panel */
        .rp { position: sticky; top: 24px; display: grid; gap: 16px; font-family: var(--ui); }
        .rp-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 22px; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .rp-status { display: flex; align-items: center; gap: 12px; }
        .rp-check { flex: 0 0 42px; height: 42px; border-radius: 50%; display: grid; place-items: center; background: #dcfce7; color: #16a34a; font-size: 22px; }
        .rp-status strong { display: block; font-size: 15px; }
        .rp-status small { display: block; margin-top: 2px; font-size: 13px; color: var(--muted); }
        .rp-amount { margin: 20px 0 2px; font-size: 32px; font-weight: 800; letter-spacing: -.5px; }
        .rp-for { font-size: 13px; color: var(--muted); }
        .rp-list { margin: 18px 0 20px; padding: 0; }
        .rp-list div { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-top: 1px dashed var(--line); font-size: 13px; }
        .rp-list dt { color: var(--muted); }
        .rp-list dd { margin: 0; font-weight: 600; text-align: right; word-break: break-all; }
        .rp-btn { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 48px; margin-top: 10px; border-radius: 12px; border: 1.5px solid var(--line); background: #fff; color: var(--ink); font: 700 15px var(--ui); text-decoration: none; cursor: pointer; transition: transform .15s, box-shadow .15s, border-color .15s, background .15s; }
        .rp-btn:hover { border-color: #cbd5e1; background: #f8fafc; }
        .rp-btn:focus-visible { outline: 3px solid rgba(255, 107, 0, .35); outline-offset: 2px; }
        .rp-btn.is-primary { margin-top: 0; border: 0; color: #fff; background: linear-gradient(135deg, #ff6b00, #e53935); box-shadow: 0 10px 22px rgba(229, 57, 53, .28); }
        .rp-btn.is-primary:hover { transform: translateY(-1px); box-shadow: 0 14px 26px rgba(229, 57, 53, .34); }
        .rp-btn.is-ghost { border-color: transparent; background: none; color: var(--muted); font-weight: 600; }
        .rp-btn.is-ghost:hover { color: var(--ink); background: #f8fafc; }
        .rp-tip { display: flex; gap: 8px; margin: 14px 0 0; font-size: 12.5px; line-height: 1.5; color: var(--muted); }
        .rp-note { display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px; background: #fff7ed; border: 1px solid #fed7aa; font-size: 12.5px; line-height: 1.5; color: #7c2d12; }
        .rp-note i { font-size: 20px; color: var(--brand); }
        .rp-note strong { display: block; font-size: 13.5px; color: #9a3412; }

        .notice { max-width: 520px; margin: 48px auto; padding: 32px; background: #fff; border: 1px solid var(--line); border-radius: 18px; text-align: center; font: 500 15px/1.6 var(--ui); box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .notice i { font-size: 36px; color: #f59e0b; }

        /* ---- tablet and phone: the receipt first, the panel underneath */
        @media (max-width: 1180px) {
            .rl { grid-template-columns: minmax(0, 1fr); gap: 20px; padding: 20px 12px 40px; }
            .rp { position: static; width: 100%; max-width: 560px; margin: 0 auto; }
        }
        @media (max-width: 575.98px) {
            .rh-in { padding: 10px 14px; }
            .rh-brand img { height: 34px; }
            .rh-secure { font-size: 12px; padding: 5px 10px; }
            .rp-card { padding: 18px; }
        }

        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; min-height: 0; }
            .rh, .rp { display: none !important; }
            .rl { display: block; padding: 0; margin: 0; max-width: none; }
            .sheet { box-shadow: none; border-radius: 0; zoom: 1 !important; margin: 0; min-height: 0; height: 296mm; overflow: hidden; }
        }
    </style>
</head>
<body>
<header class="rh">
    <div class="rh-in">
        <a class="rh-brand" href="<?= e(url('')) ?>"><img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="<?= e(receipt_text('receipt_org')) ?>"></a>
        <span class="rh-secure"><i class="bi bi-shield-check"></i> Official donation <?= $doc === 'statement' ? 'statement' : 'receipt' ?></span>
    </div>
</header>

<?php if (!$paid): ?>
    <div class="notice">
        <i class="bi bi-hourglass-split"></i><br>
        <strong>The receipt is ready once the payment is confirmed.</strong><br>
        This donation is currently <strong><?= e($d['status']) ?></strong>. If money was taken from your account, it is confirmed automatically within a few minutes.<br><br>
        <a href="<?= e(url('donate/thank-you?ref=' . $d['ref'])) ?>">Back to the donation page</a>
    </div>
<?php else: ?>
    <main class="rl">
        <div class="rl-doc" id="doc">
            <div class="sheet" id="sheet">
                <?php partial($doc === 'statement' ? 'receipt-statement' : 'receipt-receipt', compact('d', 'pay', 'paidAt', 'year', 'receiptNo', 'seq', 'rupees', 'money')); ?>
            </div>
        </div>

        <aside class="rp" aria-label="Receipt actions">
            <div class="rp-card">
                <div class="rp-status">
                    <span class="rp-check"><i class="bi bi-check-lg"></i></span>
                    <div><strong>Payment received</strong><small>Thank you, <?= e($firstName) ?>!</small></div>
                </div>
                <div class="rp-amount">₹<?= e($money($rupees)) ?></div>
                <div class="rp-for">towards <?= e(donation_towards($d)) ?></div>
                <dl class="rp-list">
                    <div><dt>Receipt no.</dt><dd><?= e($receiptNo) ?></dd></div>
                    <div><dt>Date</dt><dd><?= e(date('j M Y', strtotime($paidAt))) ?></dd></div>
                    <div><dt>Paid by</dt><dd><?= e($pay['mode']) ?></dd></div>
                </dl>
                <button type="button" class="rp-btn is-primary" id="dlPdf" data-file="<?= e($fileName) ?>.pdf"><i class="bi bi-download"></i> <span>Download PDF</span></button>
                <button type="button" class="rp-btn" onclick="window.print()"><i class="bi bi-printer"></i> Print <?= $doc === 'statement' ? 'statement' : 'receipt' ?></button>
                <a class="rp-btn is-ghost" href="<?= e(url('')) ?>"><i class="bi bi-arrow-left"></i> Back to website</a>
                <p class="rp-tip"><i class="bi bi-info-circle"></i><span>Saves an A4 PDF straight to your device — keep it for your records.</span></p>
            </div>
            <div class="rp-note">
                <i class="bi bi-patch-check-fill"></i>
                <div><strong>80G tax benefit</strong>Keep this <?= $doc === 'statement' ? 'statement' : 'receipt' ?> for your income-tax filing. Trust PAN <?= e(receipt_text('receipt_pan')) ?>.</div>
            </div>
        </aside>
    </main>
    <script>
        // Fit the A4 sheet on smaller screens (printing always uses the full A4 size).
        (function () {
            var sheet = document.getElementById('sheet'), doc = document.getElementById('doc');
            var full = 794; // 210mm at 96 dpi
            function fit() {
                var scale = Math.min(1, doc.clientWidth / full);
                sheet.style.zoom = scale < 1 ? scale : '';
            }
            window.addEventListener('resize', fit);
            fit();
        })();

        // Download PDF: draws the A4 receipt into a PDF file in the browser and saves it (no print window).
        (function () {
            var btn = document.getElementById('dlPdf'), label = btn.querySelector('span');
            var LIB = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js', loading = null;
            function loadLib() {
                if (window.html2pdf) return Promise.resolve();
                return loading || (loading = new Promise(function (ok, fail) {
                    var s = document.createElement('script');
                    s.src = LIB; s.async = true; s.onload = ok; s.onerror = function () { loading = null; fail(); };
                    document.head.appendChild(s);
                }));
            }
            // Fetch the PDF maker in the background once the page has loaded, so the click is quick.
            window.addEventListener('load', function () { setTimeout(function () { loadLib().catch(function () {}); }, 800); });

            btn.addEventListener('click', function () {
                if (btn.disabled) return;
                btn.disabled = true;
                label.textContent = 'Preparing PDF…';
                var done = function () { btn.disabled = false; label.textContent = 'Download PDF'; };
                var scrollY = window.pageYOffset;
                loadLib().then(function () {
                    // The PDF maker shifts the picture by how far the page is scrolled (on phones the button is below the receipt).
                    window.scrollTo(0, 0);
                    // Work on a full-size copy, so the PDF is the same on a phone as on a computer.
                    var copy = document.getElementById('sheet').cloneNode(true);
                    copy.removeAttribute('id');
                    copy.style.cssText = 'zoom:1;margin:0;box-shadow:none;border-radius:0;width:210mm;height:296mm;min-height:0;overflow:hidden';
                    return html2pdf().set({
                        margin: 0,
                        filename: btn.getAttribute('data-file'),
                        image: { type: 'jpeg', quality: 0.96 },
                        html2canvas: { scale: 2.5, useCORS: true, backgroundColor: '#ffffff', logging: false, scrollX: 0, scrollY: 0 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: true },
                        pagebreak: { mode: 'avoid-all' }
                    }).from(copy).save();
                }).then(function () {
                    window.scrollTo(0, scrollY);
                    label.textContent = 'Downloaded ✓';
                    setTimeout(done, 2200);
                }).catch(function () {
                    window.scrollTo(0, scrollY);
                    done();
                    window.print(); // the PDF maker could not load (no internet / blocked) - the print window can still save a PDF
                });
            });
        })();
    </script>
<?php endif; ?>
</body>
</html>
