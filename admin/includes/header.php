<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="<?php echo esc(app_url('/assets/styles.css')); ?>">
</head>
<body>
<header class="topbar">
    <div class="container">
        <nav>
            <strong>Panel Administrativo</strong>
            <div class="menu-links">
                <a href="<?php echo esc(app_url('/index.php')); ?>">Inicio</a>
                <a href="<?php echo esc(app_url('/index.php')); ?>">Dashboard</a>
                <a href="<?php echo esc(app_url('/categories.php')); ?>">Categorias</a>
                <a href="<?php echo esc(app_url('/products.php')); ?>">Productos</a>
                <a href="<?php echo esc(app_url('/orders.php')); ?>">Carros/Pedidos</a>
                <a href="<?php echo esc(app_url('/chat.php')); ?>">Chat interno</a>
                <a href="<?php echo esc(app_url('/login.php?logout=1')); ?>">Salir</a>
            </div>
        </nav>
    </div>
</header>
<main class="container">
