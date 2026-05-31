<?php
namespace src\Controllers;

use src\Models\Pregunta;

class PreguntaController {
    
    public function index() {
        // 1. Instanciem el Model de Preguntes
        $model = new Pregunta();
        
        // 2. Li demanem les dades processades
        $dades = $model->getAllWithOpcions();
        
        // 3. Enviem la resposta JSON al client (Frontend)
        http_response_code(200);
        echo json_encode($dades, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}