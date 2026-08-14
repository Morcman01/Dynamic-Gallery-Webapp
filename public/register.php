<?php
require __DIR__ . '/../includes/db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true);


//check for empty username and password (null value)
if(!isset($data['username']) || !isset($data['password'])){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Username and password required"
    ]);

    exit;
}

//sanitise and process user data
$username = trim($data['username']);
$password = $data['password'];


//check for empty space input
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Username and password required"]);
    exit;
}

//check for password length
if(strlen($data['password']) < 8 || strlen($data['password']) > 20){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Password must be between 8-20 characters"
    ]);

    exit;
}

//check for special chars in username 
if(!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => "Username can only contain alphanumerics and underscores, Max 20 Chars"
    ]);

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
} else {
    if($conn->errno === 1062){
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => "Username already taken"
        ]);
    } else{
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Something went wrong please try again"
        ]);
    }
}

$stmt->close();
$conn->close();
exit;

?>