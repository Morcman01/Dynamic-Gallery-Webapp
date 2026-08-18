<?php
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function requireCsrf(): void {
    $headers = getallheaders();
    $submittedToken = $headers['X-CSRF-Token'] ?? '';

    if (
        empty($_SESSION['csrf_token']) ||
        empty($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing security token']);
        exit;
    }
}
?>