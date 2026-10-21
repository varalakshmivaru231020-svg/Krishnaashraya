<?php
declare(strict_types=1);

// Every editable site-wide text, grouped for the form. [key => [label, type, help]]
$groups = [
    'Contact details' => [
        'phone_1'            => ['Phone 1', 'text', ''],
        'phone_2'            => ['Phone 2', 'text', ''],
        'email_1'            => ['Email 1', 'email', 'Main contact email, also shown on the thank-you page.'],
        'email_2'            => ['Email 2', 'email', ''],
        'whatsapp_number'    => ['WhatsApp number', 'text', 'With country code, digits only, e.g. 919364077341. Leave empty to hide.'],
        'address_registered' => ['Registered office address', 'textarea', ''],
        'address_center'     => ['Hoskote center address', 'textarea', ''],
        'visiting_hours'     => ['Visiting hours', 'textarea', ''],
        'maps_url'           => ['Google Maps link', 'url', 'The "Share" link from Google Maps.'],
        'maps_embed'         => ['Google Maps embed URL (optional)', 'url', 'Leave empty to show the Hoskote center address on the Contact page map. For an exact pin: Google Maps → Share → Embed a map → copy only the src="…" address.'],
    ],
    'Bank & donation details' => [
        'bank_name'         => ['Bank name', 'text', ''],
        'bank_account_name' => ['Account name', 'text', ''],
        'bank_account_no'   => ['Account number', 'text', ''],
        'bank_ifsc'         => ['IFSC code', 'text', ''],
        'bank_branch'       => ['Branch', 'text', ''],
        'upi'               => ['UPI / Paytm / GPay', 'text', ''],
        'tax_note'          => ['Tax benefit note', 'textarea', ''],
        'donate_presets'    => ['Suggested amounts (₹)', 'text', 'Comma-separated, e.g. 1000,2000,5000,10000. The first one is selected by default. Donors can also enter a custom amount.'],
        'donate_min'        => ['Minimum donation (₹)', 'number', 'Smallest custom amount a donor can give. Leave as 1 for no real minimum.'],
    ],
    'Donation receipt' => [
        'receipt_org'              => ['Trust name', 'text', ''],
        'receipt_tagline'          => ['Line under the name', 'text', 'e.g. (Registered Charitable Trust)'],
        'receipt_address'          => ['Address on the receipt', 'textarea', 'Shown in the orange bar and in the registration details.'],
        'receipt_pan'              => ['Trust PAN', 'text', ''],
        'receipt_12aa'             => ['12AA registration no.', 'text', ''],
        'receipt_80g'              => ['80G certification no.', 'text', ''],
        'receipt_email'            => ['Support email on the receipt', 'email', ''],
        'receipt_closing'          => ['Receipt closing line', 'text', 'The red line near the bottom of the receipt, e.g. Thanks for your support!'],
        'receipt_thanks'           => ['Statement thank-you message', 'textarea', 'Printed on the Detailed statement.'],
        'receipt_prefix'           => ['Receipt number start', 'text', 'Website receipts are numbered START/YEAR/00001, e.g. SKET/WEB/2026/00001. Keep it different from your office receipt book so numbers never clash.'],
        'receipt_statement_prefix' => ['Statement number start', 'text', 'e.g. SKET/DCS gives SKET/DCS/2026/W00001.'],
        'receipt_donor_prefix'     => ['Donor ID start', 'text', 'e.g. DIDW gives DIDW00012. The same donor (same phone or email) keeps the same ID.'],
    ],
    'Footer & donation figures' => [
        'stat_cost_per_child' => ['Cost per child / year (₹)', 'number', 'Used on the Donate page to show how long a gift lasts ("covers about 6 days of care"). The home page figures are under Impact Numbers.'],
        'footer_about'  => ['Footer text', 'textarea', ''],
    ],
    'Social media links' => [
        'social_facebook'  => ['Facebook', 'url', ''],
        'social_instagram' => ['Instagram', 'url', ''],
        'social_youtube'   => ['YouTube', 'url', ''],
        'social_twitter'   => ['X (Twitter)', 'url', ''],
        'social_linkedin'  => ['LinkedIn', 'url', ''],
    ],
];

$errors = [];

if (is_post() && input('action') === 'font') {
    csrf_check();
    if (array_key_exists(input('font_pair'), font_pairs())) {
        setting_save('font_pair', input('font_pair'));
        flash('success', 'Website fonts changed to "' . font_pairs()[input('font_pair')]['label'] . '". Press Ctrl + F5 on the website to see them.');
    }
    redirect('admin/settings#fonts');
}

if (is_post() && input('action') === 'site') {
    csrf_check();
    $values = [];
    foreach ($groups as $fields) {
        foreach ($fields as $key => [$label, $type]) {
            $v = trim((string) ($_POST[$key] ?? ''));
            if ($v !== '' && $type === 'email' && !valid_email($v)) {
                $errors[] = "$label is not a valid email address.";
            }
            if ($v !== '' && $type === 'url' && !preg_match('#^https://#i', $v)) {
                $errors[] = "$label must start with https://";
            }
            if ($key === 'maps_embed' && $v !== '' && !preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#i', $v)) {
                $errors[] = 'The map embed address must start with https://www.google.com/maps/embed';
            }
            if ($type === 'secret' && $v === '') {
                continue; // empty secret box = keep the saved value
            }
            $values[$key] = $v;
        }
    }
    if (!$errors) {
        foreach ($values as $k => $v) {
            setting_save($k, $v);
        }
        flash('success', 'Settings saved. The website is updated.');
        redirect('admin/settings');
    }
}

admin_render('settings', [
    'title'  => 'Site Settings',
    'groups' => $groups,
    'values' => array_merge(settings_all(), is_post() && input('action') === 'site' ? $_POST : []),
    'errors' => $errors,
]);
