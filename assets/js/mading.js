console.log("mading.js loaded");

// =======================================
// CONFIG
// =======================================
const API_URL = "http://localhost/mading-online/backend/api/berita/read.php";

let allNews = [];
let currentPage = 1;
let perPage = 6;
let filteredData = [];

// =======================================
// LOAD NEWS FROM API
// =======================================
async function loadNews() {
    try {
        showLoading(true);

        const res = await fetch(API_URL);
        const data = await res.json();

        if (data.success && Array.isArray(data.data)) {
            allNews = data.data;
            filteredData = [...allNews];
            renderNews();
            renderPagination();
        } else {
            console.error("Format API salah:", data);
        }

    } catch (err) {
        console.error("Gagal load berita:", err);
    } finally {
        showLoading(false);
    }
}

// =======================================
// RENDER NEWS
// =======================================
function renderNews() {
    const wrap = document.getElementById("news-container");
    wrap.innerHTML = "";

    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const pageData = filteredData.slice(start, end);

    if (pageData.length === 0) {
        wrap.innerHTML = "<p style='text-align:center;'>Belum ada berita ditampilkan.</p>";
        return;
    }

    pageData.forEach(item => {
        const img = item.file_url
            ? item.file_url
            : "/mading-online/frontend/assets/img/no-image.png"; // fallback

        const card = `
            <div class="news-item">
                <img src="${img}" alt="${item.judul}">
                <h3>${item.judul}</h3>
                <p>${item.konten.slice(0, 120)}...</p>
                <button onclick="copyLink(${item.id})">Bagikan</button>
            </div>
        `;

        wrap.innerHTML += card;
    });
}

// =======================================
// PAGINATION
// =======================================
function renderPagination() {
    const pag = document.getElementById("pagination");
    pag.innerHTML = "";

    const totalPages = Math.ceil(filteredData.length / perPage);

    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
        pag.innerHTML += `
            <button class="page-btn ${i === currentPage ? "active" : ""}"
                    onclick="gotoPage(${i})">${i}</button>
        `;
    }
}

function gotoPage(num) {
    currentPage = num;
    renderNews();
    renderPagination();
}

// =======================================
// COPY LINK
// =======================================
function copyLink(id) {
    const link = `${window.location.origin}/mading-online/frontend/mading.html?id=${id}`;
    navigator.clipboard.writeText(link);
    alert("Link disalin:\n" + link);
}

// =======================================
// LOADING ANIMATION
// =======================================
function showLoading(status) {
    const loader = document.getElementById("loading");
    if (!loader) return;

    loader.style.display = status ? "block" : "none";
}

// =======================================
// INIT
// =======================================
document.addEventListener("DOMContentLoaded", () => {
    loadNews();
});

