<?php







@ini_set('session.cookie_httponly', 1);
@ini_set('session.use_strict_mode', 1);


if (
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
) {
    @ini_set('session.cookie_secure', 1);
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}





function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            
            $_SESSION['csrf_token'] = bin2hex(md5(uniqid(mt_rand(), true)));
        }
    }
    return $_SESSION['csrf_token'];
}





function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}





function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            (!function_exists('hash_equals')
                ? $_POST['csrf_token'] !== $_SESSION['csrf_token']
                : !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
        ) {
            die('⚠️ CSRF token tidak valid. Silakan refresh halaman dan coba lagi.');
        }
    }
}






function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}





function csrf_meta(): string {
    $token = csrf_token();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}






function csrf_check_ajax(): bool {
    $token = '';

    
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'x-csrf-token') {
                $token = $value;
                break;
            }
        }
    }

    
    if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    
    if (empty($token) && isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    if (!function_exists('hash_equals')) {
        return $token === $_SESSION['csrf_token'];
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}








function rate_limit_check(string $key, int $max_attempts = 5, int $lockout_time = 300): array {
    if (!isset($_SESSION['rate_limit'][$key])) {
        return ['allowed' => true, 'message' => ''];
    }
    
    $attempts = $_SESSION['rate_limit'][$key]['attempts'];
    $last_time = $_SESSION['rate_limit'][$key]['time'];
    
    if ($attempts >= $max_attempts) {
        $time_passed = time() - $last_time;
        if ($time_passed < $lockout_time) {
            $wait = ceil(($lockout_time - $time_passed) / 60);
            return ['allowed' => false, 'message' => "Terlalu banyak percobaan gagal. Silakan coba lagi dalam $wait menit."];
        } else {
            
            unset($_SESSION['rate_limit'][$key]);
            return ['allowed' => true, 'message' => ''];
        }
    }
    
    return ['allowed' => true, 'message' => ''];
}




function rate_limit_record(string $key): void {
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'time' => time()
        ];
    } else {
        $_SESSION['rate_limit'][$key]['attempts']++;
        $_SESSION['rate_limit'][$key]['time'] = time();
    }
}




function rate_limit_clear(string $key): void {
    if (isset($_SESSION['rate_limit'][$key])) {
        unset($_SESSION['rate_limit'][$key]);
    }
}








function validate_upload(array $file, array $allowed_mimes = ['image/jpeg', 'image/png', 'application/pdf'], float $max_size_mb = 2.0): array {
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'message' => 'Parameter tidak valid.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'Gagal upload file. Error code: ' . $file['error']];
    }
    
    
    $max_size_bytes = $max_size_mb * 1024 * 1024;
    if ($file['size'] > $max_size_bytes) {
        return ['valid' => false, 'message' => "Ukuran file terlalu besar. Maksimal $max_size_mb MB."];
    }
    
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if ($mime === false || !in_array($mime, $allowed_mimes, true)) {
        return ['valid' => false, 'message' => 'Format file tidak diizinkan. Tipe terdeteksi: ' . ($mime ? $mime : 'Unknown')];
    }
    
    
    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'application/pdf' => 'pdf'
    ];
    $ext = $ext_map[$mime] ?? 'bin';
    
    return ['valid' => true, 'message' => 'Valid', 'ext' => $ext];
}
?>
