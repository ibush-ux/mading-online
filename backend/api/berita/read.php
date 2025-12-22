<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    if(isset($_GET['id'])){
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM berita WHERE id = :id");
        $stmt->bindParam(":id",$id,PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$data) throw new Exception("Berita tidak ditemukan");

        $data['file_url'] = !empty($data['file']) 
    ? "http://localhost/mading-online/backend/uploads/berita/" . $data['file'] 
    : null;

        echo json_encode(['success'=>true,'type'=>'by_id','data'=>$data]);
        exit;
    }

    $stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($list as &$row){
        $row['file_url'] = !empty($row['file']) 
    ? "http://localhost/mading-online/backend/uploads/berita/" . $row['file'] 
    : null;
    }

    echo json_encode(['success'=>true,'type'=>'all','data'=>$list]);

} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
