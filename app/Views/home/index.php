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
            <label>Ordenar items</label>
            <select name="sort" id="sortItems">
                <option value="alphabetical" <?php echo $sort === 'alphabetical' ? 'selected' : ''; ?>>Alfabeticamente</option>
                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Precio mas bajo</option>
                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Precio mas alto</option>
            </select>
        </div>
        <div>
            <label>Total en carrito (<?php echo get_current_currency(); ?>)</label>
            <input type="text" value="<?php echo number_format(get_current_currency() === 'USD' ? $cartTotal / get_exchange_rate() : $cartTotal, 2, '.', ''); ?>" readonly onclick="window.location.href='<?php echo esc(route_url('cart')); ?>'" title="Haz clic para ver el carrito" style="cursor:pointer;">
        </div>
        <div>
            <button type="submit">Filtrar</button>
        </div>
        <div>
            <a class="btn btn-alt" href="<?php echo esc(route_url('cart')); ?>" style="display:block;text-align:center;padding:10px;">Ver carrito</a>
        </div>
    </form>
    <?php if (!empty($sellerId)): ?>
        <div class="panel" style="background:#fff6e8;border:1px solid #f0d7bb; margin-top:10px; padding:12px;">
            <strong>Comprando a través del vendedor #<?php echo (int)$sellerId; ?>.</strong>
            <p>El pedido quedará asociado a este vendedor si finalizas la compra.</p>
        </div>
    <?php endif; ?>
    <form method="post" style="margin-top:10px;">
        <button class="btn-warn" type="submit" name="clear_cart" value="1">Borrar items</button>
    </form>
    <div id="letterFilter" style="margin-top:14px;display:flex;flex-wrap:wrap;gap:8px;"></div>
</section>

<section class="grid" id="productGrid">
    <?php foreach ($products as $p): ?>
        <?php
        $images = array_filter(array_map('trim', explode('|', $p['image_path'])));
        if (count($images) === 0) {
            $images = array($p['image_path']);
        }
        $basePrice = isset($p['price_retail']) && $p['price_retail'] > 0 ? $p['price_retail'] : $p['price'];
        $modalPayload = array(
            'id' => (int)$p['id'],
            'images' => array_values($images),
            'price' => format_price($basePrice),
            'priceWholesale' => isset($p['price_wholesale']) && $p['price_wholesale'] > 0 ? format_price($p['price_wholesale']) : '',
            'showRetail' => isset($p['show_retail']) ? (bool)$p['show_retail'] : true,
            'showWholesale' => isset($p['show_wholesale']) ? (bool)$p['show_wholesale'] : false,
            'allowNegative' => isset($p['allow_negative_stock']) ? (bool)$p['allow_negative_stock'] : false,
            'stock' => (int)$p['stock'],
            'color' => $p['color'],
            'code' => $p['internal_code'],
            'name' => $p['name']
        );
        ?>
        <article class="card" data-name="<?php echo esc(mb_strtolower($p['name'], 'UTF-8')); ?>" data-price="<?php echo number_format((float)$basePrice, 2, '.', ''); ?>">
            <img src="<?php echo esc($images[0]); ?>" alt="<?php echo esc($p['name']); ?>" onclick='openProductModal(<?php echo json_encode($modalPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
            <div class="card-body">
                <h3><?php echo esc($p['name']); ?></h3>
                <p><?php echo esc($p['description']); ?></p>
                <p>Codigo: <?php echo esc($p['internal_code']); ?></p>
                <p class="stock">Stock: <?php echo (int)$p['stock']; ?></p>
                <?php if (!empty($p['show_retail'])): ?>
                    <p>Precio detal: <?php echo format_price($basePrice); ?></p>
                <?php endif; ?>
                <?php if (!empty($p['show_wholesale']) && !empty($p['price_wholesale'])): ?>
                    <p>Precio mayor: <?php echo format_price($p['price_wholesale']); ?></p>
                <?php endif; ?>
                <?php if (!empty($p['allow_negative_stock'])): ?>
                    <p style="color:#b45d13;">Permite venta en stock negativo</p>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                    <label>Cantidad</label>
                    <input type="number" min="1" value="1" name="qty">
                    <button type="submit" name="add_cart" value="1" style="margin-top:10px;margin-bottom:10px;">Agregar al carrito</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<p id="emptyLetterResult" style="display:none;margin:12px 0;color:#7a1f1f;font-weight:600;">No hay items disponibles para esa letra en esta pagina.</p>

