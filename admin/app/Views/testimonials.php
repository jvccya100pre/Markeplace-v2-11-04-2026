<section class="panel">
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
                    <td><?php echo (int)$t['id']; ?></td>
                    <td><?php echo esc($t['author_name']); ?></td>
                    <td><?php echo esc($t['message']); ?></td>
                    <td><img src="<?php echo esc($t['photo']); ?>" alt="Foto" width="50"></td>
                    <td><?php echo !empty($t['approved']) ? 'Aprobado' : 'Pendiente'; ?></td>
                    <td><?php echo esc($t['created_at']); ?></td>
                    <td>
                        <?php if (empty($t['approved'])): ?>
                            <a href="<?php echo esc(app_url('/testimonials.php?action=approve&id=' . (int)$t['id'])); ?>" class="btn">Aprobar</a>
                            <a href="<?php echo esc(app_url('/testimonials.php?action=reject&id=' . (int)$t['id'])); ?>" class="btn btn-warn">Rechazar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>