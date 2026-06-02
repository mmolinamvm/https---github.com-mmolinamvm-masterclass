CREATE TABLE IF NOT EXISTS usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    cognoms VARCHAR(100) NOT NULL,
    rol ENUM('alumne', 'professor') DEFAULT 'alumne',
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserim un alumne de prova (Contrasenya: "alumne123")
-- Nota: El hash s'ha generat amb el mètode oficial password_hash() de PHP
INSERT INTO usuaris (username, email, password_hash, nom, cognoms, rol)
VALUES ('alumne1', 'alumne@masterclass.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joan', 'Garcia', 'alumne');

-- Inserim un professor de prova (Contrasenya: "profe123")
INSERT INTO usuaris (username, email, password_hash, nom, cognoms, rol)
VALUES ('profe1', 'profe@masterclass.com', '$2y$10$5Kk7XwQ0hY7Vv2bHh8C8O.gE1E9qZ7YjW/U6Z7fG0oT2uG3vXFmUe', 'Marta', 'Prats', 'professor');