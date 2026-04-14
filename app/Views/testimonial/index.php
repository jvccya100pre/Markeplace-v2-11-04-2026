<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonios</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h1>Testimonios de Clientes</h1>
        <div class="testimonials">
            <?php foreach ($testimonials as $t): ?>
            <div class="testimonial">
                <img src="<?php echo esc($t['photo']); ?>" alt="Foto de <?php echo esc($t['username']); ?>">
                <p><?php echo esc($t['message']); ?></p>
                <small>- <?php echo esc($t['username']); ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (auth_user()): ?>
        <a href="testimonial.php?action=create" class="btn">Agregar Testimonio</a>
        <?php endif; ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>