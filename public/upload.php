<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

requireUploader();

header('Content-Type: application/json');


// Check that files were actually submitted
if (!isset($_FILES['photo'])) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'No files uploaded'
    ]);

    exit;
}


$allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
$allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
$max_size = 8 * 1024 * 1024;
$max_files = 10;

$files = $_FILES['photo'];

if (!is_array($files['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Expected multi-file upload field']);
    exit;
}

if (count($files['name']) > $max_files) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Max $max_files files per upload"]);
    exit;
}

$uploaded = 0;
$failed = [];

//loop through uploaded files
for($i = 0; $i < count($files['name']); $i++){

    $original_name = $files['name'][$i];
    $tmp = $files['tmp_name'][$i];
    $size = $files['size'][$i];
    $error = $files['error'][$i];


    //Check for upload error
    if($error !== UPLOAD_ERR_OK){
        $failed[] = "$original_name : upload error";
        continue;
    }


    //Check file size 
    if ($size > $max_size) {
        $failed[] = "$original_name: file too large (max 8MB)";
        continue;
    }

    
    $extension = strtolower(
        pathinfo($original_name, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $allowed_types)) {
        $failed[] = "$original_name: wrong file type";
        continue;
    }


    // Check for upload file types
    $mime_type = mime_content_type($tmp);

    if (!in_array($mime_type, $allowed_mime)) {
        $failed[] = "$original_name: invalid file type";
        continue;
    }

    //Filename sanitization
    $filename = uniqid() . "." . $extension;                     //bikin unique id buat file sebelum di upload
    $destination = __DIR__ . "/foto-berdua/" . $filename;

    // Move file
    if (!move_uploaded_file($tmp, $destination)) {
        $failed[] = "$original_name: failed to save file";
        continue;
    }

    // Insert into database
    $sql = "INSERT INTO photos (filename) VALUES (?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filename);

    if (!$stmt->execute()) {

        // Database failed, remove uploaded file
        unlink($destination);

        $failed[] = "$original_name: failed to save database record";

        $stmt->close();
        continue;
    }

    $stmt->close();

    $uploaded++;
}

$conn->close();

if ($uploaded > 0) {

    echo json_encode([
        'success' => true,
        'uploaded' => $uploaded,
        'failed' => $failed
    ]);

} else {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'No files were uploaded',
        'failed' => $failed
    ]);
}

exit;
?> 