-- 1. Creem la taula de vídeos
DROP TABLE IF EXISTS videos;

CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codi_youtube VARCHAR(50) NOT NULL,
    titol VARCHAR(255) NOT NULL,
    descripcio TEXT,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Injectem el teu vídeo actual per no perdre les dades
INSERT INTO videos (id, codi_youtube, titol, descripcio) 
VALUES (1, 'Oe2tzG4vI0o', 'Masterclass MP4 OI UF4 S1', 'Introducció a les normatives de protecció del medi natural');

-- 3. Modifiquem la taula preguntes per afegir la FK cap a vídeos
ALTER TABLE preguntes ADD COLUMN video_id INT AFTER id;

-- 4. Assignem totes les teves preguntes actuals al vídeo 1
UPDATE preguntes SET video_id = 1;

-- 5. Fem la columna FK NOT NULL i creem la relació formal
ALTER TABLE preguntes MODIFY COLUMN video_id INT NOT NULL;
ALTER TABLE preguntes ADD CONSTRAINT fk_preguntes_videos FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE;