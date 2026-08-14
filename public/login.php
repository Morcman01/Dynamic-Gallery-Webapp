<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

header("Content-Type: application/json");

//get json data from javascript
$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['username']) || !isset($data['password'])){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Username and password required"
    ]);
    
    exit;
}

//username and password empty string check
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit;
}


//pull user data from database using given username
$sql = "SELECT id, username, password_hash, role
        FROM users
        WHERE username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();


//if user doesn't exist
if($result->num_rows === 0){
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => "Invalid username or password"
    ]);

    exit;
}

//get user data from database as associate array
$user = $result -> fetch_assoc();

//check password
if(!password_verify($password, $user['password_hash'])){
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => "Invalid username or password"
    ]);

    exit;
}

//auth successful
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'success' => true,
    'message' => "Login successful, hey there " . $username
]);

exit;


?>