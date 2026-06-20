<?php
// index.php - Front Controller

// AFESTA AQUESTES 3 LÍNIES TEMPORALMENT PER TRAURE ELS ERRORS AMAGATS:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. INICIEM LA SESSIÓ GLOBALMENT PER A TOTA L'APP (Rutes i APIs)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    // Si entrem directament per navegador, podem posar per defecte el teu propi domini
    header("Access-Control-Allow-Origin: http://www.masterclass.com");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

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

    case 'api/post_resposta_alumne':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Mètode no permès"]);
            exit;
        }
        $controller = new src\Controllers\RespostaController();
        $controller->store_resposta_alumne();
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

// --- Dins del switch ($action) de l'index.php ---
    default:
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Si no hi ha sessió, bloquegem de forma estricta i servim el login
        if (!isset($_SESSION['usuari_id'])) {
            header("Content-Type: text/html; charset=utf-8");
            readfile(__DIR__ . '/public/login.html');
            exit;
        }

        // 2. 🌟 NOVA CONDICIÓ: Si s'ha enviat un paràmetre ?video=X a la URL, servim el reproductor dinàmic
        if (isset($_GET['video'])) {
            header("Content-Type: text/html; charset=utf-8");
            readfile(__DIR__ . '/public/reproductor.html');
            exit;
        }

        // 3. Si no hi ha paràmetre de vídeo, gestionem l'accés per rols normal
        header("Content-Type: text/html; charset=utf-8");
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'professor') {
            echo "Benvingut Professor " . htmlspecialchars($_SESSION['nom']) . ". Aviat construirem el teu panell. <a href='index.php?action=logout'>Sortir</a>";
        } else {
            readfile(__DIR__ . '/public/dashboard_alumne.html');
        }
        break;
}