<div class="pagination">
    <a href="<?php echo esc(route_url('home', array('page' => 1, 'category' => (int)$category, 'search' => $search, 'sort' => $sort))); ?>">Primera</a>
    <?php for ($i = 1; $i <= min(10, $totalPages); $i++): ?>
        <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="<?php echo esc(route_url('home', array('page' => $i, 'category' => (int)$category, 'search' => $search, 'sort' => $sort))); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <a href="<?php echo esc(route_url('home', array('page' => (int)$totalPages, 'category' => (int)$category, 'search' => $search, 'sort' => $sort))); ?>">Ultima</a>
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
        <div class="modal-img-box">
            <img id="mImg" src="" alt="Producto">
            <div class="modal-gallery" id="modalGallery" style="display:none;">
                <button type="button" onclick="nextModalImage(-1)">&larr;</button>
                <span id="modalImageIndex">1 / 1</span>
                <button type="button" onclick="nextModalImage(1)">&rarr;</button>
            </div>
            <div class="modal-thumbnails" id="modalThumbnails"></div>
        </div>
        <div class="modal-text">
            <h3>Detalle</h3>
            <p id="modalRetailPrice">Precio detal: VES <span id="mPrice"></span></p>
            <p id="modalWholesalePrice">Precio mayor: VES <span id="mPriceWholesale"></span></p>
            <p>Cantidad disponible: <span id="mQty"></span></p>
            <p>Color: <span id="mColor"></span></p>
            <hr>
            <p>Codigo: <span id="mCode"></span></p>
            <p>Nombre: <span id="mName"></span></p>
            <p>Stock: <span id="mStock2"></span></p>
            <form method="post" style="margin-top:12px;">
                <input type="hidden" id="mProductId" name="product_id" value="0">
                <div><label>Cantidad</label></div>
                <div><input id="mQtyInput" type="number" min="1" value="1" name="qty"></div>
                <div><button type="submit" name="add_cart" value="1" style="margin-top:10px;margin-bottom:10px;">Agregar al carrito</button></div>
            </form>
            <button class="btn-alt" onclick="closeProductModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
(function () {
    var sortSelect = document.getElementById('sortItems');
    var grid = document.getElementById('productGrid');
    var letterFilter = document.getElementById('letterFilter');
    var emptyResult = document.getElementById('emptyLetterResult');
    if (!sortSelect || !grid || !letterFilter) {
        return;
    }

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.card'));
    var activeLetter = 'all';

    function normalizeText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toUpperCase();
    }

    function getCardLetter(card) {
        var name = card.getAttribute('data-name') || '';
        var normalized = normalizeText(name);
        return normalized ? normalized.charAt(0) : '#';
    }

    function updatePaginationLinks() {
        var links = document.querySelectorAll('.pagination a');
        for (var i = 0; i < links.length; i++) {
            var url = new URL(links[i].href, window.location.origin);
            url.searchParams.set('sort', sortSelect.value);
            links[i].href = url.toString();
        }
    }

    function renderLetterButtons() {
        var letters = {};
        for (var i = 0; i < cards.length; i++) {
            letters[getCardLetter(cards[i])] = true;
        }

        var keys = Object.keys(letters).sort();
        var html = ['<button type="button" data-letter="all" style="padding:6px 10px;border:1px solid #d4d4d4;border-radius:999px;background:#fff;cursor:pointer;">Todos</button>'];
        for (var j = 0; j < keys.length; j++) {
            html.push('<button type="button" data-letter="' + keys[j] + '" style="padding:6px 10px;border:1px solid #d4d4d4;border-radius:999px;background:#fff;cursor:pointer;">' + keys[j] + '</button>');
        }
        letterFilter.innerHTML = html.join('');
        highlightActiveLetter();
    }

    function highlightActiveLetter() {
        var buttons = letterFilter.querySelectorAll('button');
        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i].getAttribute('data-letter') === activeLetter) {
                buttons[i].style.background = '#111';
                buttons[i].style.color = '#fff';
                buttons[i].style.borderColor = '#111';
            } else {
                buttons[i].style.background = '#fff';
                buttons[i].style.color = '#111';
                buttons[i].style.borderColor = '#d4d4d4';
            }
        }
    }

    function applyLetterFilter() {
        var visibleCount = 0;
        for (var i = 0; i < cards.length; i++) {
            var match = activeLetter === 'all' || getCardLetter(cards[i]) === activeLetter;
            cards[i].style.display = match ? '' : 'none';
            if (match) {
                visibleCount++;
            }
        }
        emptyResult.style.display = visibleCount === 0 ? 'block' : 'none';
        highlightActiveLetter();
    }

    function sortCards() {
        var sorted = cards.slice();
        sorted.sort(function (a, b) {
            var mode = sortSelect.value;
            if (mode === 'price_low') {
                return parseFloat(a.getAttribute('data-price') || '0') - parseFloat(b.getAttribute('data-price') || '0');
            }
            if (mode === 'price_high') {
                return parseFloat(b.getAttribute('data-price') || '0') - parseFloat(a.getAttribute('data-price') || '0');
            }
            return (a.getAttribute('data-name') || '').localeCompare(b.getAttribute('data-name') || '', 'es');
        });

        for (var i = 0; i < sorted.length; i++) {
            grid.appendChild(sorted[i]);
        }

        cards = sorted;
        renderLetterButtons();
        applyLetterFilter();

        var url = new URL(window.location.href);
        url.searchParams.set('sort', sortSelect.value);
        window.history.replaceState({}, '', url.toString());
        updatePaginationLinks();
    }

    sortSelect.addEventListener('change', sortCards);
    letterFilter.addEventListener('click', function (event) {
        var button = event.target;
        if (!button || button.tagName !== 'BUTTON') {
            return;
        }
        activeLetter = button.getAttribute('data-letter') || 'all';
        applyLetterFilter();
    });

    renderLetterButtons();
    applyLetterFilter();
    updatePaginationLinks();
}());
</script>
