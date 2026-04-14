<section class="panel">
    <h1>Categorias</h1>
    <?php if (isset($message) && $message !== ''): ?>
        <p><?php echo esc($message); ?></p>
    <?php endif; ?>
    <form method="post" class="filters">
        <?php if (isset($editId) && $editId > 0 && isset($editingCategory)): ?>
            <input type="hidden" name="update_category" value="1">
            <input type="hidden" name="category_id" value="<?php echo (int)$editingCategory['id']; ?>">
            <div><label>Editar categoría</label><input type="text" name="name" value="<?php echo esc($editingCategory['name']); ?>" required></div>
            <div style="display:inline-flex;gap:10px;align-items:center;">
                <button type="submit">Actualizar categoría</button>
                <a class="btn btn-alt" href="<?php echo esc(app_url('/categories.php')); ?>">Cancelar</a>
            </div>
        <?php else: ?>
            <input type="hidden" name="add_category" value="1">
            <div><label>Nombre categoría</label><input type="text" name="name" required></div>
            <div><button type="submit">Agregar categoría</button></div>
        <?php endif; ?>
    </form>
    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin-top:10px;">
        <tr><th>ID</th><th>Categoria</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo esc($r['name']); ?></td>
                <td><?php echo (int)$r['is_active'] ? 'Activa' : 'Inactiva'; ?></td>
                <td>
                    <a href="<?php echo esc(app_url('/categories.php?edit=' . (int)$r['id'])); ?>">Editar</a>
                    <form method="post" style="display:inline-block;margin:0;padding:0;" onsubmit="return confirm('¿Eliminar categoría?');">
                        <input type="hidden" name="delete_category" value="1">
                        <input type="hidden" name="category_id" value="<?php echo (int)$r['id']; ?>">
                        <button type="submit" class="btn-warn" style="padding:6px 10px;">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
