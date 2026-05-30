<?php
// Capçaleres obligatòries per a una API JSON RESTful
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 1. Validació del mètode HTTP (Enrutament bàsic)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Mètode no permès. Requerit: POST."]);
    exit();
}

// 2. Captura del flux d'entrada (Raw input stream)
$json_pures = file_get_contents("php://input");
$dades = json_decode($json_pures, true); // Deserialitzem a un array associatiu de PHP

// 3. Validació de dades estructurals (Defensa del controlador)
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "JSON mal formatulat."]);
    exit();
}

if (!isset($dades['pregunta_id']) || !isset($dades['tipus']) || !isset($dades['resposta'])) {
    http_response_code(422); // Unprocessable Entity
    echo json_encode(["status" => "error", "message" => "Dades incompletes (Falten camps obligatoris)."]);
    exit();
}

// 4. Extracció i sanejament bàsic (Simulació del que faria el teu Model/Repository)
$pregunta_id = intval($dades['pregunta_id']);
$tipus = filter_var($dades['tipus'], FILTER_SANITIZE_SPECIAL_CHARS);
$resposta_alumne = $dades['resposta']; // Pot ser un String o un Array

// 5. Resposta d'èxit per comprovar el servidor (Eco del que hem rebut)
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Resposta rebuda correctament al servidor!",
    "dades_processades" => [
        "pregunta_id" => $pregunta_id,
        "tipus" => $tipus,
        "resposta" => $resposta_alumne,
        "fet_a_les" => date("Y-m-d H:i:s")
    ]
]);