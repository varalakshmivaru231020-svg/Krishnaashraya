<?php /** Live preview for the banner edit screens. Optional @var array $defaults (used when a box is left empty). */ ?>
<script>
    (function () {
        var box = document.getElementById('prevBox');
        var title = document.getElementById('prevTitle');
        var sub = document.getElementById('prevSub');
        var empty = document.getElementById('prevEmpty');
        var fallback = <?= json_encode($defaults ?? ['title' => '', 'subtitle' => ''], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
        var photo = document.getElementById('image');
        var reset = document.getElementById('reset_image');
        var current = box.style.backgroundImage;

        function text(el) { return el.value.trim(); }
        document.getElementById('title').addEventListener('input', function (e) {
            title.textContent = '';
            (text(e.target) || fallback.title).split('\n').forEach(function (line, i) {
                if (i) title.appendChild(document.createElement('br'));
                title.appendChild(document.createTextNode(line));
            });
        });
        document.getElementById('subtitle').addEventListener('input', function (e) {
            sub.textContent = text(e.target) || fallback.subtitle;
        });
        photo.addEventListener('change', function () {
            if (!photo.files[0]) { box.style.backgroundImage = current; return; }
            box.style.backgroundImage = 'url(' + URL.createObjectURL(photo.files[0]) + ')';
            if (empty) empty.hidden = true;
            if (reset) reset.checked = false;
        });
        if (reset) reset.addEventListener('change', function () {
            box.style.backgroundImage = reset.checked ? 'url(' + reset.getAttribute('data-original') + ')' : current;
        });
    })();
</script>
