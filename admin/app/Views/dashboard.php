<section class="panel">
    <h2>Estadisticas</h2>
    <div class="filters">
        <div><strong>Visitas totales</strong><p><?php echo $totalVisits; ?></p></div>
        <div><strong>Visita unica por IP</strong><p><?php echo $uniqueIp; ?></p></div>
        <div><strong>Usuarios registrados</strong><p><?php echo $totalUsers; ?></p></div>
        <div><strong>Pedidos realizados</strong><p><?php echo $totalOrders; ?></p></div>
        <div><strong>Total ganancias del mes</strong><p>VES <?php echo number_format($monthGain, 2); ?></p></div>
    </div>
</section>

<section class="panel">
    <h2>Paginas mas visitadas</h2>
    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
        <tr><th>Web visitada</th><th>Total</th></tr>
        <?php foreach ($topPages as $row): ?>
            <tr><td><?php echo esc($row['page_visited']); ?></td><td><?php echo (int)$row['c']; ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="panel">
    <h2>Editar metodos de pago</h2>
    <?php if ($message !== ''): ?>
        <p><?php echo esc($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <div class="filters">
            <div>
                <label>Banco</label>
                <select name="pm_bank">
                    <option value="0102 BANCO DE VENEZUELA S.A.I.C.A." <?php echo $currentBank === '0102 BANCO DE VENEZUELA S.A.I.C.A.' ? 'selected' : ''; ?>>0102 BANCO DE VENEZUELA S.A.I.C.A.</option>
                    <option value="0134 BANESCO" <?php echo $currentBank === '0134 BANESCO' ? 'selected' : ''; ?>>0134 BANESCO</option>
                    <option value="0156 100%BANCO" <?php echo $currentBank === '0156 100%BANCO' ? 'selected' : ''; ?>>0156 100%BANCO</option>
                    <option value="0172 BANCAMIGA BANCO UNIVERSAL, C.A." <?php echo $currentBank === '0172 BANCAMIGA BANCO UNIVERSAL, C.A.' ? 'selected' : ''; ?>>0172 BANCAMIGA BANCO UNIVERSAL, C.A.</option>
                    <option value="0105 BANCO MERCANTIL C.A." <?php echo $currentBank === '0105 BANCO MERCANTIL C.A.' ? 'selected' : ''; ?>>0105 BANCO MERCANTIL C.A.</option>
                    <option value="0108 BANCO PROVINCIAL BBVA" <?php echo $currentBank === '0108 BANCO PROVINCIAL BBVA' ? 'selected' : ''; ?>>0108 BANCO PROVINCIAL BBVA</option>
                </select>
            </div>
            <div>
                <label>Tipo identidad</label>
                <select name="pm_identity_type">
                    <option value="V" <?php echo $currentIdentityType === 'V' ? 'selected' : ''; ?>>V</option>
                    <option value="E" <?php echo $currentIdentityType === 'E' ? 'selected' : ''; ?>>E</option>
                    <option value="Rif" <?php echo $currentIdentityType === 'Rif' ? 'selected' : ''; ?>>Rif</option>
                    <option value="nic" <?php echo $currentIdentityType === 'nic' ? 'selected' : ''; ?>>nic</option>
                    <option value="dni" <?php echo $currentIdentityType === 'dni' ? 'selected' : ''; ?>>dni</option>
                </select>
            </div>
            <div><label>Numero identidad</label><input type="text" maxlength="30" name="pm_identity_number" value="<?php echo esc($currentIdentityNumber); ?>"></div>
            <div><label>Prefijo telefono</label><input type="text" name="pm_phone_prefix" value="<?php echo esc($currentPhonePrefix); ?>"></div>
            <div><label>Telefono</label><input type="text" name="pm_phone_number" value="<?php echo esc($currentPhoneNumber); ?>"></div>
            <div><label>Binance UID/correo</label><input type="text" name="binance_uid" value="<?php echo esc($currentBinance); ?>"></div>
            <div><label>Paypal correo</label><input type="email" name="paypal_email" value="<?php echo esc($currentPaypal); ?>"></div>
            <div style="grid-column:1/-1;">
                <label>Correos con acceso a precio al mayor</label>
                <textarea name="wholesale_allowed_emails" rows="4" placeholder="cliente1@correo.com&#10;cliente2@correo.com"><?php echo esc($currentWholesaleEmails); ?></textarea>
                <small>Un correo por línea. Solo estos clientes registrados verán precio al mayor.</small>
            </div>
            <div><button type="submit" name="save_payment" value="1">Guardar</button></div>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Tasa de cambio USD a VES</h2>
    <?php if ($message !== '' && strpos($message, 'Tasa') !== false): ?>
        <p><?php echo esc($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <div class="filters">
            <div><label>Valor USD a VES (hoy)</label><input type="number" step="0.01" name="usd_to_ves" value="<?php echo $currentRate ? number_format($currentRate, 2) : ''; ?>" required></div>
            <div><button type="submit" name="save_exchange" value="1">Guardar tasa</button></div>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Generar Backup</h2>
    <form method="post">
        <p>Descargar un backup completo de la base de datos en formato SQL.</p>
        <button type="submit" name="generate_backup" value="1">Generar Backup</button>
    </form>
</section>

<section class="panel">
    <h2>Limpiar Productos</h2>
    <form method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar todos los productos? Esta acción no se puede deshacer.');">
        <p>Eliminar todos los productos de la base de datos.</p>
        <button type="submit" name="clean_products" value="1" style="background:red;color:white;">Limpiar Productos</button>
    </form>
</section>
