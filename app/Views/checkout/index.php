<section class="panel" style="max-width:720px;margin:18px auto;">
    <h1>Confirmar pedido</h1>
    <?php if ($message): ?>
        <p><?php echo esc($message); ?></p>
        <p><a href="<?php echo esc(app_url('/index.php')); ?>">Ir a panel de cliente</a></p>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <p style="color:#a12622;font-weight:600;"><?php echo esc($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label>Metodo de pago</label>
            <select name="payment_method" id="payment_method" required>
                <option value="deposito bancario" <?php echo $selectedPayment === 'deposito bancario' ? 'selected' : ''; ?>>Deposito bancario</option>
                <option value="paypal" <?php echo $selectedPayment === 'paypal' ? 'selected' : ''; ?>>Paypal</option>
                <option value="binance" <?php echo $selectedPayment === 'binance' ? 'selected' : ''; ?>>Binance</option>
            </select>
            <div id="payment-method-data" style="margin-top:12px;padding:12px;border:1px solid #d9d9d9;border-radius:8px;background:#fafafa;"></div>
            <?php if ($hasDelivery): ?>
            <label>Metodo de entrega</label>
            <select name="delivery_method" required>
                <option value="personal" <?php echo $selectedDelivery === 'personal' ? 'selected' : ''; ?>>Personal</option>
                <option value="encomienda" <?php echo $selectedDelivery === 'encomienda' ? 'selected' : ''; ?>>Encomienda</option>
                <option value="delivery" <?php echo $selectedDelivery === 'delivery' ? 'selected' : ''; ?>>Delivery</option>
            </select>
            <?php endif; ?>
            <label>Notas</label>
            <textarea name="notes" rows="4"><?php echo esc($notes); ?></textarea>
            <button type="submit">Confirmar pago</button>
        </form>
        <script>
        (function () {
            var paymentDetails = <?php echo json_encode($paymentDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var select = document.getElementById('payment_method');
            var container = document.getElementById('payment-method-data');

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderPaymentDetails() {
                var selected = select.value;
                var details = paymentDetails[selected];
                if (!details) {
                    container.innerHTML = '<p style="color:#a12622;font-weight:600;">No hay datos configurados para este metodo de pago.</p>';
                    return;
                }

                var rows = [];
                var hasAnyValue = false;
                for (var label in details.fields) {
                    if (!Object.prototype.hasOwnProperty.call(details.fields, label)) {
                        continue;
                    }
                    var value = details.fields[label];
                    if (String(value).trim() !== '') {
                        hasAnyValue = true;
                        rows.push('<p style="margin:4px 0;"><strong>' + escapeHtml(label) + ':</strong> ' + escapeHtml(value) + '</p>');
                    }
                }

                if (!hasAnyValue) {
                    container.innerHTML = '<p style="color:#a12622;font-weight:600;">Este metodo de pago no tiene datos configurados. Contacta al administrador.</p>';
                    return;
                }

                container.innerHTML = '<p style="margin:0 0 8px 0;font-weight:600;">Datos para ' + escapeHtml(details.label) + '</p>' + rows.join('');
            }

            select.addEventListener('change', renderPaymentDetails);
            renderPaymentDetails();
        }());
        </script>
    <?php endif; ?>
</section>
