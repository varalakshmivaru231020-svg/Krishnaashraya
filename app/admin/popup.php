<?php
declare(strict_types=1);

// Admin -> Website Popup: an announcement box with a photo that opens over the website (an appeal, an event, a flyer).

$errors = [];
$p = popup();

if (is_post()) {
    csrf_check();
    $date = function (string $v): string {
        $v = trim($v);
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $v : '';
    };
    $form = [
        'on'        => isset($_POST['on']) ? '1' : '0',
        'layout'    => input('layout') === 'poster' ? 'poster' : 'card',
        'title'     => mb_substr(input('title'), 0, 120),
        'text'      => mb_substr(trim(str_replace("\r\n", "\n", (string) ($_POST['text'] ?? ''))), 0, 500),
        'button'    => mb_substr(input('button'), 0, 40),
        'link'      => mb_substr(input('link'), 0, 255),
        'pages'     => input('pages') === 'all' ? 'all' : 'home',
        'frequency' => in_array(input('frequency'), ['session', 'day', 'always'], true) ? input('frequency') : 'session',
        'delay'     => (string) max(0, min(30, (int) input('delay'))),
        'start'     => $date(input('start')),
        'end'       => $date(input('end')),
    ];

    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'popup', 1200);
        } catch (RuntimeException $ex) {
            $errors[] = 'Photo: ' . $ex->getMessage();
        }
    }
    $image = $newImage ?? (isset($_POST['remove_image']) ? '' : $p['image']);

    if ($form['link'] !== '' && popup_link($form['link']) === '') {
        $errors[] = 'Button link: type a page of this website (like donate or contact) or a full address starting with https://';
    }
    if ($form['start'] !== '' && $form['end'] !== '' && $form['end'] < $form['start']) {
        $errors[] = 'The "Show until" date is before the "Show from" date.';
    }
    if ($form['layout'] === 'poster' && $image === '') {
        $errors[] = 'A poster popup needs a photo. Choose one, or switch to "Photo with text".';
    } elseif ($form['layout'] === 'card' && $image === '' && $form['title'] === '' && $form['text'] === '') {
        $errors[] = 'Add a photo, a heading or a message — the popup would be empty.';
    }

    if ($errors) {
        delete_upload($newImage);
        $p = ['image' => $p['image']] + $form;
    } else {
        foreach ($form as $k => $v) {
            setting_save("popup_$k", $v);
        }
        setting_save('popup_image', $image);
        if ($image !== $p['image']) {
            delete_upload($p['image']);
        }
        $p = $form + ['image' => $image];
        flash('success', $form['on'] === '1' ? 'Popup saved. ' . popup_status($p)[1] . '.' : 'Popup saved. It is turned off, so visitors don\'t see it.');
        redirect('admin/popup');
    }
}

admin_render('popup', [
    'title'  => 'Website Popup',
    'p'      => $p,
    'status' => popup_status($p),
    'errors' => $errors,
]);
