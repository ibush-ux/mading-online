<?php
session_start();

require_once __DIR__ . '/csrf.php';

/* 1. Pastikan login */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

/* 2. Validasi CSRF untuk request berbahaya */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $csrf = $_POST['csrf_token']
        ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (!csrf_verify($csrf)) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Invalid CSRF token"
        ]);
        exit;
    }
}

