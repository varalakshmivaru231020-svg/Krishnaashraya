<?php /** @var array $errors */ if (!$errors) return; ?>
<div class="alert alert-danger">
    <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
</div>
