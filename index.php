<?php
// index.php - Front Controller

// AFESTA AQUESTES 3 LÍNIES TEMPORALMENT PER TRAURE ELS ERRORS AMAGATS:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... la resta del teu index.php habitual (autoloader, switch, etc.) ...
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

    case 'api/login':
        $controller = new src\Controllers\AuthController();
        $controller->login();
        break;

    case 'logout':
        $controller = new src\Controllers\AuthController();
        $controller->logout();
        break;

// --- NOVA RUTA D'API PER AL DASHBOARD ---
    case 'api/get_videos_alumne':
        $controller = new src\Controllers\VideoController();
        $controller->getVideosAlumne();
        break;

default:
        // 1. Iniciem la sessió (si no s'havia iniciat abans)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. COMPROVACIÓ: Té l'usuari la sessió iniciada?
        if (!isset($_SESSION['usuari_id'])) {
            // ❌ NO ha fet login: Li enviem la pantalla d'inici de sessió
            header("Content-Type: text/html; charset=utf-8");
            readfile(__DIR__ . '/public/login.html');
        } else {
            //  SÍ ha fet login: Mirem el seu rol per decidir on enviar-lo
            header("Content-Type: text/html; charset=utf-8");
            
            if (isset($_SESSION['usuari_rol']) && $_SESSION['usuari_rol'] === 'professor') {
                // En el futur, aquí carregaríem el panell del docent
                echo "Benvingut Professor " . $_SESSION['nom'] . ". Aviat construirem el teu panell. <a href='index.php?action=logout'>Sortir</a>";
            } else {
                // Si és alumne, de moment el deixem passar al reproductor que ja tens fet!
                readfile(__DIR__ . '/public/dashboard_alumne.html');
            }
        }
        break;
}