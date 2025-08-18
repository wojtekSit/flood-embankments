# Flood Embankments

A Dockerized PHP application for reporting and tracking flood-embankment issues. Users can register, log in (after approval), and submit damage reports with GPS coordinates and photos. Admins can review and close reports.

> **Status:** Work in progress. No license file is currently present.

---

## ✨ Features

* **User accounts** with approval flow (admin must approve before login succeeds).
* **Report submissions** with:

  * Object type and issue type
  * GPS latitude/longitude (stored as a MySQL `POINT` with spatial index)
  * Photo upload path
  * Damage level (1-5)
  * Optional description
  * Open/closed status
* **Timestamps** for creation and updates on users and reports.

---

## 🧱 Tech Stack

* **PHP 8.2 + Apache** (containerized)
* **MySQL 8.0** with spatial features enabled
* **phpMyAdmin** for DB management
* **Docker Compose** for local development

---

## 🏁 Quick Start

### Prerequisites

* Docker & Docker Compose installed

### 1) Clone

```bash
git clone https://github.com/wojtekSit/flood-embankments.git
cd flood-embankments
```

### 2) Start the stack

```bash
docker compose up --build
```

This brings up three services:

* **App (PHP+Apache):** [http://localhost:8080](http://localhost:8080)
* **Database (MySQL 8):** localhost:3307 (mapped to container 3306)
* **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)

Default DB credentials (from Compose):

```
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=flood_monitor
MYSQL_USER=user
MYSQL_PASSWORD=pass
```

> If ports `8080`, `8081`, or `3307` are busy on your machine, edit `docker-compose.yml` before starting.

### 3) Initialize the database schema

Use **phpMyAdmin** → *Import* and upload `sql/init.sql` to create tables and indexes.

Tables created:

* `users`
* `reports`

> The schema uses a generated column `gps_point` and a SPATIAL index for fast geospatial queries.

### 4) Log in / Approvals

* Register a user in the app, then set `is_approved = 1` for that user via phpMyAdmin to enable login.
* Set `is_admin = 1` to grant admin privileges.

---

## 🗂️ Project Structure (high level)

```
.
├── admin/                 # (Admin views/actions – WIP)
├── config/                # App configuration (e.g., DB config)
├── includes/              # Shared PHP includes (DB, helpers, auth)
├── public/                # Public-facing PHP files (entry points)
├── sql/                   # Database scripts (init.sql)
├── uploads/               # Uploaded files (e.g., photos)
├── Dockerfile             # PHP 8.2 + Apache image with PDO MySQL
└── docker-compose.yml     # App, MySQL, phpMyAdmin services
```

> **Note:** Some directories may be placeholders until code is added.

---

## 🔧 Development

* The application code is mounted into the container (`.:/var/www/html`) for live-reload style editing.
* Apache’s `mod_rewrite` is enabled in the image for future routing flexibility.
* PHP extensions installed: `pdo`, `pdo_mysql`.

### Useful commands

Rebuild after changing Dockerfile:

```bash
docker compose build --no-cache php-apache
```

Connect to the app container shell:

```bash
docker compose exec php-apache bash
```

MySQL CLI inside DB container:

```bash
docker compose exec db mysql -uuser -ppass -Dflood_monitor
```

---

## 🔐 Security notes

* Never commit real production credentials.
* Consider adding `.env` support and Docker secrets.
* Validate and sanitize file uploads; enforce size/type limits.
* Use password hashing (`password_hash()` / `password_verify()` are expected in code) and HTTPS in production.

---

## 🗺️ Roadmap (suggested)

* [ ] Seed data and fixtures
* [ ] Admin dashboard for approvals & report management
* [ ] File storage hardening (unique names, size/type checks)
* [ ] Basic frontend styles and forms validation
* [ ] API endpoints for mobile/reporting clients
* [ ] Map view of reports (Leaflet/Mapbox) using spatial index
* [ ] Docker healthchecks and init scripts for auto-seeding
* [ ] Automated tests and CI workflow

---

## 📜 License

No license file provided. If you intend others to use/contribute, consider adding an open-source license (e.g., MIT, Apache-2.0).

---

## 🤝 Contributing

1. Fork the repo, create a feature branch
2. Commit with clear messages
3. Open a PR with a description and screenshots when relevant

---

## 🧪 Troubleshooting

* **Containers start but app 404s:** ensure the code lives in the repo root (mounted to `/var/www/html`).
* **SQL import errors:** ensure you’re importing into the `flood_monitor` DB and using MySQL 8.0; re-check `sql/init.sql`.
* **Port already in use:** change ports in `docker-compose.yml` and restart.
* **Login fails even with correct password:** make sure `is_approved = 1` for the user.

---

## 🙌 Acknowledgements

* MySQL spatial features (`POINT`, spatial indexes) for location-aware queries
* Docker for reproducible local environments
