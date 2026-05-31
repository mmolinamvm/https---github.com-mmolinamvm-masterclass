<?php
namespace src\Models;

use config\Database;

class Pregunta {
    private $db;

    public function __construct() {
        // Demanem la connexió centralitzada a la nostra classe Database
        $this->db = Database::getConnection();
    }

    // NOU MÈTODE: Obtenir les dades pures del vídeo
    public function getVideoInfo($video_id) {
        $sql = "SELECT id, codi_youtube, titol, descripcio FROM videos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$video_id]);
        return $stmt->fetch(); // Retorna un array associatiu amb les dades del vídeo o false
    }
    
    // REVISAT: Passem el video_id per filtrar la consulta
    public function getByVideoIdWithOpcions($video_id) {
        $sql = "SELECT p.id AS pregunta_id, p.segon, p.tipus, p.text_pregunta,
                       o.id AS opcio_id, o.text_opcio
                FROM preguntes p
                LEFT JOIN opcions_pregunta o ON p.id = o.pregunta_id
                WHERE p.video_id = ?
                ORDER BY p.segon ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$video_id]);
        
        $preguntes_processades = [];

        while ($row = $stmt->fetch()) {
            $p_id = $row['pregunta_id'];

            if (!isset($preguntes_processades[$p_id])) {
                $preguntes_processades[$p_id] = [
                    "id" => intval($p_id),
                    "segon" => intval($row['segon']),
                    "tipus" => $row['tipus'],
                    "text" => $row['text_pregunta'],
                    "opcions" => [] 
                ];
            }

            if ($row['opcio_id'] !== null) {
                $preguntes_processades[$p_id]['opcions'][] = [
                    "id" => intval($row['opcio_id']),
                    "text_opcio" => $row['text_opcio']
                ];
            }
        }

        return array_values($preguntes_processades);
    }


    public function getAllWithOpcions() {
        // La teva consulta original amb LEFT JOIN
        $sql = "SELECT p.id AS pregunta_id, p.segon, p.tipus, p.text_pregunta,
                       o.id AS opcio_id, o.text_opcio
                FROM preguntes p
                LEFT JOIN opcions_pregunta o ON p.id = o.pregunta_id
                ORDER BY p.segon ASC";

        $stmt = $this->db->query($sql);
        $preguntes_processades = [];

        // El teu algorisme pedagògic de mapeig i agrupació
        while ($row = $stmt->fetch()) {
            $p_id = $row['pregunta_id'];

            if (!isset($preguntes_processades[$p_id])) {
                $preguntes_processades[$p_id] = [
                    "id" => intval($p_id),
                    "segon" => intval($row['segon']),
                    "tipus" => $row['tipus'],
                    "text" => $row['text_pregunta'],
                    "opcions" => [] 
                ];
            }

            if ($row['opcio_id'] !== null) {
                $preguntes_processades[$p_id]['opcions'][] = [
                    "id" => intval($row['opcio_id']),
                    "text_opcio" => $row['text_opcio']
                ];
            }
        }

        // Reindexem l'array perquè perdi les claus associatives i retornem les dades pures
        return array_values($preguntes_processades);
    }
}