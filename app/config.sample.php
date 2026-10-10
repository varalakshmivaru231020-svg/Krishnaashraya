<?php
// Copy this file to app/config.php and fill in your values.
// app/config.php holds secrets - never share it or commit it.

return [
    'app' => [
        'name'     => 'Sri Krishnashraya Educational Trust',
        // Leave empty when the site lives at the domain root (https://www.krishnashraya.in).
        // Set to e.g. '/ashraya' if it lives in a sub-folder.
        'base_url' => '',
        'debug'    => false,   // true shows PHP errors - only on your own computer
        'timezone' => 'Asia/Kolkata',
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'krishnashraya',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // Payment keys are normally entered in Admin -> Payment Settings (Razorpay or Cashfree).
    // These Razorpay values are only a fallback. Razorpay dashboard -> Account & Settings -> API Keys.
    // Use rzp_test_... keys while testing, rzp_live_... keys when going live.
    'razorpay' => [
        'key_id'         => '',
        'key_secret'     => '',
        // Razorpay dashboard -> Webhooks. URL: https://YOUR-DOMAIN/donate/webhook
        // Events: payment.captured, payment.failed, order.paid
        'webhook_secret' => '',
    ],

    'donation' => [
        'min_rupees' => 1,
        'max_rupees' => 1000000,
    ],

    // Optional Google reCAPTCHA v2 ("I'm not a robot") for the contact form.
    // Leave both empty to disable (a hidden honeypot field still blocks simple bots).
    'recaptcha' => [
        'site_key'   => '',
        'secret_key' => '',
    ],

    // Optional: email a notification when a donation is paid or an enquiry arrives.
    // Uses PHP mail(), which works on most cPanel hosts. Leave 'notify_to' empty to disable.
    'mail' => [
        'notify_to' => '',
        'from'      => 'no-reply@krishnashraya.in',
    ],
];
