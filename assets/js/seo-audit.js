// Admin -> SEO: edit window with live Google preview, and the SEO audit (runs in the browser, page by page).
(function () {
    'use strict';

    // ------------------------------------------------ edit window
    var modal = document.getElementById('seoModal');
    if (modal) {
        var f = function (n) { return modal.querySelector('[name="' + n + '"]'); };
        var auto = { title: '', description: '' };
        var refresh = function () {
            var t = f('title').value.trim() || auto.title;
            var d = f('description').value.trim() || auto.description;
            modal.querySelector('[data-p-title]').textContent = t;
            modal.querySelector('[data-p-desc]').textContent = d;
            modal.querySelectorAll('.len-count').forEach(function (c) {
                var input = document.getElementById(c.getAttribute('data-count-for'));
                var n = (input.value.trim() || (input.name === 'title' ? auto.title : auto.description)).length;
                var min = +c.getAttribute('data-min'), max = +c.getAttribute('data-max');
                c.textContent = n + ' characters' + (input.value.trim() ? '' : ' (automatic)') + ' · best ' + min + '–' + max;
                c.className = 'len-count ' + (n === 0 ? 'is-bad' : (n < min || n > max ? 'is-warn' : 'is-good'));
            });
        };
        modal.addEventListener('show.bs.modal', function (e) {
            var b = e.relatedTarget;
            auto.title = b.getAttribute('data-auto-title');
            auto.description = b.getAttribute('data-auto-description');
            modal.querySelector('.modal-title').textContent = 'Search settings — ' + b.getAttribute('data-label');
            f('key').value = b.getAttribute('data-key');
            f('title').value = b.getAttribute('data-title');
            f('description').value = b.getAttribute('data-description');
            f('title').placeholder = auto.title;
            f('description').placeholder = auto.description;
            modal.querySelector('[data-p-url]').textContent = b.getAttribute('data-url');
            refresh();
        });
        modal.addEventListener('input', refresh);
    }

    // ------------------------------------------------ audit
    var run = document.getElementById('runAudit');
    if (!run) return;
    var pages = window.SEO_PAGES || [];
    var base = window.SEO_BASE || '';
    var isLocal = /^(localhost|127\.0\.0\.1|\[::1\])$/.test(location.hostname) || /\.(local|test)$/.test(location.hostname);

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function check(level, label, detail) { return { level: level, label: label, detail: detail || '' }; } // level: good | warn | bad | info
    var WEIGHT = { good: 1, warn: 0.5, bad: 0, info: null };

    function auditPage(page, html, status) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var out = [];
        if (status !== 200) out.push(check('bad', 'Page does not load', 'The server answered ' + status + '.'));

        var title = (doc.querySelector('title') || {}).textContent || '';
        title = title.trim();
        if (!title) out.push(check('bad', 'Missing page title'));
        else if (title.length > 60) out.push(check('warn', 'Title is long (' + title.length + ' characters)', 'Google shows about 60 characters. Shorten it in the Pages tab.'));
        else if (title.length < 30) out.push(check('warn', 'Title is short (' + title.length + ' characters)', 'Add a few descriptive words, e.g. the city or what the page offers.'));
        else out.push(check('good', 'Title length is good (' + title.length + ')'));

        var desc = ((doc.querySelector('meta[name="description"]') || {}).content || '').trim();
        if (!desc) out.push(check('bad', 'Missing meta description'));
        else if (desc.length > 160) out.push(check('warn', 'Description is long (' + desc.length + ')', 'Google cuts it after about 160 characters.'));
        else if (desc.length < 70) out.push(check('warn', 'Description is short (' + desc.length + ')', 'Write one or two full sentences (70–160 characters).'));
        else out.push(check('good', 'Description length is good (' + desc.length + ')'));

        var h1 = doc.querySelectorAll('h1');
        if (h1.length === 1) out.push(check('good', 'One main heading (H1)'));
        else if (h1.length === 0) out.push(check('bad', 'No main heading (H1)'));
        else out.push(check('warn', h1.length + ' main headings (H1)', 'A page should have exactly one H1.'));

        var h2 = doc.querySelectorAll('main h2, section h2, h2').length;
        out.push(h2 ? check('good', h2 + ' section headings (H2)') : check('warn', 'No section headings (H2)', 'Headings help Google understand the page.'));

        var imgs = Array.prototype.slice.call(doc.querySelectorAll('img'));
        var noAlt = imgs.filter(function (i) { return !i.hasAttribute('alt'); });
        if (noAlt.length) out.push(check('bad', noAlt.length + ' image(s) without alternative text', 'Screen readers and Google cannot understand these images.'));
        else out.push(check('good', 'All ' + imgs.length + ' images have alternative text'));

        out.push(doc.querySelector('link[rel="canonical"]') ? check('good', 'Canonical address set') : check('warn', 'No canonical address'));
        var og = doc.querySelector('meta[property="og:title"]') && doc.querySelector('meta[property="og:image"]');
        out.push(og ? check('good', 'Sharing preview for WhatsApp / Facebook') : check('warn', 'No sharing preview tags'));
        out.push(doc.documentElement.getAttribute('lang') ? check('good', 'Language set (' + doc.documentElement.getAttribute('lang') + ')') : check('warn', 'Page language not set'));
        out.push(doc.querySelector('meta[name="viewport"]') ? check('good', 'Mobile-friendly viewport') : check('bad', 'Not set up for mobile'));
        if (doc.querySelector('meta[name="robots"][content*="noindex"]')) out.push(check('bad', 'Hidden from Google (noindex)'));

        // Visible words, excluding menu and footer
        doc.querySelectorAll('nav, footer, script, style, .modal').forEach(function (n) { n.remove(); });
        var words = (doc.body ? doc.body.textContent : '').trim().split(/\s+/).filter(Boolean).length;
        if (words < 150) out.push(check('warn', 'Little text on the page (' + words + ' words)', 'Pages with at least 150–300 words rank better. Add a description or story.'));
        else out.push(check('good', words + ' words of content'));

        return { title: title, desc: desc, checks: out };
    }

    function score(checks) {
        var total = 0, got = 0;
        checks.forEach(function (c) { if (WEIGHT[c.level] !== null) { total++; got += WEIGHT[c.level]; } });
        return total ? Math.round(got * 100 / total) : 100;
    }

    function icon(level) {
        return { good: 'check-circle-fill', warn: 'exclamation-triangle-fill', bad: 'x-circle-fill', info: 'info-circle-fill' }[level];
    }

    function renderChecks(checks) {
        return checks.map(function (c) {
            return '<li class="is-' + c.level + '"><i class="bi bi-' + icon(c.level) + '"></i><div><strong>' + esc(c.label) + '</strong>' +
                (c.detail ? '<span>' + esc(c.detail) + '</span>' : '') + '</div></li>';
        }).join('');
    }

    function setScore(n) {
        var box = document.getElementById('auditScore');
        box.querySelector('span').textContent = n;
        box.className = 'audit-score ' + (n >= 85 ? 'is-good' : n >= 60 ? 'is-warn' : 'is-bad');
        box.querySelector('.arc').style.strokeDashoffset = String(327 - 327 * n / 100);
    }

    function get(path) {
        return fetch(base + '/' + path, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.text().then(function (t) { return { status: r.status, text: t }; }); })
            .catch(function () { return { status: 0, text: '' }; });
    }

    run.addEventListener('click', function () {
        run.disabled = true;
        var statusEl = document.getElementById('auditStatus');
        var results = [];
        var i = 0;

        function next() {
            if (i >= pages.length) return site();
            var page = pages[i++];
            statusEl.textContent = 'Checking ' + i + ' of ' + pages.length + ': ' + page.label + '…';
            get(page.path).then(function (r) {
                var a = auditPage(page, r.text, r.status);
                a.page = page;
                results.push(a);
                next();
            });
        }

        function site() {
            statusEl.textContent = 'Checking sitemap and robots.txt…';
            Promise.all([get('sitemap.xml'), get('robots.txt')]).then(function (res) {
                var siteChecks = [];
                var sm = res[0], rb = res[1];
                var urls = (sm.text.match(/<loc>/g) || []).length;
                siteChecks.push(sm.status === 200 && urls ? check('good', 'Sitemap lists ' + urls + ' pages', 'Submit sitemap.xml in Google Search Console after going live.') : check('bad', 'Sitemap is not available'));
                if (isLocal) siteChecks.push(check('info', 'robots.txt blocks Google on this local copy (on purpose)', 'On the live site it allows Google and points to the sitemap.'));
                else siteChecks.push(/Sitemap:/i.test(rb.text) && !/Disallow:\s*\/\s*$/m.test(rb.text) ? check('good', 'robots.txt allows Google and points to the sitemap') : check('bad', 'robots.txt blocks Google or has no sitemap line'));
                siteChecks.push(location.protocol === 'https:' ? check('good', 'Secure connection (https)') : (isLocal ? check('info', 'Not https on this local copy', 'Turn on SSL (https) on the live hosting.') : check('bad', 'Site is not on https')));
                siteChecks.push(document.getElementById('ga_measurement_id').value.trim() ? check('good', 'Google Analytics connected') : check('warn', 'Google Analytics not connected', 'Add it in the Google tab.'));
                siteChecks.push(document.getElementById('gsc_verification').value.trim() ? check('good', 'Google Search Console verification added') : check('warn', 'Google Search Console not verified', 'Add the code in the Google tab, then submit the sitemap.'));

                // Duplicate titles / descriptions across pages
                var seenT = {}, seenD = {};
                results.forEach(function (r) {
                    if (r.title) (seenT[r.title] = seenT[r.title] || []).push(r);
                    if (r.desc) (seenD[r.desc] = seenD[r.desc] || []).push(r);
                });
                Object.keys(seenT).forEach(function (t) { if (seenT[t].length > 1) seenT[t].forEach(function (r) { r.checks.push(check('warn', 'Same title as ' + (seenT[t].length - 1) + ' other page(s)', 'Every page should have its own title.')); }); });
                Object.keys(seenD).forEach(function (d) { if (seenD[d].length > 1) seenD[d].forEach(function (r) { r.checks.push(check('warn', 'Same description as ' + (seenD[d].length - 1) + ' other page(s)')); }); });

                var pageScores = results.map(function (r) { return score(r.checks); });
                var overall = Math.round((pageScores.reduce(function (a, b) { return a + b; }, 0) / Math.max(1, pageScores.length)) * 0.8 + score(siteChecks) * 0.2);
                setScore(overall);

                document.getElementById('siteChecks').innerHTML = renderChecks(siteChecks);
                document.getElementById('pageChecks').innerHTML = results.map(function (r, n) {
                    var s = pageScores[n];
                    var issues = r.checks.filter(function (c) { return c.level === 'warn' || c.level === 'bad'; }).length;
                    // Show the result next to the page in the Pages tab too
                    var chip = document.querySelector('[data-audit-for="' + r.page.key + '"]');
                    if (chip) { chip.hidden = false; chip.className = 'seo-audit-chip is-' + (s >= 85 ? 'good' : s >= 60 ? 'warn' : 'bad'); chip.textContent = 'Audit ' + s; }
                    return '<details class="audit-page"><summary><span class="audit-page-score is-' + (s >= 85 ? 'good' : s >= 60 ? 'warn' : 'bad') + '">' + s + '</span>' +
                        '<span class="audit-page-name">' + esc(r.page.label) + '<small>/' + esc(r.page.path) + '</small></span>' +
                        '<span class="audit-page-issues">' + (issues ? issues + ' to improve' : 'All good') + '</span></summary>' +
                        '<ul class="audit-list">' + renderChecks(r.checks) + '</ul></details>';
                }).join('');
                document.getElementById('auditResults').hidden = false;
                statusEl.textContent = 'Done — ' + results.length + ' pages checked.';
                run.disabled = false;
                run.innerHTML = '<i class="bi bi-arrow-repeat"></i> Run again';
            });
        }

        next();
    });
})();
