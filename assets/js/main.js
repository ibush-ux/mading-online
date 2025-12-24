const API_BASE = "http://localhost/mading-online/backend/api/berita/";

// ======================================================
// SAFE FETCH DEBUGGING
// ======================================================
async function safeFetch(url, options = {}) {
    try {
        const response = await fetch(url, options);

        const text = await response.text();
        console.log("RAW RESPONSE:", text);

        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("JSON parse error:", e);
            return { success: false, message: "Invalid JSON", raw: text };
        }

    } catch (err) {
        console.error("Fetch error:", err);
        return { success: false, message: err.message };
    }
}

// ======================================================
// INPUT ELEMENT
// ======================================================
const judulInput = document.getElementById("judul");
const kontenInput = document.getElementById("konten");
const penulisInput = document.getElementById("penulis");
const statusInput = document.getElementById("status");
const fileInput = document.getElementById("fileUpload");
const previewImg = document.getElementById("previewGambar");
const btnSimpan = document.getElementById("btnSimpan");

let editID = null;

// ======================================================
// PREVIEW FILE
// ======================================================
fileInput.addEventListener("change", function () {
    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewImg.style.display = "block";
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

// ======================================================
// LOAD BERITA
// ======================================================
async function loadBerita() {
    const data = await safeFetch(API_BASE + "read.php");

    const wrapper = document.getElementById("daftarBerita");
    wrapper.innerHTML = "";

    if (!data.success) {
        wrapper.innerHTML = "<p>Gagal memuat data berita</p>";
        return;
    }

    data.data.forEach(b => {

        // fallback jika API belum kirim file_url
        const fileUrl = b.file_url 
            ? b.file_url 
            : (b.file ? `http://localhost/mading-online/backend/uploads/berita/${b.file}` : null);

        const ext = b.file ? b.file.split('.').pop().toLowerCase() : null;

        let mediaElement = "";

        if (fileUrl && ["jpg","jpeg","png","gif","webp"].includes(ext)) {
            mediaElement = `<img src="${fileUrl}" class="berita-thumb">`;
        }
        else if (fileUrl && ["mp4","webm","ogg"].includes(ext)) {
            mediaElement = `<video src="${fileUrl}" class="berita-thumb" controls></video>`;
        }
        else if (fileUrl && ext === "pdf") {
            mediaElement = `<embed src="${fileUrl}" type="application/pdf" class="berita-thumb" />`;
        }
        else {
            mediaElement = `<p class="berita-thumb">Tidak dapat menampilkan file</p>`;
        }

        wrapper.innerHTML += `
            <div class="berita-card">
                ${mediaElement}

                <h3>${b.judul}</h3>
                <p>${b.konten.substring(0, 100)}...</p>

                <button onclick="editBerita(${b.id})">Edit</button>
                <button onclick="hapusBerita(${b.id})">Hapus</button>
            </div>
        `;
    });
}

// ======================================================
// SIMPAN BERITA
// ======================================================
async function simpanBerita() {
    const formData = new FormData();
    formData.append("judul", judulInput.value);
    formData.append("konten", kontenInput.value);
    formData.append("penulis", penulisInput.value);
    formData.append("status", statusInput.value);
    if (fileInput.files[0]) formData.append("file", fileInput.files[0]);

    const json = await safeFetch(API_BASE + "create.php", {
        method: "POST",
        body: formData
    });

    if (!json.success) {
        alert(json.message);
        return;
    }

    alert("Berita berhasil disimpan");
    resetForm();
    loadBerita();
}

// ======================================================
// EDIT BERITA
// ======================================================
function editBerita(id) {
    fetch(API_BASE + "read_single.php?id=" + id)
        .then(r => r.json())
        .then(res => {
            console.log("RAW RESPONSE:", res);

            if (!res.success) return alert("Tidak dapat mengambil data");

            const b = res.data;

            // Set value ke input
            document.getElementById("editJudul").value = b.judul;
            document.getElementById("editKonten").value = b.konten;
            document.getElementById("editPenulis").value = b.penulis;
            document.getElementById("editStatus").value = b.status;
            document.getElementById("editId").value = b.id;

            // Simpan ID untuk update nanti
            let editID=null
            window.editID = b.id;

            // Hitung URL file
            const fileUrl = b.file 
                ? `http://localhost/mading-online/backend/uploads/berita/${b.file}` 
                : null;

            const ext = b.file ? b.file.split('.').pop().toLowerCase() : null;

            let preview = "";

            if (!b.file) {
                preview = "<p>Tidak ada file</p>";
            }
            else if (["jpg","jpeg","png","gif","webp"].includes(ext)) {
                preview = `<img src="${fileUrl}" class="berita-thumb">`;
            }
            else if (["mp4","webm","ogg"].includes(ext)) {
                preview = `<video src="${fileUrl}" controls class="berita-thumb"></video>`;
            }
            else if (ext === "pdf") {
                preview = `<embed src="${fileUrl}" type="application/pdf" class="berita-thumb">`;
            }
            else {
                preview = "<p>File tidak dapat ditampilkan</p>";
            }

            document.getElementById("previewEdit").innerHTML = preview;

            document.getElementById("modalEdit").style.display = "flex";
        });
}

// ======================================================
// UPDATE BERITA
// ======================================================
async function updateBerita() {
    const id = document.getElementById("editId").value;
    const judul = document.getElementById("editJudul").value;
    const konten = document.getElementById("editKonten").value;
    const penulis = document.getElementById("editPenulis").value;
    const status = document.getElementById("editStatus").value;
    const file = document.getElementById("editFile").files[0];

    if (!id) {
        alert("ID tidak ditemukan");
        return;
    }

    let form = new FormData();
    form.append("id", id);
    form.append("judul", judul);
    form.append("konten", konten);
    form.append("penulis", penulis);
    form.append("status", status);

    if (file) {
        form.append("file", file);
    }

    const json = await safeFetch(API_BASE + "update.php", {
        method: "POST",
        body: form
    });

    if (!json.success) {
        alert(json.message || "Gagal update");
        return;
    }

    alert("Berita berhasil diupdate");
    closeModal();
    loadBerita();
}

// ======================================================
// DELETE BERITA
// ======================================================
async function hapusBerita(id) {
    if (!confirm("Yakin ingin menghapus?")) return;

    const formData = new FormData();
    formData.append("id", id);

    const json = await safeFetch(API_BASE + "delete.php", {
        method: "POST",
        body: formData
    });

    if (!json.success) {
        alert(json.message);
        return;
    }

    alert("Berita dihapus");
    loadBerita();
}

// ======================================================
// RESET FORM
// ======================================================

function resetForm() {
    judulInput.value = "";
    kontenInput.value = "";
    penulisInput.value = "";
    statusInput.value = "draft";
    fileInput.value = "";

    previewImg.style.display = "none";
    previewImg.src = "";

    document.getElementById("editId").value = "";   // Penting!
    btnSimpan.textContent = "Simpan Berita";
}

btnSimpan.addEventListener("click", function () {
    const id = document.getElementById("editId").value;

    if (id) {
        updateBerita();
    } else {
        simpanBerita();
    }
});

function closeModal() {
    document.getElementById("modalEdit").style.display = "none";
    document.getElementById("editId").value = "";  
}

document.addEventListener("DOMContentLoaded", loadBerita);
