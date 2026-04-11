<section class="panel" style="max-width:520px;margin:18px auto;">
    <h1>Registro</h1>
    <?php if ($msg): ?><p><?php echo esc($msg); ?></p><?php endif; ?>
    <form method="post">
        <label>Nombre completo</label>
        <input type="text" name="full_name" required>
        <label>Correo</label>
        <input type="email" name="email" required>
        <label>Contrasena</label>
        <div class="password-wrap">
            <input id="pass_register" type="password" name="password" required>
            <button type="button" onclick="togglePassword('pass_register', this)">Ver</button>
        </div>
        <button type="submit">Crear cuenta</button>
    </form>
</section>
