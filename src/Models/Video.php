<?php
namespace src\Models;

use config\Database;

class Video {
    private $db;

    public function __construct() {
        // Demanem la connexió centralitzada a la nostra classe Database
        $this->db = Database::getConnection();
    }

    /**
     * Obté tots els vídeos assignats a un alumne concret amb els seus estats
     * @param int $usuari_id
     * @return array
     */
    public function getVideosAssignats($usuari_id) {
        $sql = "SELECT v.id, v.titol, v.descripcio, v.codi_youtube,
                    uv.estat, uv.reproduccions_restants, uv.data_limit, uv.data_completat,
                    (uv.data_limit < NOW()) AS esta_caducat
                FROM videos v
                INNER JOIN usuari_videos uv ON v.id = uv.video_id
                WHERE uv.usuari_id = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([intval($usuari_id)]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}