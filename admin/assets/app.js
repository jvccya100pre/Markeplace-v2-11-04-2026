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

function openProductModal(payload) {
    var modal = document.getElementById('productModal');
    if (!modal) return;
    document.getElementById('mImg').src = payload.image;
    document.getElementById('mPrice').innerHTML = payload.price;
    document.getElementById('mQty').innerHTML = payload.stock;
    document.getElementById('mColor').innerHTML = payload.color;
    document.getElementById('mCode').innerHTML = payload.code;
    document.getElementById('mName').innerHTML = payload.name;
    document.getElementById('mStock2').innerHTML = payload.stock;
    modal.style.display = 'block';
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

function backToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
