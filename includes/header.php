<?php
$u = auth_user();
$cartCount = count(cart_session_items());
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu tienda On line</title>
    <link rel="icon" href="<?php echo esc(app_url('img/company/icono.ico')); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/styles.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="assets/app.js" defer></script>
    <?php if ($u): ?>
        <script>
            window.sessionTimeoutConfig = {
                timeoutMs: 600000,
                warningMs: 60000,
                logoutUrl: "<?php echo esc(route_url('logout')); ?>"
            };
        </script>
    <?php endif; ?>
</head>
<body>
<header class="topbar">
    <div class="container">
        <nav>
            <a class="brand" href="<?php echo esc(route_url('home')); ?>" title="Tu tienda On line AA">
                <img class="brand-icon" src="img/company/LogoTuTiendaOnline3.png" width='45px' height='45px' alt="Logo">
                <strong>Tu tienda On line</strong>
            </a>
            <button class="burger-menu" type="button" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="menu-links">
                <a href="<?php echo esc(route_url('home')); ?>">Inicio</a>
                <a href="<?php echo esc(route_url('catalog_pdf')); ?>">Descargar catalogo PDF</a>
                <a href="<?php echo esc(route_url('home')); ?>">Catalogo de productos</a>
                <a href="testimonial.php">Testimonios</a>
                <?php if ($u): ?>
                    <a href="<?php echo esc(app_url('/index.php')); ?>">Mi panel</a>
                    <a href="<?php echo esc(route_url('cart')); ?>">Carrito (<?php echo $cartCount; ?>)</a>
                    <a href="<?php echo esc(route_url('logout')); ?>">Cerrar sesion</a>
                <?php else: ?>
                    <a href="<?php echo esc(route_url('login')); ?>">INICIAR SESION</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<img src="<?php echo esc(app_url('/tienda.png')); ?>" alt="Tienda" class="hero-img">
<main class="container">
