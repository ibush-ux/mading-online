<?php
header('Content-Type: application/json');

$routerPath = '/mading-online/backend/router.php';
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoint = substr($requestPath, strlen($routerPath));
if ($endpoint === false || $endpoint === '') {
    $endpoint = '/';
}

// Menangkap URL yang diminta
$request = $_SERVER['REQUEST_URI'];
$method  = $_SERVER['REQUEST_METHOD'];

// Base path project
$basePath = '/mading-online/backend'; 

// Hilangkan base path dari URL
$endpoint = str_replace($basePath, '', $request);

$routerPath = '/mading-online/backend/router.php';
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoint = substr($requestPath, strlen($routerPath));
if ($endpoint === false || $endpoint === '') {
    $endpoint = '/';
}

// ROUTING
switch (true) {

    // ==========================
    // GET ALL BERITA
    // ==========================
    case $endpoint === '/api/berita/read':
        require __DIR__ . '/api/berita/read.php';
        break;

    // ==========================
    // GET BERITA BY ID
    // /api/berita/read/5
    // ==========================
    case preg_match('#^/api/berita/read/(\d+)$#', $endpoint, $m):
        $_GET['id'] = $m[1];
        require __DIR__ . '/api/berita/read_by_id.php';
        break;

    // ==========================
    // CREATE BERITA (POST)
    // ==========================
    case $endpoint === '/api/berita/create' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require __DIR__ . '/api/berita/create.php';
        break;

    // ==========================
    // UPDATE BERITA (POST)
    // ==========================
    case $endpoint === '/api/berita/update' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require __DIR__ . '/api/berita/update.php';
        break;

    // ==========================
    // DELETE BERITA (POST)
    // ==========================
    case $endpoint === '/api/berita/delete' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require __DIR__ . '/api/berita/delete.php';
        break;

    // ==========================
    // ENDPOINT NOT FOUND
    // ==========================
    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint tidak ditemukan", "endpoint" => $endpoint]);
}
