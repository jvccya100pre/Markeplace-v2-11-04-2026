<section class="panel">
    <h1>Chat interno (cliente-admin)</h1>
    <form method="get" class="filters">
        <div>
            <label>Cliente</label>
            <select name="user_id" onchange="this.form.submit()">
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo (int)$u['id']; ?>" <?php echo $userId === (int)$u['id'] ? 'selected' : ''; ?>><?php echo esc($u['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="panel" style="max-height:300px;overflow:auto;">
        <?php foreach ($messages as $m): ?>
            <p><strong><?php echo $m['sender_role'] === 'admin' ? 'Admin' : 'Cliente'; ?>:</strong> <?php echo esc($m['message']); ?> <small>(<?php echo esc($m['created_at']); ?>)</small></p>
        <?php endforeach; ?>
    </div>

    <?php if ($userId > 0): ?>
    <form method="post">
        <textarea name="message" rows="4" required></textarea>
        <button type="submit">Enviar</button>
    </form>
    <?php endif; ?>
</section>
