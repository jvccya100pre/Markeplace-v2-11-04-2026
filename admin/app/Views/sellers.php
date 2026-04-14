<section class="panel">
    <h1>Vendedores</h1>
    <?php if ($message !== ''): ?>
        <p><?php echo esc($message); ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p style="color:#a10f0f"><?php echo esc($error); ?></p>
    <?php endif; ?>

    <?php if (!$hasTable): ?>
        <p>La tabla de vendedores no está disponible. Ejecuta la migración SQL en <code>schema-update.sql</code> y vuelve a cargar.</p>
    <?php else: ?>
        <form method="post" class="filters" style="margin-bottom:12px; max-width:600px;" autocomplete="off">
            <?php if ($formData['id'] > 0): ?>
                <input type="hidden" name="update_seller" value="1">
                <input type="hidden" name="seller_id" value="<?php echo (int)$formData['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_seller" value="1">
            <?php endif; ?>

            <div><label>Nombre completo</label><input type="text" name="full_name" value="<?php echo esc($formData['full_name']); ?>" required></div>
            <div><label>Correo</label><input type="email" name="email" value="<?php echo esc($formData['email']); ?>" required></div>
            <div><label>Usuario</label><input type="text" name="username" value="<?php echo esc($formData['username']); ?>" required></div>
            <div>
                <button type="submit"><?php echo $formData['id'] > 0 ? 'Actualizar vendedor' : 'Crear vendedor'; ?></button>
                <?php if ($formData['id'] > 0): ?>
                    <a class="btn btn-alt" href="<?php echo esc(app_url('/sellers.php')); ?>">Cancelar edicion</a>
                <?php endif; ?>
            </div>
        </form>

        <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; margin-top:12px;">
            <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Usuario</th><th>Enlace / codigo</th><th>Acciones</th></tr>
            <?php foreach ($rows as $seller): ?>
                <tr>
                    <td><?php echo (int)$seller['id']; ?></td>
                    <td><?php echo esc($seller['full_name']); ?></td>
                    <td><?php echo esc($seller['email']); ?></td>
                    <td><?php echo esc($seller['username']); ?></td>
                    <td>
                        <?php echo esc($seller['link_token']); ?><br>
                        <a href="<?php echo esc(app_url('/?seller=' . (int)$seller['id'])); ?>" target="_blank">Ver link</a>
                    </td>
                    <td>
                        <a href="<?php echo esc(app_url('/sellers.php?edit=' . (int)$seller['id'])); ?>">Editar</a>
                        <form method="post" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Eliminar vendedor?');">
                            <input type="hidden" name="delete_seller" value="1">
                            <input type="hidden" name="seller_id" value="<?php echo (int)$seller['id']; ?>">
                            <button class="btn-warn" type="submit" style="width:auto;padding:6px 10px;">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</section>
