<?php
//error_reporting(0); // suppress error output
//ini_set('display_errors', 0); // don't display errors to browser
//ob_start();
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';
//ob_clean();


//Check for upload error
if($_FILES['photo']['error'] !== UPLOAD_ERR_OK){
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit;   
}


//Check file size 
$max_size = 8 * 1024 * 1024;
if($_FILES['photo']['size'] > $max_size){
    echo json_encode(['success' => false, 'message' => 'File size too big, max 8mb']);
    exit;
}


// Check for upload file types
$allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
$extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

if(!in_array($extension, $allowed_types)){
    echo json_encode(['success' => false, 'message' => 'Wrong file type!']);
    exit;
}


//Check for upload file MIME type
$allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
$mime_type = mime_content_type($_FILES['photo']['tmp_name']);

if(!in_array($mime_type, $allowed_mime)){
    echo json_encode(['success' => false, 'message' => 'Invalid file type!']);
    exit;
}


//Filename sanitization
$filename = uniqid() . "." . $extension;                     //bikin unique id buat file sebelum di upload
$tmp = $_FILES["photo"]["tmp_name"];                         //bikin var buat file photo temporary yang di upload ke php
$destination = __DIR__ . "/foto-berdua/" . $filename;

if(move_uploaded_file($tmp, $destination)){                  //pindahkan file foto ke directory foto-berdua
    $sql = "INSERT INTO photos (filename) VALUES (?)";       //query ke database

    $stmt = $conn->prepare($sql);   
    $stmt->bind_param("s", $filename);                       //????
    $stmt->execute();

    if (!$stmt->execute()) {
        unlink($destination);

        echo json_encode([
            'success' => false,
            'message' => 'Failed to save photo information'
        ]);

        exit;
    }

    // ob_end_clean();
    echo json_encode(['success' => true]);
    exit;

} else{
    echo json_encode([
        "success" => false,
        "message" => "Upload failed"
    ]);
}
?> 