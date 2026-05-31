<?php
// index.php - Front Controller

// Autoload bàsic de classes per namespace (Molt útil per a DWES)
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/';
    
    // Transformem els namespaces en rutes de fitxers (ex: Src\Models\Pregunta -> src/Models/Pregunta.php)
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Enrutador súper simple basat en un paràmetre URL (Ex: index.php?action=get_preguntes)
$action = $_GET['action'] ?? 'home';

// CORS Headers globals per a l'API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

switch ($action) {
    case 'api/get_preguntes':
        $controller = new src\Controllers\PreguntaController();
        $controller->index();
        break;
        
    case 'api/post_resposta':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Mètode no permès"]);
            exit;
        }
        $controller = new src\Controllers\RespostaController();
        $controller->store();
        break;

    default:
        // Si no és una acció de l'API, serveix la vista estàtica del frontend
        header("Content-Type: text/html; charset=UTF-8");
        readfile(__DIR__ . '/public/index.html');
        break;
}