<?php
session_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../auth/check.php'; // guard admin

$db = new Database();
$conn = $db->getConnection();

$id       = $_POST['id'] ?? null;
$username = trim($_POST['username'] ?? '');
$fullname = trim($_POST['fullname'] ?? '');
$password = $_POST['password'] ?? null;

// Validasi dasar
if (!$id || $username === '' || $fullname === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit;
}

try {

    // Jika password tidak diubah
    if ($password === null || trim($password) === '') {

        $sql = "UPDATE admin_user 
                SET username = :username, fullname = :fullname 
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    } else {

        // HASH PASSWORD
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE admin_user 
                SET username = :username, 
                    fullname = :fullname, 
                    password = :password 
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    }

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Profil berhasil diperbarui"
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui profil"
    ]);
}
