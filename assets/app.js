function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Ocultar';
    } else {
        input.type = 'password';
        btn.innerHTML = 'Ver';
    }
}

function createSessionWarningBanner(message, onContinue) {
    var existing = document.getElementById('sessionTimeoutWarning');
    if (existing) {
        return existing;
    }

    var banner = document.createElement('div');
    banner.id = 'sessionTimeoutWarning';
    banner.className = 'session-warning-banner';

    var text = document.createElement('p');
    text.textContent = message;
    banner.appendChild(text);

    var button = document.createElement('button');
    button.type = 'button';
    button.textContent = 'Continuar conectado';
    button.addEventListener('click', onContinue);
    banner.appendChild(button);

    document.body.appendChild(banner);
    return banner;
}

function removeSessionWarningBanner() {
    var existing = document.getElementById('sessionTimeoutWarning');
    if (existing) {
        existing.parentNode.removeChild(existing);
    }
}

function initSessionTimeout(config) {
    if (!config || !config.logoutUrl) {
        return;
    }

    var timeoutMs = parseInt(config.timeoutMs, 10) || 600000;
    var warningMs = parseInt(config.warningMs, 10) || 60000;
    var logoutUrl = config.logoutUrl;
    var warningTimer = null;
    var logoutTimer = null;

    function scheduleTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);

        warningTimer = setTimeout(function () {
            createSessionWarningBanner('Tu sesión se cerrará en 1 minuto por inactividad.', function () {
                resetTimers();
            });
        }, Math.max(0, timeoutMs - warningMs));

        logoutTimer = setTimeout(function () {
            window.location.href = logoutUrl + '?timeout=1';
        }, timeoutMs);
    }

    function resetTimers() {
        removeSessionWarningBanner();
        scheduleTimers();
    }

    var activityHandler = function () {
        resetTimers();
    };

    ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll'].forEach(function (eventName) {
        window.addEventListener(eventName, activityHandler);
    });

    scheduleTimers();
}

var modalImages = [];

document.addEventListener('DOMContentLoaded', function () {
    if (window.sessionTimeoutConfig) {
        initSessionTimeout(window.sessionTimeoutConfig);
    }
});
var currentModalImageIndex = 0;

function openProductModal(payload) {
    var modal = document.getElementById('productModal');
    if (!modal) return;

    modalImages = Array.isArray(payload.images) ? payload.images : [payload.image];
    currentModalImageIndex = 0;

    setModalImage(currentModalImageIndex);
    document.getElementById('mPrice').innerHTML = payload.price;
    document.getElementById('mPriceWholesale').innerHTML = payload.priceWholesale || payload.price;
    document.getElementById('mQty').innerHTML = payload.stock;
    document.getElementById('mColor').innerHTML = payload.color;
    document.getElementById('mCode').innerHTML = payload.code;
    document.getElementById('mName').innerHTML = payload.name;
    document.getElementById('mStock2').innerHTML = payload.stock;
    document.getElementById('mProductId').value = payload.id;
    document.getElementById('mQtyInput').value = 1;

    var qtyField = document.getElementById('mQtyInput');
    var maxQty = payload.allowNegative ? 9999 : payload.stock;
    qtyField.max = maxQty > 0 ? maxQty : 9999;

    var gallery = document.getElementById('modalGallery');
    if (modalImages.length > 1) {
        gallery.style.display = 'flex';
        renderModalThumbnails();
    } else {
        gallery.style.display = 'none';
    }

    var wholesaleSection = document.getElementById('modalWholesalePrice');
    if (payload.showWholesale && payload.priceWholesale) {
        wholesaleSection.style.display = 'block';
    } else {
        wholesaleSection.style.display = 'none';
    }

    var retailSection = document.getElementById('modalRetailPrice');
    if (payload.showRetail) {
        retailSection.style.display = 'block';
    } else {
        retailSection.style.display = 'none';
    }

    modal.style.display = 'block';
}

function setModalImage(index) {
    var image = document.getElementById('mImg');
    var current = document.getElementById('modalImageIndex');
    if (!image || !modalImages.length) return;

    index = ((index % modalImages.length) + modalImages.length) % modalImages.length;
    currentModalImageIndex = index;
    image.src = modalImages[index];
    if (current) {
        current.innerText = (index + 1) + ' / ' + modalImages.length;
    }
    document.querySelectorAll('.modal-thumbnails img').forEach(function (thumb, thumbIndex) {
        thumb.classList.toggle('active', thumbIndex === index);
    });
}

function renderModalThumbnails() {
    var thumbnailsContainer = document.getElementById('modalThumbnails');
    if (!thumbnailsContainer) return;
    thumbnailsContainer.innerHTML = '';

    modalImages.forEach(function (src, index) {
        var img = document.createElement('img');
        img.src = src;
        img.alt = 'Vista ' + (index + 1);
        img.className = index === currentModalImageIndex ? 'active' : '';
        img.onclick = function () {
            setModalImage(index);
        };
        thumbnailsContainer.appendChild(img);
    });
}

function nextModalImage(direction) {
    setModalImage(currentModalImageIndex + direction);
}

function closeProductModal() {
    var modal = document.getElementById('productModal');
    if (modal) modal.style.display = 'none';
}

window.onclick = function (event) {
    var modal = document.getElementById('productModal');
    if (modal && event.target === modal) {
        closeProductModal();
    }
};

window.addEventListener('DOMContentLoaded', function () {
    var img = document.getElementById('mImg');
    if (!img) return;
    img.addEventListener('mousemove', function (event) {
        var rect = img.getBoundingClientRect();
        var x = ((event.clientX - rect.left) / rect.width) * 100;
        var y = ((event.clientY - rect.top) / rect.height) * 100;
        img.style.transformOrigin = x + '% ' + y + '%';
    });
    img.addEventListener('mouseleave', function () {
        img.style.transformOrigin = 'center center';
    });
});

function backToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
