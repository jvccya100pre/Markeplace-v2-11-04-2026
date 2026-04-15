<section class="panel">
    <h1>Testimonios de Clientes</h1>
    <?php if (count($testimonials) === 0): ?>
        <p>Aun no hay testimonios aprobados.</p>
    <?php else: ?>
        <div class="testimonials">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial">
                    <img src="<?php echo esc($t['photo']); ?>" alt="Foto de <?php echo esc($t['author_name']); ?>">
                    <p><?php echo esc($t['message']); ?></p>
                    <small>- <?php echo esc($t['author_name']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (auth_user()): ?>
        <p style="margin-top:16px;">
            <a href="<?php echo esc(route_url('testimonial_create')); ?>" class="btn" style="display:inline-block;width:auto;padding:10px 16px;">Agregar Testimonio</a>
        </p>
    <?php endif; ?>
</section>