CREATE TABLE IF NOT EXISTS usuari_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuari_id INT NOT NULL,
    video_id INT NOT NULL,
    estat ENUM('pendent', 'vist', 'completat') DEFAULT 'pendent',
    reproduccions_restants INT DEFAULT 3, -- Exemple de límit de repros
    data_limit DATETIME NULL,              -- Per controlar si està caducat
    data_completat TIMESTAMP NULL,
    FOREIGN KEY (usuari_id) REFERENCES usuaris(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    UNIQUE KEY usuari_video_unic (usuari_id, video_id) -- Evita duplicats reals
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assignem el vídeo 1 al nostre alumne de proves (Joan Garcia, usuari_id = 1)
-- Li posem una data límit àmplia per a les proves
INSERT INTO usuari_videos (usuari_id, video_id, estat, reproduccions_restants, data_limit)
VALUES (1, 1, 'pendent', 3, '2026-12-31 23:59:59');