<section class="panel">
    <h1>Productos</h1>
    <?php if ($message !== ''): ?>
        <p><?php echo esc($message); ?></p>
    <?php endif; ?>

    <form method="get" class="filters" style="margin-bottom:10px;">
        <div>
            <label>Buscar por nombre o codigo</label>
            <input type="text" name="q" value="<?php echo esc($search); ?>" placeholder="Ej: SKU-DEMO-0001">
        </div>
        <div><button type="submit">Buscar</button></div>
        <div><a class="btn btn-alt" style="display:block;text-align:center;padding:10px;" href="<?php echo esc(app_url('/products.php')); ?>">Limpiar</a></div>
    </form>

    <form method="post" class="filters">
        <?php if ($formData['id'] > 0): ?>
            <input type="hidden" name="update_product" value="1">
            <input type="hidden" name="product_id" value="<?php echo (int)$formData['id']; ?>">
        <?php else: ?>
            <input type="hidden" name="add_product" value="1">
        <?php endif; ?>

        <div>
            <label>Categoria</label>
            <select name="category_id" required>
                <?php foreach ($cats as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)$formData['category_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo esc($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Nombre</label><input type="text" name="name" value="<?php echo esc($formData['name']); ?>" required></div>
        <div><label>Descripcion</label><input type="text" name="description" value="<?php echo esc($formData['description']); ?>" required></div>
        <div><label>Codigo de producto</label><input type="text" name="internal_code" value="<?php echo esc($formData['internal_code']); ?>" required></div>
        <div><label>Stock disponible</label><input type="number" min="0" name="stock" value="<?php echo (int)$formData['stock']; ?>" required></div>
        <div><label>Color</label><input type="text" name="color" value="<?php echo esc($formData['color']); ?>"></div>
        <div><label>Precio</label><input type="number" step="0.01" min="0" name="price" value="<?php echo esc($formData['price']); ?>" required></div>
        <div><label>Ruta imagen</label><input type="text" name="image_path" value="<?php echo esc($formData['image_path']); ?>"></div>
        <div>
            <button type="submit"><?php echo $formData['id'] > 0 ? 'Actualizar producto' : 'Guardar producto'; ?></button>
            <?php if ($formData['id'] > 0): ?>
                <a class="btn btn-alt" style="display:inline-block;margin-top:8px;text-align:center;padding:10px;" href="<?php echo esc(app_url('/products.php')); ?>">Cancelar edicion</a>
            <?php endif; ?>
        </div>
    </form>

    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin-top:12px;">
        <tr><th>ID</th><th>Categoria</th><th>Nombre</th><th>Descripcion</th><th>Codigo</th><th>Stock</th><th>Precio</th><th>Acciones</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo esc($r['category_name']); ?></td>
                <td><?php echo esc($r['name']); ?></td>
                <td><?php echo esc($r['description']); ?></td>
                <td><?php echo esc($r['internal_code']); ?></td>
                <td><?php echo (int)$r['stock']; ?></td>
                <td>$<?php echo number_format($r['price'], 2); ?></td>
                <td>
                    <a href="<?php echo esc(app_url('/products.php?edit=' . (int)$r['id'])); ?>">Editar</a>
                    <form method="post" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Eliminar producto?');">
                        <input type="hidden" name="delete_product" value="1">
                        <input type="hidden" name="product_id" value="<?php echo (int)$r['id']; ?>">
                        <button class="btn-warn" type="submit" style="width:auto;padding:6px 10px;">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
