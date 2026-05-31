<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Captura del flux d'entrada (JSON asíncron del fetch)
$json_pur = file_get_contents("php://input");
$dades = json_decode($json_pur, true);

if (!isset($dades['pregunta_id']) || !isset($dades['tipus']) || !isset($dades['resposta'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dades d'entrada incompletes."]);
    exit();
}

$pregunta_id = intval($dades['pregunta_id']);
$tipus       = $dades['tipus'];
$resposta    = $dades['resposta'];
$alumne_id   = 1; // ID de proves (Mock) fins que implementis la sessió de l'aula

// 2. Connexió PDO amb les credencials del teu script de Reset
$host = 'localhost';
$db   = 'masterclass_db';
$user = 'masterclass_user';
$pass = 'ContrasenyaSegura123!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // 3. Iniciem una transacció atòmica
    $pdo->beginTransaction();

    if ($tipus === 'text') {
        // Text lliure: va directe a la columna de text
        $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, resposta_text) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pregunta_id, $alumne_id, $resposta]);

    } elseif ($tipus === 'single') {
        // Opció única: comprovem que l'ID sigui un enter i guardem a la FK de l'opció
        $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, opcio_seleccionada_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pregunta_id, $alumne_id, intval($resposta)]);

    } elseif ($tipus === 'multiple') {
        // Opció múltiple: rebem un array de IDs reals (ex: [6, 4]).
        // Preparem la sentència una sola vegada fora del bucle per optimitzar rendiment
        $sql = "INSERT INTO respostes_alumnes (pregunta_id, alumne_id, opcio_seleccionada_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Fem una inserció a la taula per cada opció marcada per l'alumne
        foreach ($resposta as $opcio_id) {
            $stmt->execute([$pregunta_id, $alumne_id, intval($opcio_id)]);
        }
    }

    // Si cap execució ha llançat una excepció, validem i guardem de manera persistent
    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Resposta guardada correctament a MySQL!",
        "dades_processades" => [
            "pregunta_id" => $pregunta_id,
            "tipus" => $tipus,
            "fet_a_les" => date("Y-m-d H:i:s")
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    // Si qualsevol de les línies o inserts falla, revertim l'estat per seguretat
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error a la Base de Dades: " . $e->getMessage()
    ]);
}