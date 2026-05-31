<?php
namespace src\Controllers;

use src\Models\Pregunta;

class PreguntaController {
    
    public function index() {
        die("SÍ, S'ESTÀ EXECUTANT EL CONTROLADOR CORRECTE");
        // Capturem el paràmetre de la URL, si no ve, donem un error 400
        $video_id = $_GET['video_id'] ?? null;
        
        if (!$video_id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Falta el paràmetre video_id"]);
            exit;
        }

        // 1. Instanciem el Model de Preguntes
        $model = new Pregunta();

        // 1. Busquem la informació del vídeo
        $video_info = $model->getVideoInfo(intval($video_id));
        
        if (!$video_info) {
            http_response_code(404);
            echo json_encode([
                "status" => "error", 
                "message" => "El vídeo amb ID " . $video_id . " no s'ha trobat a la base de dades. Revisa si has omplert la taula 'videos'."
            ]);
            exit;
        }

        // 2. Li demanem les dades processades
        // $dades = $model->getAllWithOpcions();
        $preguntes = $model->getByVideoIdWithOpcions(intval($video_id));
        
        // 3. Estructura unificada de resposta professional
        $resposta = [
            "video_id" => intval($video_info['id']),
            "codi_youtube" => $video_info['codi_youtube'],
            "titol" => $video_info['titol'],
            "descripcio" => $video_info['descripcio'],
            "preguntes" => $preguntes
        ];

        // 3. Enviem la resposta JSON al client (Frontend)
        http_response_code(200);
        echo json_encode($dades, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}