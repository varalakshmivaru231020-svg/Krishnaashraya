<?php
declare(strict_types=1);

// Shrinks an uploaded home-slider video for the web. Runs in the background, started by Admin -> Banners -> slide.
// Usage: php app/tools/video-optimize.php <slide id>

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../bootstrap.php';
set_time_limit(0);

$id = (int) ($argv[1] ?? 0);
$slide = db_one('SELECT * FROM home_slides WHERE id = ?', [$id]);
if (!$slide || $slide['video_status'] !== 'processing' || !$slide['video']) {
    exit;
}
$source = (string) $slide['video'];
$sourceFile = ROOT . '/' . $source;
$playable = (bool) preg_match('/\.(mp4|webm)$/i', $source);

try {
    [$video, $poster] = video_optimize($source);
    $before = filesize($sourceFile);
    $after = filesize(ROOT . '/' . $video);

    // The admin may have replaced or deleted the slide while this was running: then this result is not needed.
    $fresh = db_one('SELECT * FROM home_slides WHERE id = ?', [$id]);
    if (!$fresh || $fresh['video'] !== $source) {
        delete_upload($video);
        delete_upload($poster);
        exit;
    }

    if ($after >= $before && $playable) {
        // Already a light web video: keep the original.
        delete_upload($video);
        $video = $source;
        $note = sprintf('Already web-ready (%.1f MB).', $before / 1024 ** 2);
    } else {
        $note = sprintf('Shrunk from %.1f MB to %.1f MB.', $before / 1024 ** 2, $after / 1024 ** 2);
    }
    $usePoster = $poster && !$fresh['image'];
    db_exec(
        "UPDATE home_slides SET video = ?, image = COALESCE(image, ?), video_status = 'ready', video_note = ? WHERE id = ?",
        [$video, $usePoster ? $poster : null, $note, $id]
    );
    if (!$usePoster) {
        delete_upload($poster);
    }
    if ($video !== $source) {
        delete_upload($source);
    }
} catch (Throwable $ex) {
    error_log('Slider video optimise failed for slide ' . $id . ': ' . $ex->getMessage());
    // A small MP4 / WebM can still be shown as it is; anything else needs a new upload.
    $keep = $playable && is_file($sourceFile) && filesize($sourceFile) <= VIDEO_DIRECT_MAX_MB * 1024 ** 2;
    db_exec(
        'UPDATE home_slides SET video_status = ?, video_note = ? WHERE id = ? AND video = ?',
        [$keep ? 'ready' : 'failed', 'Could not shrink this video' . ($keep ? ' — the original is used.' : '. Please try another file (MP4 or MOV).'), $id, $source]
    );
}
