<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// 1. Connexió PDO
$host = 'localhost';
$db   = 'masterclass_db'; 
$user = 'root';           
$pass = '';               

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Consulta amb LEFT JOIN per arreplegar les preguntes i les seves opcions (si en tenen)
    $sql = "SELECT p.id AS pregunta_id, p.segon, p.tipus, p.text_pregunta,
                   o.id AS opcio_id, o.text_opcio
            FROM preguntes p
            LEFT JOIN opcions_pregunta o ON p.id = o.pregunta_id
            ORDER BY p.segon ASC";

    $stmt = $pdo->query($sql);
    
    // 3. Algorisme d'agrupació en l'entorn servidor (Molt docent per a DWES)
    $preguntes_processades = [];

    while ($row = $stmt->fetch()) {
        $p_id = $row['pregunta_id'];

        // Si és la primera vegada que veiem aquesta pregunta, creem la seva estructura base
        if (!isset($preguntes_processades[$p_id])) {
            $preguntes_processades[$p_id] = [
                "id" => intval($p_id),
                "segon" => intval($row['segon']),
                "tipus" => $row['tipus'],
                "text" => $row['text_pregunta'],
                "opcions" => [] // S'omplirà com un array d'objectes si té opcions
            ];
        }

        // Si la fila té una opció de resposta (no és NULL pel LEFT JOIN), l'afegim
        if ($row['opcio_id'] !== null) {
            $preguntes_processades[$p_id]['opcions'][] = [
                "id" => intval($row['opcio_id']),
                "text_opcio" => $row['text_opcio']
            ];
        }
    }

    // Reindexem l'array perquè perdi les claus associatives dels IDs i sigui un array JS pur []
    $json_final = array_values($preguntes_processades);

    http_response_code(200);
    echo json_encode($json_final, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de connexió a la BD: " . $e->getMessage()
    ]);
}