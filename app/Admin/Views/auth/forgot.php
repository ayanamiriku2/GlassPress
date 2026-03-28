<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Forgot Password - <?= htmlspecialchars($siteName) ?></title>
    <?php $favicon = $app->getService('settings')->get('site_favicon', ''); if ($favicon):
        $favExt = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
        $favType = match($favExt) { 'png' => 'image/png', 'svg' => 'image/svg+xml', 'gif' => 'image/gif', 'jpg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', default => 'image/x-icon' };
    ?>
    <link rel="icon" type="<?= $favType ?>" href="<?= htmlspecialchars($favicon) ?>">
    <?php endif; ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --glass-bg: rgba(255,255,255,0.1); --glass-border: rgba(255,255,255,0.18); --primary: #6366f1; --text: #f1f5f9; --text-muted: #94a3b8; --input-bg: rgba(255,255,255,0.08); --input-border: rgba(255,255,255,0.15); }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #0f172a, #1e1b4b, #1e293b, #0f172a); min-height: 100vh; display: flex; justify-content: center; align-items: center; color: var(--text); padding: 1rem; }
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(circle at 30% 20%, rgba(99,102,241,0.15) 0%, transparent 50%), radial-gradient(circle at 70% 80%, rgba(139,92,246,0.1) 0%, transparent 50%); pointer-events:none; }
        .box { position:relative; width:100%; max-width:420px; background:var(--glass-bg); backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:16px; padding:2.5rem 2rem; box-shadow:0 8px 32px rgba(0,0,0,0.3); }
        .logo { text-align:center; margin-bottom:2rem; }
        .logo h1 { font-size:1.75rem; background:linear-gradient(135deg,#818cf8,#c084fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .logo p { color:var(--text-muted); font-size:0.85rem; margin-top:0.25rem; }
        .alert { padding:0.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:0.85rem; }
        .alert-error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; }
        .alert-info { background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; }
        .form-group { margin-bottom:1.25rem; }
        label { display:block; font-size:0.85rem; font-weight:500; color:var(--text-muted); margin-bottom:0.35rem; }
        input[type="email"] { width:100%; padding:0.7rem 0.85rem; background:var(--input-bg); border:1px solid var(--input-border); border-radius:8px; color:var(--text); font-size:0.9rem; outline:none; transition:border-color 0.2s; }
        input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,0.3); }
        input::placeholder { color:rgba(255,255,255,0.3); }
        .btn { width:100%; padding:0.8rem; background:linear-gradient(135deg,var(--primary),#8b5cf6); color:white; border:none; border-radius:10px; font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.3s; }
        .btn:hover { transform:translateY(-1px); box-shadow:0 4px 20px rgba(99,102,241,0.4); }
        .back-link { text-align:center; margin-top:1.5rem; }
        .back-link a { color:var(--text-muted); text-decoration:none; font-size:0.85rem; }
        .back-link a:hover { color:var(--text); }
    </style>
</head>
<body>
    <div class="box">
        <div class="logo">
            <h1>&#9670; GlassPress</h1>
            <p>Password Recovery</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $app->getAdminUrl('forgot-password') ?>">
            <?= $csrf_field ?>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="admin@example.com" required autofocus>
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>

        <div class="back-link">
            <a href="<?= $app->getAdminUrl('login') ?>">&larr; Back to login</a>
        </div>
    </div>
</body>
</html>
