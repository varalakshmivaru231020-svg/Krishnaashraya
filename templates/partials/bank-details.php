<h3 class="footer-title mb-4">Bank Details</h3>
<table class="bank-details-table">
    <?php foreach ([
        'Bank Name'         => setting('bank_name'),
        'Account Name'      => setting('bank_account_name'),
        'Account No'        => setting('bank_account_no'),
        'IFSC Code'         => setting('bank_ifsc'),
        'Branch'            => setting('bank_branch'),
        'UPI / Paytm / GPay' => setting('upi'),
    ] as $label => $value): if ($value === '') continue; ?>
        <tr>
            <td><?= e($label) ?></td>
            <td><?= e($value) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (setting('tax_note')): ?>
    <div class="highlight-box">
        <p><strong>Tax Benefit:</strong> <?= e(setting('tax_note')) ?></p>
    </div>
<?php endif; ?>
<div class="mt-4">
    <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient w-100 text-center js-donate">Donate Online Now</a>
</div>
