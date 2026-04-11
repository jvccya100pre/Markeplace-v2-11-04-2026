<section class="panel" style="max-width:720px;margin:18px auto;">
    <h1>Confirmar pedido</h1>
    <?php if ($message): ?>
        <p><?php echo esc($message); ?></p>
        <p><a href="<?php echo esc(app_url('/index.php')); ?>">Ir a panel de cliente</a></p>
    <?php else: ?>
        <form method="post">
            <label>Metodo de pago</label>
            <select name="payment_method" required>
                <option value="deposito bancario">Deposito bancario</option>
                <option value="paypal">Paypal</option>
                <option value="binance">Binance</option>
            </select>
            <label>Metodo de entrega</label>
            <select name="delivery_method" required>
                <option value="encomienda">Encomienda</option>
                <option value="personal">Personal</option>
                <option value="acordar con el vendedor">Acordar con el vendedor</option>
            </select>
            <label>Notas</label>
            <textarea name="notes" rows="4"></textarea>
            <button type="submit">Crear pedido</button>
        </form>
    <?php endif; ?>
</section>
