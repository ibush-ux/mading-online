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

    $id = $_POST['id'] ?? 0;
    if(!$id) throw new Exception("ID berita dibutuhkan");

    // Ambil nama file dulu untuk dihapus
    $stmt = $conn->prepare("SELECT file FROM berita WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row && !empty($row['file'])){
        $file_path = __DIR__ . '/../../uploads/berita/' . $row['file'];
        if(file_exists($file_path)) unlink($file_path);
    }

    $stmt = $conn->prepare("DELETE FROM berita WHERE id=:id");
    $stmt->execute([':id'=>$id]);

    echo json_encode(['success'=>true,'message'=>'Berita berhasil dihapus']);

} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
