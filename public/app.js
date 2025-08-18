/* Minimal JS: map wiring, geolocation, file preview, basic guardrails */
(() => {
    const qs = (s, root = document) => root.querySelector(s);
    const mapEl = qs('#map');
    const gpsLatEl = qs('#gps_lat');
    const gpsLngEl = qs('#gps_lng');
    const coordsDisplay = qs('#coords_display');
    const btnGeo = qs('#btnGeo');
    const form = qs('#reportForm');
    const photoInput = qs('#photo');
    const photoPreview = qs('#photo_preview');
    const photoImg = qs('#photo_img');
    const photoName = qs('#photo_name');

    // Leaflet map (kept as the sole dependency)
    let map, marker;

    function setMarker(lat, lng) {
        if (!marker) {
            marker = L.marker([lat, lng]).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
        }
    }

    function setCoords(lat, lng, label) {
        gpsLatEl.value = lat;
        gpsLngEl.value = lng;
        coordsDisplay.textContent = `${label}: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    function initMap() {
        if (!mapEl) return;
        map = L.map('map', { zoomControl: false }).setView([52.0, 19.0], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

        map.on('click', e => {
            const lat = +e.latlng.lat;
            const lng = +e.latlng.lng;
            setMarker(lat, lng);
            setCoords(lat, lng, '📍 Wybrana lokalizacja');
        });
    }

    function getLocation() {
        if (!navigator.geolocation) {
            alert("Geolokalizacja nie jest wspierana przez tę przeglądarkę.");
            return;
        }
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = +pos.coords.latitude;
            const lng = +pos.coords.longitude;
            setMarker(lat, lng);
            setCoords(lat, lng, '📍 Twoja lokalizacja');
            map && map.setView([lat, lng], 15);
        }, err => {
            alert("Błąd pobierania lokalizacji: " + err.message);
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    function previewFile() {
        const f = photoInput.files?.[0];
        if (!f) { photoPreview.hidden = true; return; }
        const ok = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(f.type);
        if (!ok) { alert('Nieprawidłowy format zdjęcia.'); photoInput.value = ''; photoPreview.hidden = true; return; }
        if (f.size > 6 * 1024 * 1024) { alert('Plik jest zbyt duży (max 6MB).'); photoInput.value = ''; photoPreview.hidden = true; return; }
        const reader = new FileReader();
        reader.onload = () => { photoImg.src = reader.result; photoName.textContent = f.name; photoPreview.hidden = false; };
        reader.readAsDataURL(f);
    }

    function validateBeforeSubmit(e) {
        const lat = gpsLatEl.value;
        const lng = gpsLngEl.value;
        if (!lat || !lng) {
            e.preventDefault();
            alert("Musisz wybrać lokalizację (kliknij na mapie lub użyj przycisku geolokalizacji).");
        }
    }

    // wire up
    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        btnGeo && btnGeo.addEventListener('click', getLocation);
        photoInput && photoInput.addEventListener('change', previewFile);
        form && form.addEventListener('submit', validateBeforeSubmit);
    });
})();
