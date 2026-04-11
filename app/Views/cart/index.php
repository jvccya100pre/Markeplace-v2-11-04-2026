<section class="panel">
    <h1>Carrito de compras</h1>
    <?php if (!count($products)): ?>
        <p>No hay productos en el carrito.</p>
    <?php else: ?>
        <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;">
            <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th>Accion</th></tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo esc($p['name']); ?></td>
                    <td><?php echo (int)$items[$p['id']]; ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td>$<?php echo number_format($p['price'] * (int)$items[$p['id']], 2); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                            <button class="btn-warn" type="submit" name="remove_item" value="1">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Total: $<?php echo number_format($total, 2); ?></h3>
        <a class="btn" href="<?php echo esc(route_url('checkout')); ?>" style="display:inline-block;padding:10px 12px;">Confirmar compra</a>
    <?php endif; ?>
</section>
