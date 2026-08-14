<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if(!isLoggedIn()){
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => "Authentication required"
        ]);

        exit;
    }
}

function requireUploader(): void
{
    requireLogin();

    if($_SESSION['role'] !== 'uploader'){
        http_response_code(403);

        echo json_encode([
        'success' => false,
        'message' => "Uploader permission required"
    ]);

    exit;
    }
}

?>