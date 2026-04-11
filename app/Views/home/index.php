<section class="panel">
    <form method="get" class="filters" action="<?php echo esc(route_url('home')); ?>">
        <div>
            <label>Categoria</label>
            <select name="category">
                <option value="0">Ver todos</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo $category === (int)$c['id'] ? 'selected' : ''; ?>><?php echo esc($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Buscar palabra clave</label>
            <input type="text" name="search" value="<?php echo esc($search); ?>">
        </div>
        <div>
            <label>Total en carrito ($)</label>
            <input type="text" value="<?php echo number_format($cartTotal, 2, '.', ''); ?>" readonly onclick="window.location.href='<?php echo esc(route_url('cart')); ?>'" title="Haz clic para ver el carrito" style="cursor:pointer;">
        </div>
        <div>
            <button type="submit">Filtrar</button>
        </div>
        <div>
            <a class="btn btn-alt" href="<?php echo esc(route_url('cart')); ?>" style="display:block;text-align:center;padding:10px;">Ver carrito</a>
        </div>
    </form>
    <form method="post" style="margin-top:10px;">
        <button class="btn-warn" type="submit" name="clear_cart" value="1">Borrar items</button>
    </form>
</section>

<section class="grid">
    <?php foreach ($products as $p): ?>
        <article class="card">
            <img src="<?php echo esc($p['image_path']); ?>" alt="<?php echo esc($p['name']); ?>" onclick='openProductModal({"image":"<?php echo esc($p['image_path']); ?>","price":"<?php echo number_format($p['price'], 2, '.', ''); ?>","stock":"<?php echo (int)$p['stock']; ?>","color":"<?php echo esc($p['color']); ?>","code":"<?php echo esc($p['internal_code']); ?>","name":"<?php echo esc($p['name']); ?>"})'>
            <div class="card-body">
                <h3><?php echo esc($p['name']); ?></h3>
                <p><?php echo esc($p['description']); ?></p>
                <p>Codigo: <?php echo esc($p['internal_code']); ?></p>
                <p class="stock">Stock: <?php echo (int)$p['stock']; ?></p>
                <p>Precio: $<?php echo number_format($p['price'], 2); ?></p>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                    <label>Cantidad</label>
                    <input type="number" min="1" max="<?php echo (int)$p['stock']; ?>" value="1" name="qty">
                    <button type="submit" name="add_cart" value="1">Agregar al carrito</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<div class="pagination">
    <a href="<?php echo esc(route_url('home', array('page' => 1, 'category' => (int)$category, 'search' => $search))); ?>">Primera</a>
    <?php for ($i = 1; $i <= min(10, $totalPages); $i++): ?>
        <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="<?php echo esc(route_url('home', array('page' => $i, 'category' => (int)$category, 'search' => $search))); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <a href="<?php echo esc(route_url('home', array('page' => (int)$totalPages, 'category' => (int)$category, 'search' => $search))); ?>">Ultima</a>
</div>

<section class="panel">
    <h2>Testimonios</h2>
    <div class="testimonials">
        <blockquote class="quote">"Excelente atencion y entrega rapida" - Maria C.</blockquote>
        <blockquote class="quote">"Productos de calidad y buen precio" - Carlos R.</blockquote>
        <blockquote class="quote">"Compre por primera vez y volvere" - Andrea L.</blockquote>
    </div>
</section>

<section class="panel">
    <h2>Formulario de contacto</h2>
    <?php $contactStatus = isset($_GET['contact']) ? $_GET['contact'] : ''; ?>
    <?php if ($contactStatus === 'ok'): ?>
        <p style="color:#0a6e2f;font-weight:600;">Mensaje enviado correctamente.</p>
    <?php elseif ($contactStatus === 'captcha_error'): ?>
        <p style="color:#a12622;font-weight:600;">Error de captcha de Google. Verifica el captcha e intenta de nuevo.</p>
    <?php elseif ($contactStatus === 'missing_fields'): ?>
        <p style="color:#a12622;font-weight:600;">Completa todos los campos del formulario.</p>
    <?php endif; ?>
    <form method="post" action="<?php echo esc(route_url('send_contact')); ?>">
        <div class="filters">
            <div><label>Nombre</label><input type="text" name="name" required></div>
            <div><label>Correo</label><input type="email" name="email" required></div>
            <div style="grid-column:1/-1;"><label>Mensaje</label><textarea name="message" rows="5" required></textarea></div>
            <?php global $config; ?>
            <?php if (!empty($config['captcha_public'])): ?>
                <div style="grid-column:1/-1;">
                    <center>
                        <div style="display:inline-block;">
                            <div class="g-recaptcha" data-sitekey="<?php echo esc($config['captcha_public']); ?>"></div>
                        </div>
                    </center>
                </div>
            <?php else: ?>
                <div style="grid-column:1/-1;"><small style="color:#a12622;">Captcha no configurado (falta captcha_public).</small></div>
            <?php endif; ?>
            <div><button type="submit">Enviar contacto</button></div>
        </div>
    </form>
</section>

<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-img-box"><img id="mImg" src="" alt="Producto"></div>
        <div class="modal-text">
            <h3>Detalle</h3>
            <p>Precio: $<span id="mPrice"></span></p>
            <p>Cantidad disponible: <span id="mQty"></span></p>
            <p>Color: <span id="mColor"></span></p>
            <hr>
            <p>Codigo: <span id="mCode"></span></p>
            <p>Nombre: <span id="mName"></span></p>
            <p>Stock: <span id="mStock2"></span></p>
            <button class="btn-alt" onclick="closeProductModal()">Cerrar</button>
        </div>
    </div>
</div>
