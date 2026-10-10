<?php /** @var array $pg */ if ($pg['pages'] <= 1) return; ?>
<nav class="mt-3" aria-label="Pages">
    <ul class="pagination justify-content-center flex-wrap">
        <li class="page-item<?= $pg['page'] <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= e(qs(['page' => $pg['page'] - 1])) ?>">‹</a></li>
        <?php for ($i = max(1, $pg['page'] - 3); $i <= min($pg['pages'], $pg['page'] + 3); $i++): ?>
            <li class="page-item<?= $i === $pg['page'] ? ' active' : '' ?>"><a class="page-link" href="<?= e(qs(['page' => $i])) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item<?= $pg['page'] >= $pg['pages'] ? ' disabled' : '' ?>"><a class="page-link" href="<?= e(qs(['page' => $pg['page'] + 1])) ?>">›</a></li>
    </ul>
</nav>
