<?php
declare(strict_types=1);

// Admin -> Banners -> add or edit one home page slide (a photo, or a short video with an optional cover photo).

$id = (int) input('id');
$slide = $id ? db_one('SELECT * FROM home_slides WHERE id = ?', [$id]) : null;
if ($id && !$slide) {
    flash('error', 'That slide was not found.');
    redirect('admin/banners');
}

$errors = [];
$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
$form = $slide ?? ['media_type' => 'image', 'image' => '', 'video' => '', 'title' => "There Is No Joy\nGreater than the Joy\nof Giving", 'subtitle' => 'Empowering Lives Through Education and Care', 'is_active' => 1];

if (is_post()) {
    csrf_check();
    $form['media_type'] = input('media_type') === 'video' ? 'video' : 'image';
    $form['title'] = mb_substr(str_replace("\r\n", "\n", trim((string) ($_POST['title'] ?? ''))), 0, 255);
    $form['subtitle'] = mb_substr(input('subtitle'), 0, 255);
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $isVideo = $form['media_type'] === 'video';

    // A request bigger than post_max_size arrives with $_POST and $_FILES empty.
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $tooBig = 'The file is too large. The limit is ' . round(video_upload_limit() / 1024 ** 2) . ' MB — please compress the video and try again.';
        if ($ajax) {
            json_response(['ok' => false, 'errors' => [$tooBig]]);
        }
        flash('error', $tooBig);
        redirect('admin/banners/slide' . ($id ? '?id=' . $id : ''));
    }

    $newImage = $newVideo = null;
    if (!empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'banners', 1920);
        } catch (RuntimeException $ex) {
            $errors[] = ($isVideo ? 'Cover photo: ' : 'Photo: ') . $ex->getMessage();
        }
    }
    if ($isVideo && !empty($_FILES['video']['name'])) {
        try {
            $newVideo = store_video_upload($_FILES['video'], 'banners');
        } catch (RuntimeException $ex) {
            $errors[] = 'Video: ' . $ex->getMessage();
        }
    }

    $removeCover = $isVideo && isset($_POST['remove_cover']) && !$newImage;
    $image = $newImage ?? ($removeCover ? null : ($slide['image'] ?? null));
    $video = $isVideo ? ($newVideo ?? ($slide['video'] ?? null)) : null;

    if (!$errors) {
        if (!$isVideo && !$image) {
            $errors[] = 'Choose a photo for this slide.';
        } elseif ($isVideo && !$video) {
            $errors[] = 'Choose a video for this slide (MP4 or WebM).';
        }
    }

    // A newly uploaded video is shrunk on the server first (when it can), so visitors never download the big original.
    $optimize = $isVideo && $newVideo && video_can_optimize();
    $status = $optimize ? 'processing' : ($isVideo && !$newVideo ? ($slide['video_status'] ?? 'ready') : 'ready');
    $note = $optimize ? null : ($isVideo && !$newVideo ? ($slide['video_note'] ?? null) : null);

    if ($errors) {
        // Don't keep half-saved files from a failed save.
        delete_upload($newImage);
        delete_upload($newVideo);
        if ($ajax) {
            json_response(['ok' => false, 'errors' => $errors]);
        }
    } else {
        if ($slide) {
            db_exec(
                'UPDATE home_slides SET media_type = ?, image = ?, video = ?, video_status = ?, video_note = ?, video_queued_at = ?,
                        title = ?, subtitle = ?, is_active = ? WHERE id = ?',
                [$form['media_type'], $image, $video, $status, $note, $optimize ? date('Y-m-d H:i:s') : ($slide['video_queued_at'] ?? null),
                 $form['title'] ?: null, $form['subtitle'] ?: null, $form['is_active'], $id]
            );
            if ($slide['image'] && $slide['image'] !== $image) {
                delete_upload($slide['image']);
            }
            if ($slide['video'] && $slide['video'] !== $video) {
                delete_upload($slide['video']);
            }
            $slideId = $id;
            flash('success', $optimize ? 'Slide saved. The video is being made web-ready — this takes a minute or two.' : 'Slide saved.');
        } else {
            $order = (int) db_value('SELECT COALESCE(MAX(sort_order), 0) FROM home_slides') + 10;
            db_exec(
                'INSERT INTO home_slides (media_type, image, video, video_status, video_queued_at, title, subtitle, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$form['media_type'], $image, $video, $status, $optimize ? date('Y-m-d H:i:s') : null, $form['title'] ?: null, $form['subtitle'] ?: null, $order, $form['is_active']]
            );
            $slideId = (int) db()->lastInsertId();
            flash('success', $optimize ? 'Slide added. The video is being made web-ready — this takes a minute or two.' : 'Slide added to the home page slider.');
        }
        if ($optimize) {
            video_optimize_in_background($slideId);
        }
        if ($ajax) {
            json_response(['ok' => true, 'redirect' => url('admin/banners')]);
        }
        redirect('admin/banners');
    }
}

admin_render('banner-slide', [
    'title'  => $slide ? 'Edit slide' : 'Add slide',
    'slide'  => $slide,
    'form'   => $form,
    'errors' => $errors,
]);
