<?php

const MAX_ATTEMPTS = 5;
const RETRY_IN = 15;

function getClientIp(): string {
    return $_SERVER["REMOTE_ADDR"] ?? "unknown";
} 

function isLockedOut(mysqli $conn, string $username): array {
    $ip =  getClientIp();
    $windowStart = date('Y-m-d H:i:s', time() - RETRY_IN * 60);

    $sql = "SELECT COUNT(*) as attempts FROM login_attempts WHERE (username = ? OR ip_address = ?) AND attempted_at > ?";
    $stmt = $conn -> prepare($sql);
    $stmt -> bind_param("sss", $username, $ip, $windowStart);
    $stmt -> execute();
    $result = $stmt -> get_result()-> fetch_assoc();
    $stmt -> close();

    $attempts = (int) $result['attempts'];

    return[
        'limited' => $attempts >= MAX_ATTEMPTS,
        'attempts' => $attempts,
        'remaining' => max(0, MAX_ATTEMPTS - $attempts)
    ];
}

function recordFailedAttempt(mysqli $conn, string $username): void{
    $ip = getClientIp();
    $sql = "INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)";
    $stmt = $conn -> prepare($sql);
    $stmt -> bind_param("ss", $username, $ip);
    $stmt -> execute();
    $stmt -> close();
}

function clearAttempts(mysqli $conn, string $username): void{
    $ip = getClientIp();
    $sql = "DELETE FROM login_attempts WHERE username = ? OR ip_address = ?";
    $stmt = $conn -> prepare($sql);
    $stmt -> bind_param("ss", $username, $ip);
    $stmt -> execute();
    $stmt -> close();
}

function pruneOldAttempts(mysqli $conn): void {
    $cutoff = date('Y-m-d H:i:s', time() - RETRY_IN * 60);
    $conn->query("DELETE FROM login_attempts WHERE attempted_at < '$cutoff'");
}
?>