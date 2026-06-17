<?php
namespace src\Controllers;

use src\Models\Video;

class VideoController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * API que retorna els vídeos assignats a l'alumne autenticat
     */
    public function getVideosAlumne() {
        header('Content-Type: application/json; charset=utf-8');

        // Control d'accés: l'usuari ha de tenir sessió i ser alumne
        if (!isset($_SESSION['usuari_id']) || !isset($_SESSION['usuari_rol']) || $_SESSION['usuari_rol'] !== 'alumne') {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Accés denegat. Cal iniciar sessió com a alumne."]);
            exit;
        }

        try {
            $model = new Video();
            // Llegim l'ID directament de la sessió segura del servidor
            $videos = $model->getVideosAssignats($_SESSION['usuari_id']);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "usuari_nom" => $_SESSION['nom'],
                "videos" => $videos
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al servidor: " . $e->getMessage()]);
            exit;
        }
    }
}