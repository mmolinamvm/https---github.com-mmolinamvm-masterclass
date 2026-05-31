<?php
namespace src\Controllers;

use src\Models\Resposta;

class RespostaController {
    
    public function store() {
        // Captura del flux JSON d'entrada
        $json_pur = file_get_contents("php://input");
        $dades = json_decode($json_pur, true);

        if (!isset($dades['pregunta_id']) || !isset($dades['tipus']) || !isset($dades['resposta'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Dades incompletes."]);
            exit();
        }

        try {
            $model = new Resposta();
            $model->guardar(
                intval($dades['pregunta_id']),
                $dades['tipus'],
                $dades['resposta']
            );

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Resposta rebuda correctament al servidor!",
                "dades_processades" => [
                    "pregunta_id" => intval($dades['pregunta_id']),
                    "tipus" => $dades['tipus'],
                    "fet_a_les" => date("Y-m-d H:i:s")
                ]
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Error al desar la resposta: " . $e->getMessage()
            ]);
        }
    }
}