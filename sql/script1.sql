CREATE database masterclass_db;

USE masterclass_db;

CREATE TABLE preguntes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segon INT NOT NULL,
    tipus ENUM('text', 'single', 'multiple') NOT NULL,
    text_pregunta TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE opcions_pregunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    text_opcio VARCHAR(255) NOT NULL,
    FOREIGN KEY (pregunta_id) REFERENCES preguntes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERCIÓ DE DADES DE PROVA (Seeders)
INSERT INTO preguntes (id, segon, tipus, text_pregunta) VALUES 
(1, 10, 'text', 'Quins tres impactes de la industrialització es veuen al mapa?'),
(2, 25, 'single', 'Quina d''aquestes normatives és la principal per al medi natural?'),
(3, 40, 'multiple', 'Quins recursos es consideren exhauribles segons l''autor?');

INSERT INTO opcions_pregunta (pregunta_id, text_opcio) VALUES 
(2, 'Llei de l''Aigua 1985'),
(2, 'Directiva Hàbitats 1992'),
(2, 'Conveni de París'),
(3, 'Combustibles fòssils'),
(3, 'Energia solar'),
(3, 'Mineria de terres rares');