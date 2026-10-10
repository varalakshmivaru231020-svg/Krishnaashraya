<?php /** @var array $admin @var array $admins @var array $errors */ ?>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Change my password</div>
            <div class="card-body">
                <p class="small text-muted">Logged in as <strong><?= e($admin['email']) ?></strong></p>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="password">
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">New password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Repeat new password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <button class="btn btn-primary">Change password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">Admin users</div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Name</th><th>Email</th><th>Last login</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?= e($a['name']) ?></td>
                            <td><?= e($a['email']) ?></td>
                            <td class="small text-muted"><?= e(fmt_date($a['last_login_at'], 'd M Y')) ?></td>
                            <td class="text-end">
                                <?php if ((int) $a['id'] !== (int) $admin['id']): ?>
                                    <form method="post" data-confirm="Remove <?= e($a['email']) ?>'s admin access?">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="remove_admin"><input type="hidden" name="admin_id" value="<?= (int) $a['id'] ?>">
                                        <button class="btn btn-sm btn-link text-danger">Remove</button>
                                    </form>
                                <?php else: ?>
                                    <span class="small text-muted">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Add another admin</div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_admin">
                    <div class="col-md-4"><input class="form-control" name="name" placeholder="Name" required></div>
                    <div class="col-md-4"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
                    <div class="col-md-4"><input type="password" class="form-control" name="password" placeholder="Password (8+ chars)" minlength="8" autocomplete="new-password" required></div>
                    <div class="col-12"><button class="btn btn-outline-primary">Add admin</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
