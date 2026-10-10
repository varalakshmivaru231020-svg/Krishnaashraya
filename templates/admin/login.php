<div class="login-split">
    <div class="login-visual" style="background-image:url('<?= e(url('assets/images/slider/slider-2.webp')) ?>')">
        <div class="login-visual-inner">
            <span class="login-visual-badge"><i class="bi bi-heart-fill"></i> Sri Krishnashraya Educational Trust</span>
            <h2>Nurture. Educate. Empower.</h2>
            <p>Manage donations, enquiries, projects and the photo gallery — all in one place.</p>
        </div>
    </div>
    <div class="login-panel">
        <div class="login-box">
            <img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="Logo" class="login-logo">
            <h1>Welcome back</h1>
            <p class="login-sub">Sign in to the admin console</p>
            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= e($error) ?></span></div>
            <?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <div class="login-field">
                        <i class="bi bi-envelope"></i>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e(input('email')) ?>" autocomplete="username" placeholder="you@example.com" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <div class="login-field">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" placeholder="••••••••" required>
                    </div>
                </div>
                <button class="btn btn-primary btn-lg w-100">Sign in <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="text-center small mt-4 mb-0"><a href="<?= e(url('')) ?>" class="text-muted"><i class="bi bi-arrow-left"></i> Back to website</a></p>
        </div>
    </div>
</div>
