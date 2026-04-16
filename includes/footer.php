</main>
<footer class="footer">
    <div class="container footer-grid">
        <section>
            <h3><?php echo esc(get_setting('company_name', 'Tu Tienda Online LT')); ?></h3>
            <p><?php echo esc(get_setting('company_address', 'Calle. Miquilen , Los teques, Venezuela')); ?></p>
        </section>
        <section>
            <h3>Contacto</h3>
            <p>Telefono: <?php echo esc(get_setting('company_phone', '+584120161515')); ?></p>
            <p>Correo: <?php echo esc(get_setting('company_email', 'tutiendaonline012@gmail.com')); ?></p>
        </section>
        <section>
            <h3>Redes Sociales</h3>
            <div class="socials">
                <!-- <a href="#" target="_blank">Facebook</a> -->
                <a href="http://instagram.com/tutiendaonlinelq" target="_blank">Instagram</a>
            </div>
            <div style="margin-top:10px;">
                <button type="button" class="btn" onclick="shareStoreLink()" style="width:auto;padding:8px 14px;">Compartir link</button>
            </div>
        </section>
    </div>
</footer>
<a class="floating whatsapp" href="https://wa.me/584120161515" target="_blank" title="WhatsApp" aria-label="Abrir WhatsApp">
   <i class="fab fa-whatsapp"></i>
<!--     <svg class="whatsapp-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M11.9 3.8c-4.5 0-8.1 3.5-8.1 7.8 0 1.5.5 3 1.4 4.2l-1.1 4 4.1-1.1c1.1.7 2.5 1 3.8 1 4.5 0 8.1-3.5 8.1-7.8s-3.6-8.1-8.2-8.1z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
        <path d="M9.5 8.3l1.2 1.7c.2.3.2.6-.1.8l-.6.6c.9 1.4 2 2.5 3.4 3.4l.6-.6c.2-.2.6-.3.8-.1l1.7 1.2c.3.2.4.6.2.9l-.8 1.2c-.2.3-.5.4-.8.4-3.9-.6-7-3.7-7.6-7.6-.1-.3.1-.6.4-.8l1.2-.8c.3-.2.7-.1.9.2z" fill="currentColor"></path>
    </svg> -->
</a>
<button class="floating up" onclick="backToTop()" title="Subir">↑</button>
<script>
function shareStoreLink() {
    var shareData = {
        title: document.title || 'Tu tienda On line',
        text: 'Mira esta tienda:',
        url: window.location.origin
    };

    if (navigator.share) {
        navigator.share(shareData).catch(function () {});
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareData.url).then(function () {
            alert('Link copiado: ' + shareData.url);
        }).catch(function () {
            window.prompt('Copia este link:', shareData.url);
        });
        return;
    }

    window.prompt('Copia este link:', shareData.url);
}
</script>
</body>
</html>
