<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Testimonio</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h1>Agregar Testimonio</h1>
        <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Testimonio enviado. Espera aprobación del administrador.</p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="message">Mensaje:</label>
            <textarea name="message" id="message" required></textarea>
            <label for="photo">Foto (obligatoria): Tome una selfie o foto usando el producto</label>
            <input type="file" name="photo" id="photo" accept="image/*" required>
            <button type="submit" class="btn">Enviar</button>
        </form>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>