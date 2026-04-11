<section class="panel">
    <h1>Categorias</h1>
    <form method="post" class="filters">
        <div><label>Nombre categoria</label><input type="text" name="name" required></div>
        <div><button type="submit">Agregar categoria</button></div>
    </form>
    <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin-top:10px;">
        <tr><th>ID</th><th>Categoria</th><th>Estado</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr><td><?php echo (int)$r['id']; ?></td><td><?php echo esc($r['name']); ?></td><td><?php echo (int)$r['is_active'] ? 'Activa' : 'Inactiva'; ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>
