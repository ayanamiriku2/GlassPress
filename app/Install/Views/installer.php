<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Install GlassPress</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --primary: #6366f1;
            --primary-hover: #818cf8;
            --danger: #ef4444;
            --success: #22c55e;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 30%, #1e293b 60%, #0f172a 100%);
            --input-bg: rgba(255, 255, 255, 0.08);
            --input-border: rgba(255, 255, 255, 0.15);
            --input-focus: rgba(99, 102, 241, 0.5);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            color: var(--text);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .installer {
            position: relative; z-index: 1;
            width: 100%; max-width: 640px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 2.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #818cf8, #c084fc, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 0.25rem;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--glass-shadow);
            margin-bottom: 1.5rem;
        }

        .glass-card h2 {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .glass-card h2 .icon {
            width: 24px; height: 24px;
            background: var(--primary);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full { grid-column: 1 / -1; }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.35rem;
            color: var(--text-muted);
        }

        label .required {
            color: var(--danger);
            margin-left: 2px;
        }

        input[type="text"], input[type="password"], input[type="email"], input[type="number"], select {
            width: 100%;
            padding: 0.65rem 0.85rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--input-focus);
        }

        input::placeholder { color: rgba(255,255,255,0.3); }

        select option { background: #1e293b; color: var(--text); }

        .help-text {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            cursor: pointer;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px; height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .checkbox-group span { font-size: 0.9rem; }

        .btn-test {
            padding: 0.5rem 1rem;
            background: rgba(34, 197, 94, 0.2);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-test:hover { background: rgba(34, 197, 94, 0.3); }

        .btn-install {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.01em;
        }

        .btn-install:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-install:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .alert ul { margin: 0.5rem 0 0 1.25rem; padding: 0; }
        .alert li { margin-bottom: 0.25rem; }

        #db-test-result { margin-top: 0.5rem; }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transition: all 0.3s;
        }

        .step-dot.active { background: var(--primary); width: 24px; border-radius: 4px; }

        .req-check { font-size: 0.85rem; }
        .req-check .pass { color: var(--success); }
        .req-check .fail { color: var(--danger); }
        .req-check .warn { color: #f59e0b; }

        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; }
            .glass-card { padding: 1.25rem; }
            body { padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="logo">
            <h1>&#9670; GlassPress</h1>
            <p>Modern CMS Installation Wizard</p>
        </div>

        <div class="step-indicator">
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
            <div class="step-dot"></div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="/install" id="install-form" autocomplete="off">
            <?= $csrf_field ?>

            <!-- System Requirements -->
            <div class="glass-card">
                <h2><span class="icon">&#10003;</span> System Requirements</h2>
                <div class="req-check">
                    <?php
                    $requirements = [
                        ['PHP 8.2+', version_compare(PHP_VERSION, '8.2.0', '>='), true],
                        ['PDO MySQL Extension', extension_loaded('pdo_mysql'), false],
                        ['GD Extension', extension_loaded('gd'), false],
                        ['JSON Extension', extension_loaded('json'), true],
                        ['mbstring Extension', extension_loaded('mbstring'), true],
                        ['fileinfo Extension', extension_loaded('fileinfo'), true],
                        ['config/ writable', is_writable(GLASSPRESS_ROOT . '/config') || is_writable(dirname(GLASSPRESS_ROOT . '/config')), true],
                        ['storage/ writable', is_writable(GLASSPRESS_ROOT . '/storage'), true],
                        ['uploads/ writable', is_writable(GLASSPRESS_ROOT . '/uploads'), true],
                    ];
                    $allPassed = true;
                    foreach ($requirements as [$label, $passed, $required]):
                        if (!$passed && $required) $allPassed = false;
                    ?>
                    <p class="<?= $passed ? 'pass' : ($required ? 'fail' : 'warn') ?>">
                        <?= $passed ? '&#10003;' : '&#10007;' ?> <?= $label ?>
                        <?= $passed ? '' : ($required ? '<em>(required)</em>' : '<em>(recommended)</em>') ?>
                    </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Database Configuration -->
            <div class="glass-card">
                <h2><span class="icon">&#9881;</span> Database Configuration</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Database Host <span class="required">*</span></label>
                        <input type="text" name="db_host" value="<?= htmlspecialchars($old['db_host'] ?? '127.0.0.1', ENT_QUOTES) ?>" placeholder="127.0.0.1" required>
                    </div>
                    <div class="form-group">
                        <label>Database Port</label>
                        <input type="number" name="db_port" value="<?= htmlspecialchars($old['db_port'] ?? '3306', ENT_QUOTES) ?>" placeholder="3306">
                    </div>
                </div>
                <div class="form-group">
                    <label>Database Name <span class="required">*</span></label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($old['db_name'] ?? '', ENT_QUOTES) ?>" placeholder="glasspress" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Database Username <span class="required">*</span></label>
                        <input type="text" name="db_username" value="<?= htmlspecialchars($old['db_username'] ?? '', ENT_QUOTES) ?>" placeholder="root" required>
                    </div>
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_password" value="" placeholder="••••••••">
                    </div>
                </div>
                <div class="form-group">
                    <label>Table Prefix</label>
                    <input type="text" name="db_prefix" value="<?= htmlspecialchars($old['db_prefix'] ?? 'gp_', ENT_QUOTES) ?>" placeholder="gp_">
                    <p class="help-text">Change this if you want to run multiple GlassPress installations in one database.</p>
                </div>
                <button type="button" class="btn-test" onclick="testDatabase()">Test Connection</button>
                <div id="db-test-result"></div>
            </div>

            <!-- Site Settings -->
            <div class="glass-card">
                <h2><span class="icon">&#9733;</span> Site Settings</h2>
                <div class="form-group">
                    <label>Site Title <span class="required">*</span></label>
                    <input type="text" name="site_title" value="<?= htmlspecialchars($old['site_title'] ?? '', ENT_QUOTES) ?>" placeholder="My Amazing Blog" required>
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="site_tagline" value="<?= htmlspecialchars($old['site_tagline'] ?? '', ENT_QUOTES) ?>" placeholder="Just another GlassPress site">
                    <p class="help-text">A short description of your site.</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Timezone</label>
                        <select name="timezone">
                            <?php foreach ($timezones as $tz): ?>
                            <option value="<?= $tz ?>" <?= ($old['timezone'] ?? 'UTC') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Language</label>
                        <select name="language">
                            <option value="en" <?= ($old['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Admin Account -->
            <div class="glass-card">
                <h2><span class="icon">&#9823;</span> Administrator Account</h2>
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="admin_username" value="<?= htmlspecialchars($old['admin_username'] ?? '', ENT_QUOTES) ?>" placeholder="admin" required autocomplete="new-password">
                    <p class="help-text">3-30 characters. Letters, numbers, and underscores only.</p>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="admin_email" value="<?= htmlspecialchars($old['admin_email'] ?? '', ENT_QUOTES) ?>" placeholder="admin@example.com" required>
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="admin_password" placeholder="Minimum 8 characters" required autocomplete="new-password" minlength="8">
                </div>
            </div>

            <!-- Options -->
            <div class="glass-card">
                <h2><span class="icon">&#9998;</span> Options</h2>
                <label class="checkbox-group">
                    <input type="checkbox" name="install_sample" checked>
                    <span>Install sample content (recommended for first-time users)</span>
                </label>
            </div>

            <button type="submit" class="btn-install" <?= !$allPassed ? 'disabled' : '' ?>>
                &#9670; Install GlassPress
            </button>
        </form>

        <p style="text-align:center;color:var(--text-muted);font-size:0.8rem;margin-top:1.5rem;">
            GlassPress v<?= GLASSPRESS_VERSION ?> &middot; Requires PHP 8.2+ &middot; MySQL/MariaDB
        </p>
    </div>

    <script>
    function testDatabase() {
        const btn = document.querySelector('.btn-test');
        const result = document.getElementById('db-test-result');
        btn.disabled = true;
        btn.textContent = 'Testing...';
        result.innerHTML = '';

        const form = document.getElementById('install-form');
        const data = new FormData();
        data.append('db_host', form.db_host.value);
        data.append('db_port', form.db_port.value);
        data.append('db_name', form.db_name.value);
        data.append('db_username', form.db_username.value);
        data.append('db_password', form.db_password.value);

        fetch('/install/test-db', { method: 'POST', body: data })
            .then(r => r.json())
            .then(data => {
                result.innerHTML = '<div class="alert ' + (data.success ? 'alert-success' : 'alert-error') + '">' +
                    (data.success ? '&#10003; ' : '&#10007; ') + data.message + '</div>';
            })
            .catch(() => {
                result.innerHTML = '<div class="alert alert-error">&#10007; Connection test failed.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Test Connection';
            });
    }

    // Animated step indicator on scroll
    document.addEventListener('scroll', function() {
        const cards = document.querySelectorAll('.glass-card');
        const dots = document.querySelectorAll('.step-dot');
        const scrollPos = window.scrollY + window.innerHeight / 3;
        
        let activeStep = 0;
        cards.forEach((card, i) => {
            if (card.offsetTop <= scrollPos) activeStep = Math.min(i, dots.length - 1);
        });
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === activeStep);
        });
    });
    </script>
</body>
</html>
