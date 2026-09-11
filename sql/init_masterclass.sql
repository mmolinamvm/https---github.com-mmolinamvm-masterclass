-- =====================================================================
-- MASTERCLASS - SCRIPT DE INICIALITZACIÓ CONSOLIDAT I CORREGIT
-- =====================================================================
-- Aquest script es pot executar amb l'usuari myguiamanu (ja creat
-- amb permisos sobre guiamanudb). No conté CREATE USER/DATABASE
-- perquè requereixen privilegis d'administrador.
--
-- Ús:
--   mysql -u myguiamanu -p < sql/init_masterclass.sql
-- =====================================================================

USE guiamanudb;

-- =====================================================================
-- 1. ESBORRAR TAULES EXISTENTS (endreçat per les FK)
-- =====================================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS mc_respostes_alumnes;
DROP TABLE IF EXISTS mc_opcions_pregunta;
DROP TABLE IF EXISTS mc_preguntes;
DROP TABLE IF EXISTS mc_videos;
DROP TABLE IF EXISTS mc_usuari_videos;
DROP TABLE IF EXISTS mc_usuaris;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 2. CREACIÓ DE L'ESTRUCTURA DE TAULES (DDL)
-- =====================================================================

-- Taula principal de Vídeos
CREATE TABLE mc_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codi_youtube VARCHAR(50) NOT NULL,
    titol VARCHAR(255) NOT NULL,
    descripcio TEXT NULL,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula de Preguntes vinculades a un vídeo
CREATE TABLE mc_preguntes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    segon INT NOT NULL,
    tipus ENUM('text', 'single', 'multiple') NOT NULL,
    text_pregunta TEXT NOT NULL,
    CONSTRAINT fk_preguntes_videos
        FOREIGN KEY (video_id) REFERENCES mc_videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula d'Opcions per a preguntes de tipus 'single' o 'multiple'
CREATE TABLE mc_opcions_pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    text_opcio VARCHAR(255) NOT NULL,
    es_correcta TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_opcions_pregunta
        FOREIGN KEY (pregunta_id) REFERENCES mc_preguntes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula on es persistiran les respostes dels alumnes
CREATE TABLE mc_respostes_alumnes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    alumne_id INT NOT NULL,
    resposta_text TEXT NULL,
    opcio_seleccionada_id INT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respostes_pregunta
        FOREIGN KEY (pregunta_id) REFERENCES mc_preguntes(id) ON DELETE CASCADE,
    CONSTRAINT fk_respostes_opcio
        FOREIGN KEY (opcio_seleccionada_id) REFERENCES mc_opcions_pregunta(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula d'Usuaris (alumnes i professors)
CREATE TABLE mc_usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    cognoms VARCHAR(100) NOT NULL,
    rol ENUM('alumne', 'professor') DEFAULT 'alumne',
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula de relació Usuari - Vídeo (assignacions amb restriccions)
CREATE TABLE mc_usuari_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuari_id INT NOT NULL,
    video_id INT NOT NULL,
    estat ENUM('pendent', 'vist', 'completat') DEFAULT 'pendent',
    reproduccions_restants INT DEFAULT 3,
    data_limit DATETIME NULL,
    data_completat TIMESTAMP NULL,
    CONSTRAINT fk_uv_usuari
        FOREIGN KEY (usuari_id) REFERENCES mc_usuaris(id) ON DELETE CASCADE,
    CONSTRAINT fk_uv_video
        FOREIGN KEY (video_id) REFERENCES mc_videos(id) ON DELETE CASCADE,
    UNIQUE KEY usaqui_video_unic (usuari_id, video_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. INSERCIÓ DE DADES DE PROVA (Seeders)
-- =====================================================================

-- Vídeos
INSERT INTO mc_videos (id, codi_youtube, titol, descripcio) VALUES
(1, 'Oe2tzG4vI0o', 'Masterclass MP4 OI UF4 S1', 'Introducció a les normatives de protecció del medi natural'),
(2, 'FgG8NSmc5Tg', 'Masterclass Perfil topogràfic', 'Com es fa un perfil topogràfic');

-- Preguntes
INSERT INTO mc_preguntes (id, video_id, segon, tipus, text_pregunta) VALUES
(1, 1, 10, 'text',     'Quins tres impactes de la industrialització es veuen al mapa?'),
(2, 1, 25, 'single',   'Quina d''aquestes normatives és la principal per al medi natural?'),
(3, 1, 40, 'multiple', 'Quins recursos es consideren exhauribles segons l''autor?'),
(4, 2, 10, 'text',     'Quin és el primer pas per fer un perfil topogràfic?'),
(5, 2, 25, 'single',   'Quina fulla especial hem de fer servir per fer un perfil topogràfic?'),
(6, 2, 40, 'multiple', 'Quins estris necessitem per fer un perfil topogràfic?');

-- Opcions (Pregunta 2: single; Pregunta 3: multiple; Pregunta 5: single; Pregunta 6: multiple)
INSERT INTO mc_opcions_pregunta (id, pregunta_id, text_opcio, es_correcta) VALUES
(1,  2, 'Llei de l''Aigua 1985', 0),
(2,  2, 'Directiva Hàbitats 1992', 1),
(3,  2, 'Conveni de París', 0),
(4,  3, 'Combustibles fòssils', 1),
(5,  3, 'Energia solar', 0),
(6,  3, 'Mineria de terres rares', 1),
(7,  5, 'Fulla milimetrada', 1),
(8,  5, 'Fulla de paper de ceba', 0),
(9,  5, 'Fulla quadriculada', 0),
(10, 6, 'Regle', 1),
(11, 6, 'Fulla milimetrada', 1),
(12, 6, 'Lupa', 1),
(13, 6, 'Compàs', 0);

-- Usuaris de prova (hashes generats amb password_hash() de PHP)
--   alumne de prova:  alumne@masterclass.com   / alumne123
--   professor prova:  profe@masterclass.com    / profe123
INSERT INTO mc_usuaris (username, email, password_hash, nom, cognoms, rol) VALUES
('alumne1', 'alumne@masterclass.com', '$2y$10$29Zw0/fAkK4kVMKnbW5TJ.lx7xo9dJ63ft.U1nZRRD/a3njwKFkky', 'Joan',   'Garcia', 'alumne'),
('profe1',  'profe@masterclass.com',  '$2y$10$odFNO9sb9MvUfkpg4dO7dO/fAQepILR2ZIvkGgpWWhqGzF0dk53Lq', 'Marta',  'Prats',  'professor');

-- Assignacions: vídeo 1 assignat a l'alumne (usuari_id = 1)
INSERT INTO mc_usuari_videos (usuari_id, video_id, estat, reproduccions_restants, data_limit)
VALUES (1, 1, 'pendent', 3, '2026-12-31 23:59:59');