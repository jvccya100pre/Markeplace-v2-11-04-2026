<section class="panel">
    <h1>Categorias</h1>
    <form method="post" class="filters">
        <input type="hidden" name="action" value="create">
        <div><label>Nombre categoria</label><input type="text" name="name" required></div>
        <div><button type="submit">Agregar categoria</button></div>
    </form>
    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin-top:10px;">
        <tr><th>ID</th><th>Categoria</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td>
                    <?php if ($edit_id == $r['id']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <input type="text" name="name" value="<?php echo esc($r['name']); ?>" required>
                            <button type="submit">Guardar</button>
                            <a href="?">Cancelar</a>
                        </form>
                    <?php else: ?>
                        <?php echo esc($r['name']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo (int)$r['is_active'] ? 'Activa' : 'Inactiva'; ?></td>
                <td>
                    <?php if ($edit_id != $r['id']): ?>
                        <a href="?edit=<?php echo (int)$r['id']; ?>">Editar</a> |
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <button type="submit"><?php echo (int)$r['is_active'] ? 'Desactivar' : 'Activar'; ?></button>
                        </form> |
                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
