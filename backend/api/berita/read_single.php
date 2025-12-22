<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');


try {
    $db = new Database();
    $conn = $db->getConnection();

    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "ID berita wajib dikirimkan"
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM berita WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode([
            "success" => false,
            "message" => "Berita tidak ditemukan"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
