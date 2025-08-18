-- Ustawienia podstawowe
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- Użytkownicy
CREATE TABLE users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)      NOT NULL,
    surname      VARCHAR(100)      NOT NULL,
    email        VARCHAR(150)      NOT NULL UNIQUE,
    phone        VARCHAR(20),
    password     VARCHAR(255)      NOT NULL,        -- hash z password_hash()
    is_approved  BOOLEAN           NOT NULL DEFAULT FALSE,
    is_admin     BOOLEAN           NOT NULL DEFAULT FALSE,
    created_at   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Zgłoszenia
CREATE TABLE reports (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT             NOT NULL,
    object_type   VARCHAR(100)    NOT NULL,
    issue_type    VARCHAR(100)    NOT NULL,
    gps_lat       DOUBLE          NOT NULL,
    gps_lng       DOUBLE          NOT NULL,
    gps_point POINT NOT NULL SRID 4326,
    photo         VARCHAR(255)    NOT NULL,
    damage_level  TINYINT         NOT NULL,         
    description   TEXT,
    is_closed     BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,

    -- Indeksy
    INDEX idx_reports_user (user_id),
    INDEX idx_reports_created_at (created_at),
    SPATIAL INDEX idx_reports_gps_point (gps_point)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
