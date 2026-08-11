<?php
require __DIR__ . '/../includes/db.php';
header("Content-Type: application/json");

//get file data from javascript
$data = json_decode(file_get_contents("php://input"), true);

//check if filename exists
if(!isset($data["filename"])){
    echo json_encode([
        "success" => false,
        "message" => "File not found"
    ]);
    exit;
}

//get filename and build filepath
$filename = $data["filename"];

$filePath = __DIR__ . "/foto-berdua/" . $filename;

//check for non existent file
if(file_exists($filePath)){
    if(!unlink($filePath)){
        echo json_encode([
            "success" => false,
            "message" => "Error, photo not deleted from directory"
        ]);
        exit;
    }
}

//check for invalid file
if (!preg_match('/^[a-zA-Z0-9]+\.(jpg|jpeg|png|webp)$/', $filename)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid filename"
    ]);
    exit;
}

//delete from database
$sql = "DELETE from photos WHERE filename = ?";

$stmt = $conn->prepare($sql);
$stmt -> bind_param("s", $filename);

//return false if database deletion fails
if(!$stmt->execute()){
    echo json_encode([
        "success" => false,
        "message" => "Error, photo not deleted from database"
    ]);
    exit;
}

if ($stmt->affected_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Photo not found in database"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Photo deleted"
]);
exit;


?>