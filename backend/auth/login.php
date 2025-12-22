// NOTE: To be restricted in production

<?php
session_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../config/db.php';

/* 1. Ambil JSON dulu */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

/* 2. Validasi CSRF */
$csrf = $data['csrf_token'] ?? '';
if (!csrf_verify($csrf)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token"]);
    exit;
}

/* 3. Ambil kredensial */
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["error" => "Username dan password wajib diisi"]);
    exit;
}

/* 4. Query user */
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SELECT * FROM admin_user WHERE username = :u LIMIT 1"
);
$stmt->execute([':u' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* 5. Validasi user */
if (
    !$user ||
    (
        password_verify($password, $user['password']) === false &&
        md5($password) !== $user['password']
    )
) {
    http_response_code(401);
    echo json_encode(["error" => "Username atau password salah"]);
    exit;
}

/* 6. Auto-upgrade MD5 */
if (md5($password) === $user['password']) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $u = $conn->prepare(
        "UPDATE admin_user SET password = :p WHERE id = :id"
    );
    $u->execute([':p' => $newHash, ':id' => $user['id']]);
}

/* 7. LOGIN SUKSES */
session_regenerate_id(true);
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];

/* 8. RESPONSE (INI SATU-SATUNYA ECHO SUKSES) */
echo json_encode([
    "success" => true,
    "message" => "Login berhasil",
    "user" => [
        "id" => $user["id"],
        "username" => $user["username"]
    ]
]);
