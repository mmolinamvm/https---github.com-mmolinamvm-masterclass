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
        "opcions" => [
            ["id" => 0, "text" => "Perquè són il·limitats"],
            ["id" => 1, "text" => "Per evitar l'esgotament i destrucció de l'entorn"],
            ["id" => 2, "text" => "Només per motius econòmics"]
        ]
    ],
    [
        "id" => 3, 
        "segon" => 40, 
        "tipus" => "multiple",
        "text" => "Pregunta 3: Quina relació té això amb la normativa del medi natural?",
        "opcions" => [
            ["id" => 0, "text" => "Lleis de protecció d'espais"],
            ["id" => 1, "text" => "No té cap relació"],
            ["id" => 2, "text" => "Regulació de residus industrials"]
        ]
    ]
];

// Responem amb un codi 200 OK i les dades en JSON
http_response_code(200);
echo json_encode($preguntes);