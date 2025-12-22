<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Folder uploads absolute path
$targetDir = '/opt/lampp/htdocs/mading-online/uploads/';

// Pastikan folder uploads ada
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Cek apakah file dikirim
if(isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = basename($file["name"]);

    // Sanitasi nama file
    $fileName = preg_replace("/[^A-Za-z0-9_\-\.]/", '_', $fileName);

    $targetFile = $targetDir . $fileName;

    if(move_uploaded_file($file["tmp_name"], $targetFile)) {
        echo json_encode([
            "status" => "success",
            "message" => "File berhasil diupload!",
            "file_url" => "/mading-online/uploads/" . $fileName
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mengupload file."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Tidak ada file yang dikirim."
    ]);
}
