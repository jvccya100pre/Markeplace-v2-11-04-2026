<section class="panel" style="max-width:520px;margin:18px auto;">
    <h1>Iniciar sesion</h1>
    <?php if (!empty($message)): ?><p style="color:#0a6e2f"><?php echo esc($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:#a10f0f"><?php echo esc($error); ?></p><?php endif; ?>
    <form method="post">
        <label>Correo</label>
        <input type="email" name="email" required>
        <label>Contrasena</label>
        <div class="password-wrap">
            <input id="pass_login" type="password" name="password" required>
            <button type="button" onclick="togglePassword('pass_login', this)">Ver</button>
        </div>
        <?php global $config; ?>
        <?php if (!empty($config['captcha_public'])): ?>
            <center>
                <div style="margin:10px 0;display:inline-block;">
                    <div class="g-recaptcha" data-sitekey="<?php echo esc($config['captcha_public']); ?>"></div>
                </div>
            </center>
        <?php endif; ?>
        <button type="submit">Ingresar</button>
    </form>
    <p><a href="<?php echo esc(route_url('register')); ?>">No tienes cuenta? Registrate</a></p>
</section>
