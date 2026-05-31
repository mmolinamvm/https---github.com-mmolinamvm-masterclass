<?php
namespace src\Models;

use config\Database;

class Resposta {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function guardar($pregunta_id, $tipus, $resposta, $alumne_id = 1) {
        $this->db->beginTransaction();

        try {
            if ($tipus === 'text') {
                $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, resposta_text) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$pregunta_id, $alumne_id, $resposta]);

            } elseif ($tipus === 'single') {
                $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, opcio_seleccionada_id) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$pregunta_id, $alumne_id, intval($resposta)]);

            } elseif ($tipus === 'multiple') {
                $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, opcio_seleccionada_id) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);

                foreach ($resposta as $opcio_id) {
                    $stmt->execute([$pregunta_id, $alumne_id, intval($opcio_id)]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e; // Propaguem l'error cap al controlador
        }
    }
}