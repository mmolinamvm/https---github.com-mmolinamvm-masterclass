<?php
namespace src\Models;

use config\Database;

class Usuari {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un usuari pel seu correu electrònic
     * @param string $email
     * @return array|false Retorna les dades de l'usuari o false si no existeix
     */
    public function findByEmail($email) {
        $sql = "SELECT id, username, email, password_hash, nom, cognoms, rol 
                FROM usuaris 
                WHERE email = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}