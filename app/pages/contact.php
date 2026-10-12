<?php
declare(strict_types=1);

if (is_post()) {
    csrf_check();

    // Return to the page the form was sent from (home or contact), but only on this site.
    $back = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $sameSite = parse_url($back, PHP_URL_HOST) === parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
    $backPath = $sameSite ? (parse_url($back, PHP_URL_PATH) ?: '/') : url('contact');
    $backUrl = $backPath . '#contact';

    // Honeypot: a hidden field only spam bots fill in. Pretend success so the bot learns nothing.
    // (Its name must stay one that browser autofill ignores — see templates/partials/contact-form.php.)
    if (input('ks_hp_trap') !== '') {
        flash('success', "Thank you! Your message has been received. We'll get back to you shortly.");
        redirect($backUrl);
    }

    $data = [
        'name'    => mb_substr(input('name'), 0, 120),
        'email'   => mb_substr(input('email'), 0, 190),
        'phone'   => input('phone'),
        'type'    => array_key_exists(input('type'), enquiry_types()) ? input('type') : 'general',
        'message' => mb_substr(input('message'), 0, 3000),
    ];

    $errors = [];
    if (mb_strlen($data['name']) < 2) {
        $errors[] = 'Please enter your name.';
    }
    if (!valid_email($data['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    $phone = normalize_phone($data['phone']);
    if ($phone === null) {
        $errors[] = 'Please enter a valid 10-digit mobile number.';
    }
    if (mb_strlen($data['message']) < 5) {
        $errors[] = 'Please write a short message.';
    }
    if (!$errors && !recaptcha_passes()) {
        $errors[] = recaptcha_type() === 'v2'
            ? 'Please tick the "I\'m not a robot" box.'
            : "We couldn't confirm you're not a robot. Please try again, or call us instead.";
    }
    if (!$errors && rate_limited('contact', 5, 600)) {
        $errors[] = 'You have sent several messages already. Please wait a few minutes and try again.';
    }

    if ($errors) {
        $_SESSION['contact_old'] = $data;
        $_SESSION['contact_errors'] = $errors;
        redirect($backUrl);
    }

    db_exec(
        'INSERT INTO enquiries (type, name, email, phone, message, ip) VALUES (?, ?, ?, ?, ?, ?)',
        [$data['type'], $data['name'], $data['email'], $phone, $data['message'], client_ip()]
    );
    notify_admin(
        'New enquiry (' . enquiry_types()[$data['type']] . ') from ' . $data['name'],
        "Name: {$data['name']}\nEmail: {$data['email']}\nPhone: {$phone}\n\n{$data['message']}"
    );

    flash('success', 'Thank you, ' . $data['name'] . "! Your message has been received. We'll get back to you shortly.");
    redirect($backUrl);
}

render('contact', [
    'title'       => 'Contact Us',
    'description' => 'Contact Sri Krishnashraya Educational Trust, Bangalore - visit us, volunteer, sponsor a child or ask about donating.',
]);
