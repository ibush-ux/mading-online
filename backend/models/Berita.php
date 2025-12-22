<?php
class Berita {
    public $id;
    public $judul;
    public $isi;

    public function __construct($id, $judul, $isi) {
        $this->id = $id;
        $this->judul = $judul;
        $this->isi = $isi;
    }
}
?>
