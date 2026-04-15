<section class="panel" style="max-width:760px;margin:18px auto;">
    <h1>Agregar Testimonio</h1>
    <?php if (!empty($success)): ?>
        <p style="color:#0a6e2f;font-weight:600;">Testimonio enviado. Espera aprobación del administrador.</p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color:#a10f0f;font-weight:600;"><?php echo esc($error); ?></p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="message">Mensaje</label>
        <textarea name="message" id="message" rows="6" required></textarea>
        <label for="photo">Foto obligatoria: toma una selfie o foto usando el producto</label>
        <input type="file" name="photo" id="photo" accept="image/*" required>
        <button type="submit" class="btn" style="margin-top:12px;">Enviar</button>
    </form>
</section>