<?php
require_once __DIR__ . '/../config/db.php';

class BeritaController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($judul, $isi) {
        $sql = "INSERT INTO berita (judul, isi) VALUES ('$judul', '$isi')";
        return $this->conn->query($sql);
    }

    public function read() {
        $result = $this->conn->query("SELECT * FROM berita");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function update($id, $judul, $isi) {
        $sql = "UPDATE berita SET judul='$judul', isi='$isi' WHERE id=$id";
        return $this->conn->query($sql);
    }

    public function delete($id) {
        $sql = "DELETE FROM berita WHERE id=$id";
        return $this->conn->query($sql);
    }
}
?>
