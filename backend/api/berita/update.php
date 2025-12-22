// NOTE: To be restricted in production

<?php
require_once __DIR__ . '/../../auth/csrf.php';
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');
validate_csrf_or_die();

try {
    $db   = new Database();
    $conn = $db->getConnection();

    $id       = $_POST['id'] ?? null;
    $judul    = $_POST['judul'] ?? '';
    $konten   = $_POST['konten'] ?? '';
    $penulis  = $_POST['penulis'] ?? '';
    $status   = $_POST['status'] ?? 'draft';

    if (!$id) {
        throw new Exception("ID tidak valid");
    }

    $uploadDir = __DIR__ . '/../../uploads/berita/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    /* =========================
       AMBIL DATA FILE LAMA
       ========================= */
    $stmtOld = $conn->prepare("SELECT file FROM berita WHERE id = :id");
    $stmtOld->execute([':id' => $id]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        throw new Exception("Data berita tidak ditemukan");
    }

    $newFileName = $oldData['file'];

    /* =========================
       JIKA ADA FILE BARU
       ========================= */
    if (!empty($_FILES['file']['name'])) {

        // 1. Validasi ukuran
        $MAX_SIZE = 5 * 1024 * 1024; // 5MB
        if ($_FILES['file']['size'] > $MAX_SIZE) {
            throw new Exception("Ukuran file maksimal 5MB");
        }

        // 2. Validasi MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf'
        ];

        if (!in_array($mime, $allowedMime)) {
            throw new Exception("Tipe file tidak diizinkan");
        }

        // 3. Validasi ekstensi
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','webp','pdf'];

        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Ekstensi file tidak diizinkan");
        }

        // 4. Generate nama file aman
        $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetFile  = $uploadDir . $newFileName;

        // 5. Upload
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            throw new Exception("Upload file gagal");
        }

        // 6. Hapus file lama
        if (!empty($oldData['file']) && file_exists($uploadDir . $oldData['file'])) {
            unlink($uploadDir . $oldData['file']);
        }
    }

    /* =========================
       UPDATE DATABASE
       ========================= */
    $stmt = $conn->prepare("
        UPDATE berita SET
            judul   = :judul,
            konten  = :konten,
            penulis = :penulis,
            status  = :status,
            file    = :file
        WHERE id = :id
    ");

    $stmt->execute([
        ':id'       => $id,
        ':judul'    => $judul,
        ':konten'   => $konten,
        ':penulis'  => $penulis,
        ':status'   => $status,
        ':file'     => $newFileName
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Berita berhasil diperbarui'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
