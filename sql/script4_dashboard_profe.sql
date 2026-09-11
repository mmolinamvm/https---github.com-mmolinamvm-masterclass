-- =====================================================================
-- MASTERCLASS - SCRIPT 4: DASHBOARD DEL PROFESSOR (MIGRACIÓ)
-- =====================================================================
-- Afegeix les taules de grups, assignacions de vídeos i sessions de
-- visualització per al mòdul de Dashboard del Professor.
--
-- IDEMPOTENT: es pot executar diverses vegades sense errors.
--
-- Ús:
--   mysql -u myguiamanu -p guiamanudb < sql/script4_dashboard_profe.sql
-- =====================================================================

USE guiamanudb;

-- =====================================================================
-- 1. NOVES TAULES
-- =====================================================================

-- Grups de classe creats per un professor
CREATE TABLE IF NOT EXISTS mc_grups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    codi_uni VARCHAR(10) NOT NULL UNIQUE,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_grups_professor
        FOREIGN KEY (professor_id) REFERENCES mc_usuaris(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relació M:N entre grups i alumnes
CREATE TABLE IF NOT EXISTS mc_grups_alumnes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grup_id INT NOT NULL,
    alumne_id INT NOT NULL,
    data_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ga_grup
        FOREIGN KEY (grup_id) REFERENCES mc_grups(id) ON DELETE CASCADE,
    CONSTRAINT fk_ga_alumne
        FOREIGN KEY (alumne_id) REFERENCES mc_usuaris(id) ON DELETE CASCADE,
    UNIQUE KEY grup_alumne_unic (grup_id, alumne_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assignacions de vídeos a grups complets o a alumnes individuals
CREATE TABLE IF NOT EXISTS mc_assignacions_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    grup_id INT NULL,
    alumne_id INT NULL,
    disponible_desde DATETIME NULL,
    disponible_fins DATETIME NULL,
    max_visualitzacions INT DEFAULT 3,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_av_video
        FOREIGN KEY (video_id) REFERENCES mc_videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_av_grup
        FOREIGN KEY (grup_id) REFERENCES mc_grups(id) ON DELETE CASCADE,
    CONSTRAINT fk_av_alumne
        FOREIGN KEY (alumne_id) REFERENCES mc_usuaris(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions de visualització (cada intent de reproducció de l'alumne)
CREATE TABLE IF NOT EXISTS mc_sessions_visualitzacio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignacio_id INT NOT NULL,
    alumne_id INT NOT NULL,
    numero_visualitzacio INT NOT NULL DEFAULT 1,
    iniciada_a TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    max_segon_visionat INT DEFAULT 0,
    completada TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_sv_assignacio
        FOREIGN KEY (assignacio_id) REFERENCES mc_assignacions_videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_sv_alumne
        FOREIGN KEY (alumne_id) REFERENCES mc_usuaris(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. ALTERS DE TAULES EXISTENTS (idempotents)
-- =====================================================================

-- ---------------------------------------------------------------
-- mc_videos: afegir professor_id (propietari del vídeo)
-- ---------------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA='guiamanudb' AND TABLE_NAME='mc_videos'
              AND COLUMN_NAME='professor_id');
SET @sql = IF(@col = 0,
    'ALTER TABLE mc_videos ADD COLUMN professor_id INT NULL AFTER id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- mc_videos: afegir youtube_url (URL original enganxada pel profe)
-- ---------------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA='guiamanudb' AND TABLE_NAME='mc_videos'
              AND COLUMN_NAME='youtube_url');
SET @sql = IF(@col = 0,
    'ALTER TABLE mc_videos ADD COLUMN youtube_url VARCHAR(255) NULL AFTER codi_youtube',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- mc_videos: FK professor_id -> mc_usuaris
-- ---------------------------------------------------------------
SET @fk = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
           WHERE TABLE_SCHEMA='guiamanudb' AND TABLE_NAME='mc_videos'
             AND CONSTRAINT_NAME='fk_videos_professor');
SET @sql = IF(@fk = 0,
    'ALTER TABLE mc_videos ADD CONSTRAINT fk_videos_professor
        FOREIGN KEY (professor_id) REFERENCES mc_usuaris(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Assignar els vídeos existents al primer professor de la BD
UPDATE mc_videos
SET professor_id = (SELECT id FROM mc_usuaris WHERE rol = 'professor' ORDER BY id LIMIT 1)
WHERE professor_id IS NULL;

-- ---------------------------------------------------------------
-- mc_respostes_alumnes: afegir sessio_id (vincular resposta a intent)
-- ---------------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA='guiamanudb' AND TABLE_NAME='mc_respostes_alumnes'
              AND COLUMN_NAME='sessio_id');
SET @sql = IF(@col = 0,
    'ALTER TABLE mc_respostes_alumnes ADD COLUMN sessio_id INT NULL AFTER alumne_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- mc_respostes_alumnes: FK sessio_id -> mc_sessions_visualitzacio
-- ---------------------------------------------------------------
SET @fk = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
           WHERE TABLE_SCHEMA='guiamanudb' AND TABLE_NAME='mc_respostes_alumnes'
             AND CONSTRAINT_NAME='fk_respostes_sessio');
SET @sql = IF(@fk = 0,
    'ALTER TABLE mc_respostes_alumnes ADD CONSTRAINT fk_respostes_sessio
        FOREIGN KEY (sessio_id) REFERENCES mc_sessions_visualitzacio(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- =====================================================================
-- 3. RESUM FINAL
-- =====================================================================
SELECT 'Taules noves creades correctament' AS resultat
UNION ALL
SELECT CONCAT('mc_grups: ', COUNT(*)) FROM mc_grups
UNION ALL
SELECT CONCAT('mc_grups_alumnes: ', COUNT(*)) FROM mc_grups_alumnes
UNION ALL
SELECT CONCAT('mc_assignacions_videos: ', COUNT(*)) FROM mc_assignacions_videos
UNION ALL
SELECT CONCAT('mc_sessions_visualitzacio: ', COUNT(*)) FROM mc_sessions_visualitzacio;