CREATE DATABASE IF NOT EXISTS acerte_a_toupeira
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE acerte_a_toupeira;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    birth_date DATE NOT NULL,
    cpf CHAR(11) NOT NULL,
    phone VARCHAR(11) NOT NULL,
    email VARCHAR(254) NOT NULL,
    username VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_cpf (cpf)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    rows_count TINYINT UNSIGNED NOT NULL,
    columns_count TINYINT UNSIGNED NOT NULL,
    mode ENUM('classica', 'explosiva') NOT NULL,
    score INT NOT NULL,
    level_reached INT UNSIGNED NOT NULL,
    total_hits INT UNSIGNED NOT NULL,
    played_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_games_user_date (user_id, played_at),
    KEY ix_games_ranking (mode, score, level_reached, total_hits),
    CONSTRAINT fk_games_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
