<?php
namespace src\Controllers;

use src\Models\Pregunta;

class PreguntaController {
    
    public function index() {
        // Capturem el paràmetre de la URL, si no ve, donem un error 400
        $video_id = $_GET['video_id'] ?? null;
        
        if (!$video_id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Falta el paràmetre video_id"]);
            exit;
        }
        
        // 1. Instanciem el Model de Preguntes
        $model = new Pregunta();
        
        // 2. Li demanem les dades processades
        // $dades = $model->getAllWithOpcions();
        $dades = $model->getByVideoIdWithOpcions(intval($video_id));
        
        // 3. Enviem la resposta JSON al client (Frontend)
        http_response_code(200);
        echo json_encode($dades, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}