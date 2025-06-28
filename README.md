# Flood Report: Spatial Data Structure for Monitoring Flood Protection Infrastructure

![License](https://img.shields.io/badge/license-MIT-blue.svg)

## Overview

**Flood Report** is a web-based application developed as part of an engineering thesis titled:

**"Struktura danych przestrzennych do monitoringu wybranej infrastruktury przeciwpowodziowej – wały przeciwpowodziowe"**  
(*"Spatial Data Structure for Monitoring Selected Flood Protection Infrastructure – Flood Embankments"*)

This application aims to support local flood protection leaders and emergency services by providing a simple and effective tool to report and monitor issues related to man-made flood embankments.

## Features

- 📍 **Map-based interface** – Easily view, report, and locate issues on flood embankments.
- 📝 **Issue reporting form** – Submit incidents such as cracks, seepage, or erosion.
- 🗃️ **Spatial data integration** – Built on a geo-structured data model tailored for flood protection use cases.
- 📊 **Database storage** – Issues are stored with spatial and descriptive metadata.
- 🔐 **User roles (future)** – Intended support for verified local leaders or administrative users.

## Target Users

This tool is intended for:
- Local flood protection leaders (`społeczny nadzorca wałów`)
- Municipal flood response coordinators
- Engineering researchers or students studying geoinformatics or hydrology

## Tech Stack

- 🧭 Frontend: HTML, JavaScript, Leaflet.js
- 🛠 Backend: Node.js (Express)
- 🗂 Database: PostgreSQL + PostGIS
- 🗺️ Mapping: Leaflet with spatial data overlays

## How to Run Locally

### Prerequisites

- Node.js ≥ 14
- PostgreSQL with PostGIS enabled
- Git

### Installation

```bash
git clone https://github.com/wojtekSit/flood-report.git
cd flood-report
npm install

Database Setup
Create a PostgreSQL database.

Enable the PostGIS extension.

Run the SQL scripts provided in the /sql/ directory to set up the schema.

Update the database connection settings in the app’s configuration file.

Start the Server
bash
Copy
Edit
npm start
Open your browser at http://localhost:3000

Usage
Navigate the map to your local area.

Click on an embankment or use the form to report a new issue.

Fill in the details: type of issue, severity, and description.

Submit and track the report.

Screenshots

License
This project is licensed under the MIT License.

Author
Wojciech Sitko
Engineering Thesis – Wydział Geoinżynierii, Górnictwa i Geologii
PWR Wroclaw University of Science and Technology, Wrocław
Advisor: Dr Krzysztof Chudy

Acknowledgments
Leaflet.js for interactive maps

PostGIS for spatial data capabilities

PWR for academic support