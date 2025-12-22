// NOTE: To be restricted in production

<?php
require_once __DIR__ . '/../../auth/csrf.php';
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');
validate_csrf_or_die();

try {
    $db = new Database();
    $conn = $db->getConnection();

    $judul    = $_POST['judul'] ?? '';
    $konten   = $_POST['konten'] ?? '';
    $penulis  = $_POST['penulis'] ?? '';
    $status   = $_POST['status'] ?? 'draft';

    $fileName = null;
    $uploadDir = __DIR__ . '/../../uploads/berita/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!empty($_FILES['file']['name'])) {

        $MAX_SIZE = 5 * 1024 * 1024;
        if ($_FILES['file']['size'] > $MAX_SIZE) {
            throw new Exception("Ukuran file maksimal 5MB");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);

        $allowedMime = [
            "image/jpeg", "image/png", "image/webp", "application/pdf"
        ];

        if (!in_array($mime, $allowedMime)) {
            throw new Exception("Tipe file tidak diizinkan");
        }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowedExt = ["jpg","jpeg","png","webp","pdf"];

        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Ekstensi file tidak diizinkan");
        }

        $fileName = bin2hex(random_bytes(16)) . "." . $ext;
        $target   = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            throw new Exception("Upload file gagal");
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO berita (judul, konten, file, penulis, status, created_at)
        VALUES (:judul, :konten, :file, :penulis, :status, NOW())
    ");

    $stmt->execute([
        ':judul'   => $judul,
        ':konten'  => $konten,
        ':file'    => $fileName,
        ':penulis' => $penulis,
        ':status'  => $status
    ]);

    echo json_encode(["success" => true, "message" => "Berita berhasil dibuat"]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
