# Flood Report: Spatial Data Structure for Monitoring Flood Protection Infrastructure

![License](https://img.shields.io/badge/license-MIT-blue.svg)

## Overview

**Flood Report** is a lightweight web-based application developed as part of an engineering thesis:

**"Struktura danych przestrzennych do monitoringu wybranej infrastruktury przeciwpowodziowej – wały przeciwpowodziowe"**  
(*"Spatial Data Structure for Monitoring Selected Flood Protection Infrastructure – Flood Embankments"*)

This application helps local flood protection leaders and emergency services report and monitor issues related to flood embankments through a simple, map-based interface.

## Features

- 📍 **Interactive map** – View and pinpoint issues directly on the map.
- 📝 **Issue reporting form** – Report incidents such as cracks, seepage, or erosion.
- 🗃️ **Spatial data storage** – Each report includes precise geolocation and descriptive details.
- 💾 **File uploads** – Attach photos to reports for better documentation.

## Target Users

This tool is intended for:
- Local flood protection leaders (`społeczny nadzorca wałów`)
- Municipal flood response coordinators
- Engineering students or researchers in geoinformatics and hydrology

## Tech Stack

- 🌐 Frontend: HTML, CSS, JavaScript (Leaflet.js)
- 🛠 Backend: PHP (simple REST API)
- 🗂 Database: MySQL with spatial data types (POINT)
- 🗺️ Mapping: Leaflet with OpenStreetMap tiles

## How to Run Locally

### Prerequisites

- PHP (e.g., via XAMPP, MAMP, WAMP)
- MySQL/MariaDB
- Web browser (modern)

### Installation & Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/wojtekSit/flood-embankments.git
   cd flood-embankments

2. Place the project in your web server directory (e.g., C:\xampp\htdocs\flood-embankments).

3. Create a MySQL database (e.g., flood_db) and run the SQL scripts to set up tables:
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  code_hash VARCHAR(255) NOT NULL
);

CREATE TABLE flood_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  description TEXT,
  coordinates POINT NOT NULL,
  photo_path VARCHAR(255),
  report_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  SPATIAL INDEX(coordinates)
);
4. Add at least one user with a code, e.g. (replace hash with one generated using PHP’s password_hash):

INSERT INTO users (name, code_hash) VALUES ('Jan Kowalski', 'YOUR_HASH_HERE');

5. Update your database connection in api/db.php:

$host = '127.0.0.1';
$dbname = 'flood_db';
$user = 'root';  // or your DB user
$pass = '';      // or your DB password

6. Start Apache and MySQL E.G. in XAMPP

7. Open your browser and go to 

http://localhost/flood-embankments/index.php

Usage
Navigate the interactive map to your area.

Click on the map to set the location of the issue.

Fill in the issue description, your user code, and optionally attach a photo.

Submit the report.

Existing reports are shown on the second map with details and photos.

License
This project is licensed under the MIT License.

Author
Wojciech Sitko
Engineering Thesis – Faculty of Geoengineering, Mining and Geology
Wrocław University of Science and Technology
Advisor: Dr. Krzysztof Chudy

Acknowledgments
Leaflet.js for interactive maps

OpenStreetMap contributors for map tiles

PHP and MySQL for the backend infrastructure

yaml
Copy
Edit
