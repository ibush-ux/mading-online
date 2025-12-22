<?php
/**
 * Session Hardening
 * Wajib dipanggil sebelum session_start()
 */

ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,               // session browser
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,           // TRUE jika HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

/**
 * SESSION TIMEOUT (30 menit)
 */
$timeout = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(["error" => "Session expired"]);
    exit;
}

$_SESSION['last_activity'] = time();

/**
 * IKAT SESSION KE BROWSER
 */
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(["error" => "Session invalid"]);
    exit;
}
