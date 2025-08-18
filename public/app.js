(() => {
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('photo_preview');
    const img = document.getElementById('photo_img');
    const nameEl = document.getElementById('photo_name');

    if (photoInput && preview && img && nameEl) {
        photoInput.addEventListener('change', () => {
            const f = photoInput.files?.[0];
            if (!f) { preview.hidden = true; return; }
            const reader = new FileReader();
            reader.onload = () => { img.src = reader.result; nameEl.textContent = f.name; preview.hidden = false; };
            reader.readAsDataURL(f);
        });
    }

    let usingKeyboard = false;
    window.addEventListener('keydown', e => { if (e.key === 'Tab') usingKeyboard = true; });
    window.addEventListener('mousedown', () => { usingKeyboard = false; });
    document.addEventListener('focusin', e => {
        if (usingKeyboard && e.target.classList?.contains('input')) {
            e.target.style.boxShadow = '0 0 0 3px rgba(76,139,245,.35)';
            e.target.style.borderColor = '#4c8bf5';
        }
    });
    document.addEventListener('focusout', e => {
        if (e.target.classList?.contains('input')) {
            e.target.style.boxShadow = '';
            e.target.style.borderColor = '';
        }
    });
})();
// Drag-to-scroll dla .table-responsive (przewijanie także myszą/drag)
(() => {
    const el = document.querySelector('.table-responsive');
    if (!el) return;
    let isDown = false, startX = 0, scrollLeft = 0;

    el.addEventListener('mousedown', e => {
        isDown = true;
        startX = e.pageX - el.offsetLeft;
        scrollLeft = el.scrollLeft;
        el.classList.add('grabbing');
    });
    el.addEventListener('mouseleave', () => { isDown = false; el.classList.remove('grabbing'); });
    el.addEventListener('mouseup', () => { isDown = false; el.classList.remove('grabbing'); });
    el.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - el.offsetLeft;
        el.scrollLeft = scrollLeft - (x - startX);
    });
})();
