<?php
// Capçaleres per indicar que retornem JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET"); // En aquest cas és una petició GET

// Comprensió del mètode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Mètode no permès. Fes servir GET."]);
    exit();
}

// Simulació de dades de la Base de Dades
// (IMPORTANT: Fes servir l'estructura correcta amb 'tipus', 'opcions', etc.)
$preguntes = [
    [
        "id" => 1, 
        "segon" => 10, 
        "tipus" => "text",
        "text" => "Pregunta 1: Quins tres impactes de la industrialització s'esmenten?"
    ],
    [
        "id" => 2, 
        "segon" => 25, 
        "tipus" => "single",
        "text" => "Pregunta 2: Per què és necessari regular l'explotació de recursos?",
        "opcions" => [ "Perquè són il·limitats","Per evitar l'esgotament i destrucció de l'entorn", "Només per motius econòmics" ],
        "correcta" => 0
    ],
    [
        "id" => 3, 
        "segon" => 40, 
        "tipus" => "multiple",
        "text" => "Pregunta 3 (Multiresposta): Quins elements formen part de la normativa?",
        "opcions" => ["Protecció de recursos", "Industrialització lliure", "Impacte ambiental", "Explotació il·limitada"],
        "correcta" => [0,2]
    ]
];

// Responem amb un codi 200 OK i les dades en JSON
http_response_code(200);
echo json_encode($preguntes);