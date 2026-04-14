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
        </section>
    </div>
</footer>
<a class="floating whatsapp" href="https://wa.me/584120161515" target="_blank" title="WhatsApp">W</a>
<button class="floating up" onclick="backToTop()" title="Subir">↑</button>
</body>
</html>
