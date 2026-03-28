<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - <?= htmlspecialchars($siteName) ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --glass-bg: rgba(255,255,255,0.1);
            --glass-border: rgba(255,255,255,0.18);
            --primary: #6366f1;
            --danger: #ef4444;
            --success: #22c55e;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --input-bg: rgba(255,255,255,0.08);
            --input-border: rgba(255,255,255,0.15);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 30%, #1e293b 60%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text);
            padding: 1rem;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 30% 20%, rgba(99,102,241,0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(139,92,246,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .login-box {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo h1 {
            font-size: 1.75rem;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .login-logo p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem; }
        .alert-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--text-muted); margin-bottom: 0.35rem; }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%; padding: 0.7rem 0.85rem;
            background: var(--input-bg); border: 1px solid var(--input-border);
            border-radius: 8px; color: var(--text); font-size: 0.9rem;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.3); }
        input::placeholder { color: rgba(255,255,255,0.3); }
        .remember-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .remember-row label { display: flex; align-items: center; gap: 0.4rem; cursor: pointer; margin: 0; }
        .remember-row input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary); }
        .remember-row a { color: var(--primary); text-decoration: none; font-size: 0.85rem; }
        .remember-row a:hover { text-decoration: underline; }
        .btn-login {
            width: 100%; padding: 0.8rem;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: white; border: none; border-radius: 10px;
            font-size: 0.95rem; font-weight: 600; cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(99,102,241,0.4); }
        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; }
        .back-link a:hover { color: var(--text); }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <h1>&#9670; GlassPress</h1>
            <p><?= htmlspecialchars($siteName) ?></p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/login">
            <?= $csrf_field ?>
            <?php if (!empty($redirect)): ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="login" placeholder="admin" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="/admin/forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="back-link">
            <a href="/">&larr; Back to site</a>
        </div>
    </div>
</body>
</html>
