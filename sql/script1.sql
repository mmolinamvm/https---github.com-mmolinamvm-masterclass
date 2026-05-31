-- =====================================================================
-- SCRIPT GLOBAL DE RESET: USUARI, BASE DE DADES I CONFIGURACIÓ INICIAL
-- =====================================================================

-- 1. GESTIÓ DE L'USUARI DE L'APLICACIÓ (Entorn isolation de seguretat)
-- Esborrem l'usuari si ja existia per evitar errors de duplicat
DROP USER IF EXISTS 'masterclass_user'@'localhost';

-- Creem l'usuari amb autenticació clàssica per contrasenya (Nivell DWES)
CREATE USER 'masterclass_user'@'localhost' IDENTIFIED BY 'ContrasenyaSegura123!';

-- 2. GESTIÓ DE LA BASE DE DADES
DROP DATABASE IF EXISTS masterclass_db;
CREATE DATABASE masterclass_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Assignem tots els privilegis a l'usuari web ÚNICAMENT sobre aquesta base de dades
GRANT ALL PRIVILEGES ON masterclass_db.* TO 'masterclass_user'@'localhost';
FLUSH PRIVILEGES;

-- Ens situem al context de la base de dades creada per executar el DDL de les taules
USE masterclass_db;

-- =====================================================================
-- 3. CREACIÓ DE L'ESTRUCTURA DE TAULES (DDL)
-- =====================================================================

-- Taula principal de Vídeos
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codi_youtube VARCHAR(50) NOT NULL,
    titol VARCHAR(255) NOT NULL,
    descripcio TEXT,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Taula de Preguntes vinculades a un vídeo
CREATE TABLE preguntes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    segon INT NOT NULL,
    tipus ENUM('text', 'single', 'multiple') NOT NULL,
    text_pregunta TEXT NOT NULL,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula d'Opcions per a preguntes de tipus 'single' o 'multiple'
CREATE TABLE opcions_pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    text_opcio VARCHAR(255) NOT NULL,
    es_correcta TINYINT(1) DEFAULT 0,
    FOREIGN KEY (pregunta_id) REFERENCES preguntes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taula on es persistiran les respostes dels alumnes
CREATE TABLE respostes_alumnes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    alumne_id INT NOT NULL,
    resposta_text TEXT NULL,
    opcio_seleccionada_id INT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pregunta_id) REFERENCES preguntes(id) ON DELETE CASCADE,
    FOREIGN KEY (opcio_seleccionada_id) REFERENCES opcions_pregunta(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- 4. INSERCIÓ DE DADES DE PROVA (Seeders)
-- =====================================================================

-- Inserim el vídeo de la teva unitat de medi natural
-- 2. Injectem el vídeo actual per no perdre les dades
INSERT INTO videos (id, codi_youtube, titol, descripcio) 
VALUES (1, 'Oe2tzG4vI0o', 'Masterclass MP4 OI UF4 S1', 'Introducció a les normatives de protecció del medi natural');

-- 3. Modifiquem la taula preguntes per afegir la FK cap a vídeos
ALTER TABLE preguntes ADD COLUMN video_id INT AFTER id;

-- 4. Assignem totes les teves preguntes actuals al vídeo 1
UPDATE preguntes SET video_id = 1;

-- 5. Fem la columna FK NOT NULL i creem la relació formal
ALTER TABLE preguntes MODIFY COLUMN video_id INT NOT NULL;
ALTER TABLE preguntes ADD CONSTRAINT fk_preguntes_videos FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE;

-- Inserim les 3 preguntes reals
INSERT INTO preguntes (id, video_id, segon, tipus, text_pregunta) VALUES 
(1, 1, 10, 'text', 'Quins tres impactes de la industrialització es veuen al mapa?'),
(2, 1, 25, 'single', 'Quina d''aquestes normatives és la principal per al medi natural?'),
(3, 1, 40, 'multiple', 'Quins recursos es consideren exhauribles segons l''autor?');

-- Pregunta 2 (Opció única): Forcem IDs de l'1 al 3
INSERT INTO opcions_pregunta (id, pregunta_id, text_opcio, es_correcta) VALUES 
(1, 2, 'Llei de l''Aigua 1985', 0),
(2, 2, 'Directiva Hàbitats 1992', 1), -- La correcta
(3, 2, 'Conveni de París', 0);

-- Pregunta 3 (Opció múltiple): Forcem IDs del 4 al 6
INSERT INTO opcions_pregunta (id, pregunta_id, text_opcio, es_correcta) VALUES 
(4, 3, 'Combustibles fòssils', 1), -- Correcta
(5, 3, 'Energia solar', 0),
(6, 3, 'Mineria de terres rares', 1); -- Correcta