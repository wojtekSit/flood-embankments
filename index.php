<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Flood Report</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    body { font-family: sans-serif; margin: 0; padding: 1rem; }
    .map { height: 300px; margin-bottom: 2rem; }
    .fullmap { height: 400px; }
    form { margin-bottom: 2rem; }
  </style>
</head>
<body>

  <h1>Zgłoś uszkodzenie wału</h1>

  <form id="reportForm">
    <label>Opis:<br>
      <textarea name="description" required></textarea>
    </label><br><br>

    <label>Kod użytkownika:<br>
      <input type="text" name="code" required>
    </label><br><br>

    <label>Zdjęcie (opcjonalnie):<br>
      <input type="file" name="photo" accept="image/*">
    </label><br><br>

    <label>Zaznacz miejsce zgłoszenia na mapie:</label>
    <div id="selectMap" class="map"></div>

    <input type="hidden" name="lat" id="lat">
    <input type="hidden" name="lng" id="lng">

    <button type="submit">Wyślij zgłoszenie</button>
    <p id="status"></p>
  </form>

  <h2>Aktualne zgłoszenia</h2>
  <div id="reportsMap" class="fullmap"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    let selectedLatLng = null;

    // sending the form to db
    const form = document.getElementById('reportForm');
    const statusEl = document.getElementById('status');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!selectedLatLng) {
        statusEl.textContent = "zaznacz punkt na mapie";
        return;
      }

      const formData = new FormData(form);
      formData.set('lat', selectedLatLng.lat);
      formData.set('lng', selectedLatLng.lng);

      try {
        const res = await fetch('api/report.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
          statusEl.textContent = "zgloszenie wyslane";
          form.reset();
          selectedLatLng = null;
          marker && selectMap.removeLayer(marker);
          loadReports();
        } else {
          statusEl.textContent = data.error || "blad przy zapisie";
        }
      } catch (err) {
        console.error(err);
        statusEl.textContent = "blad sieci";
      }
    });

    // map
    const selectMap = L.map('selectMap').setView([52.2297, 21.0122], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(selectMap);

    let marker;
    selectMap.on('click', (e) => {
      selectedLatLng = e.latlng;
      document.getElementById('lat').value = e.latlng.lat;
      document.getElementById('lng').value = e.latlng.lng;

      if (marker) marker.setLatLng(e.latlng);
      else marker = L.marker(e.latlng).addTo(selectMap);
    });

    // map of reports
    const reportsMap = L.map('reportsMap').setView([52.2297, 21.0122], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(reportsMap);

    async function loadReports() {
      const res = await fetch('api/get_reports.php');
      const reports = await res.json();
      reportsMap.eachLayer((l) => { if (l instanceof L.Marker) reportsMap.removeLayer(l); });
      reports.forEach(report => {
        const m = L.marker([report.lat, report.lng]).addTo(reportsMap);
        let popup = `<strong>${report.reporter_name}</strong><br>${report.description}<br>${report.report_time}`;
        if (report.photo_path) popup += `<br><img src="${report.photo_path}" alt="Zdjecie" style="width:150px">`;
        m.bindPopup(popup);
      });
    }

    loadReports();
  </script>

</body>
</html>
