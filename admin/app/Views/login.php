<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="<?php echo esc(app_url('/assets/styles.css')); ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="<?php echo esc(app_url('/assets/app.js')); ?>" defer></script>
</head>
<body>
<section class="panel" style="max-width:520px;margin:30px auto;">
    <h1>Iniciar sesion admin</h1>
    <?php if (!empty($message)): ?><p style="color:#0a6e2f"><?php echo esc($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:#a10f0f"><?php echo esc($error); ?></p><?php endif; ?>
    <form method="post">
        <label>Correo</label>
        <input type="email" name="email" required>
        <label>Contrasena</label>
        <div class="password-wrap">
            <input id="pass_admin" type="password" name="password" required>
            <button type="button" onclick="togglePassword('pass_admin', this)">Ver</button>
        </div>
        <?php global $config; ?>
        <?php if (!empty($config['captcha_public'])): ?>
            <div style="margin:12px 0; text-align:center;">
                <div class="g-recaptcha" data-sitekey="<?php echo esc($config['captcha_public']); ?>"></div>
            </div>
        <?php endif; ?>
        <button type="submit">Entrar</button>
    </form>
</section>
</body>
</html>
