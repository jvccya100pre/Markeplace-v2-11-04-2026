<section class="panel">
    <h1>Carros de compras / Pedidos</h1>
    <?php $showSeller = isset($rows[0]) && array_key_exists('seller_name', $rows[0]); ?>
    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <th>ID</th><th>Cliente</th><th>Correo</th><th>Total</th><th>Pago</th><th>Entrega</th><?php if ($showSeller): ?><th>Vendedor</th><?php endif; ?><th>Fecha</th><th>PDF</th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?php echo (int)$r['id']; ?></td>
                <td><?php echo esc($r['full_name']); ?></td>
                <td><?php echo esc($r['email']); ?></td>
                <td>$<?php echo number_format($r['total'], 2); ?></td>
                <td><?php echo esc($r['payment_method']); ?></td>
                <td><?php echo esc($r['delivery_method']); ?></td>
                <?php if ($showSeller): ?><td><?php echo esc($r['seller_name']); ?></td><?php endif; ?>
                <td><?php echo esc($r['created_at']); ?></td>
                <td><a href="<?php echo esc(app_url('/order_pdf.php?id=' . (int)$r['id'])); ?>" target="_blank">Imprimir PDF</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
