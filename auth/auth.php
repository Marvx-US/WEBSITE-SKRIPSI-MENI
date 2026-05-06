<?php
require_once __DIR__ . '/../config/helpers.php';

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function require_role(array $roles) {
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $roles)) {
        http_response_code(403);
        echo "Akses ditolak. Anda tidak memiliki izin untuk melihat halaman ini.";
        exit;
    }
}
?>
