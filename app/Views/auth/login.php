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
    <form method="post" style="margin-top:12px;padding-top:12px;border-top:1px solid #ddd;">
        <input type="hidden" name="resend_verification" value="1">
        <label>¿No recibiste el correo de confirmacion?</label>
        <input type="email" name="resend_email" placeholder="Tu correo registrado" required>
        <button type="submit">Reenviar confirmacion</button>
        <small style="display:block;margin-top:8px;color:#555;">Maximo 3 envios por cuenta, con espera de 5 minutos entre intentos.</small>
    </form>
    <p><a href="<?php echo esc(route_url('register')); ?>">No tienes cuenta? Registrate</a></p>
</section>
