<?php
namespace src\Controllers;

use src\Models\Usuari;

class AuthController {

    public function __construct() {
        // Ens assegurem que la sessió estigui activa per poder desar les dades de l'usuari
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Processa el formulari de login (Petició POST)
     */
    public function login() {
        // Si no venen dades per POST, responem amb error o redirigim
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Mètode no permès"]);
            exit;
        }

        // Llegim les dades del formulari (tant si vénen de l'HTML tradicional com d'un fetch JSON)
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? $_POST['email'] ?? null;
        $password = $input['password'] ?? $_POST['password'] ?? null;

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "L'email i la contrasenya són obligatoris"]);
            exit;
        }

        // Busquem l'usuari a la Base de Dades a través del Model
        $model = new Usuari();
        $usuari = $model->findByEmail($email);

        // Si l'usuari existeix, comprovem la contrasenya utilitzant la funció segura de PHP
        if ($usuari && password_verify($password, $usuari['password_hash'])) {
            
            // 🔐 CREDENCIALS CORRECTES: Guardem les dades clau a la sessió
            $_SESSION['usuari_id'] = intval($usuari['id']);
            $_SESSION['username'] = $usuari['username'];
            $_SESSION['nom'] = $usuari['nom'];
            $_SESSION['rol'] = $usuari['rol']; // 'alumne' o 'professor'

            // Responem èxit i enviem el rol perquè el frontend sàpiga on redirigir
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Autenticació correcta",
                "rol" => $usuari['rol']
            ]);
            exit;
        } else {
            // ❌ CREDENCIALS INCORRECTES
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "El correu o la contrasenya no són correctes"]);
            exit;
        }
    }

    /**
     * Tanca la sessió de l'usuari
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Redirigim a la pàgina de login o d'inici
        header("Location: index.php");
        exit;
    }
}