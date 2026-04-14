<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonios - Admin</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Testimonios</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Mensaje</th>
                    <th>Foto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><?php echo esc($t['username']); ?></td>
                    <td><?php echo esc($t['message']); ?></td>
                    <td><img src="<?php echo esc($t['photo']); ?>" alt="Foto" width="50"></td>
                    <td><?php echo $t['approved'] ? 'Aprobado' : 'Pendiente'; ?></td>
                    <td><?php echo $t['created_at']; ?></td>
                    <td>
                        <?php if (!$t['approved']): ?>
                        <a href="?action=approve&id=<?php echo $t['id']; ?>" class="btn">Aprobar</a>
                        <a href="?action=reject&id=<?php echo $t['id']; ?>" class="btn btn-warn">Rechazar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>