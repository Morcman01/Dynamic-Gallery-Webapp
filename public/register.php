<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/rate_limit.php';
require __DIR__ . '/../includes/csrf.php';

header("Content-Type: application/json");

requireCsrf();

$data = json_decode(file_get_contents('php://input'), true);
$ip = getClientIp();


//check for empty username and password (null value)
if(!isset($data['username']) || !isset($data['password'])){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Username and password required"
    ]);

    recordFailedAttempt($conn, $ip);

    exit;
}

//sanitise and process user data
$username = trim($data['username']);
$password = $data['password'];


//login attempts check
$rateCheck = isLockedOut($conn, $ip);

if($rateCheck['limited']){
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => "Too many register attempts, try again in 15 minutes"
    ]);
    exit;
}

if (mt_rand(1, 100) === 1) {
    pruneOldAttempts($conn);
}

//check for empty space input
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Username and password required"]);
    recordFailedAttempt($conn, $ip);
    exit;
}

//check for password length
if(strlen($data['password']) < 8 || strlen($data['password']) > 20){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Password must be between 8-20 characters"
    ]);

    recordFailedAttempt($conn, $ip);

    exit;
}

//check for special chars in username 
if(!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Username can only contain alphanumerics and underscores, Max 20 Chars"
    ]);

    recordFailedAttempt($conn, $ip);

    exit;
}

//hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);


//sql query, makes new user
$role = 'viewer';

$sql = "INSERT into users (username, password_hash, role) VALUES (?, ?, ?)";
$stmt = $conn -> prepare($sql);
$stmt -> bind_param("sss", $username, $password_hash, $role);

if($stmt->execute()){
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => "Account created successfully"
    ]);
    recordFailedAttempt($conn, $ip);
} else {
    if($conn->errno === 1062){
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => "Username already taken"
        ]);
        recordFailedAttempt($conn, $ip);
    } else{
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Something went wrong please try again"
        ]);
        recordFailedAttempt($conn, $ip);
    }
}


$stmt->close();
$conn->close();
exit;

?>