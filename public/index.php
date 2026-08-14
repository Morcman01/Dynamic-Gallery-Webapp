<?php 
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';

$isLoggedIn = isLoggedIn();
$role = $isLoggedIn ? $_SESSION['role'] : null;
$isUploader = $isLoggedIn && $role === 'uploader';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/jpeg" href="/images/favicon.jpg">
    <title>My Galeri :D</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <header>

        <!-- Profile Section -->
        <div class="profile-bar">
            <svg id="profile-icon" viewBox="0 0 640 640">
                <path fill="currentColor" d="M463 448.2C440.9 409.8 399.4 384 352 384L288 384C240.6 384 199.1 409.8 177 448.2C212.2 487.4 263.2 512 320 512C376.8 512 427.8 487.3 463 448.2zM64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320zM320 336C359.8 336 392 303.8 392 264C392 224.2 359.8 192 320 192C280.2 192 248 224.2 248 264C248 303.8 280.2 336 320 336z"/>
            </svg>
            
            <?php if($isLoggedIn): ?>
            <span id="greeting">Hey there, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <?php endif; ?>
        </div>


        <!-- Title -->
        <h1 id="main-title">~Galeri Markus dan Taniah~</h1>


        <!-- Upload Section -->
        <nav class="navbar">
            <?php if ($isUploader):?> 
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <label for="file-upload">

                    <div>
                        <svg id="upload-icon" viewBox="0 0 640 640">
                            <path fill="currentColor" d="M352 173.3L352 384C352 401.7 337.7 416 320 416C302.3 416 288 401.7 288 384L288 173.3L246.6 214.7C234.1 227.2 213.8 227.2 201.3 214.7C188.8 202.2 188.8 181.9 201.3 169.4L297.3 73.4C309.8 60.9 330.1 60.9 342.6 73.4L438.6 169.4C451.1 181.9 451.1 202.2 438.6 214.7C426.1 227.2 405.8 227.2 393.3 214.7L352 173.3zM320 464C364.2 464 400 428.2 400 384L480 384C515.3 384 544 412.7 544 448L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 448C96 412.7 124.7 384 160 384L240 384C240 428.2 275.8 464 320 464zM464 488C477.3 488 488 477.3 488 464C488 450.7 477.3 440 464 440C450.7 440 440 450.7 440 464C440 477.3 450.7 488 464 488z"/>
                        </svg>

                    </div>
                    
                </label>

                <input id="file-upload" type="file" name="photo">   

                <button type="submit" id="upload-button">Upload</button>
                
            </form>
            <?php endif; ?>
        </nav>

    </header>

    <main>

        <!-- Main Gallery -->
        <div class="gallery-container">
            <?php if ($isLoggedIn): ?>
                <?php 
                $result = $conn -> query("SELECT * from photos");

                while($row = $result -> fetch_assoc()){

                    $filename = htmlspecialchars($row["filename"]);
                    
                    echo '
                    
                    <div class="photo">
                        <img src= "foto-berdua/' . $filename . '" 
                            alt=""
                            data-filename="' . $filename . '" >
                    </div>

                    ';
                }
                ?>
            <?php else: ?>
                <h3 class="gallery-locked-msg">Login to see the gallery</h3>    
            <?php endif; ?>    
        </div>


        <!-- Full Screen Image -->
        <div id="image-modal">
            <span id="close-modal">&times;</span>

            <span class="photo-nav" id="image-before">&lt;</span>
            <span class="photo-nav" id="image-after">&gt;</span>

            <span id="delete-span">
                <?php if($isUploader): ?>
                <svg id="delete-icon" viewBox="0 0 640 640" type="submit">
                    <path fill="currentColor" d="M232.7 69.9L224 96L128 96C110.3 96 96 110.3 96 128C96 145.7 110.3 160 128 160L512 160C529.7 160 544 145.7 544 128C544 110.3 529.7 96 512 96L416 96L407.3 69.9C402.9 56.8 390.7 48 376.9 48L263.1 48C249.3 48 237.1 56.8 232.7 69.9zM512 208L128 208L149.1 531.1C150.7 556.4 171.7 576 197 576L443 576C468.3 576 489.3 556.4 490.9 531.1L512 208z"/> 
                </svg>
                <?php endif; ?>
            </span> 
            
            <img id="modal-image" src="" alt="">
        </div>


        <!-- Profile  -->
        <div id="profile-modal">
            <?php if($isLoggedIn): ?>

                <div id="account-bar">
                    <span class="close-profile-modal">&times;</span>
                    <h2>My Profile</h2>
                    <p id="profile-desc">Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                    (<?= htmlspecialchars($role) ?>)</p>
                    <button id="logout-button">Logout</button>
                </div>

            <?php else: ?>

            <div id="login-bar">
                <span class="close-profile-modal">&times;</span>

                <h2>My Profile</h2>

                <form id="login-form">

                    <input
                        type="text"
                        name="username"
                        id="login-username"
                        placeholder="Username"
                    >

                    <input
                        type="password"
                        name="password"
                        id="login-password"
                        placeholder="Password"
                    >

                    <button type="submit">Login</button>
                </form>

                <div id="login-foot">Don't have an account? register here!</div>

            </div>

            <div id="register-bar">
                <span class="close-profile-modal">&times;</span>

                <h2>My Profile</h2>

                <form id="register-form">

                    <input
                        type="text"
                        name="username"
                        id="register-username"
                        placeholder="Username"
                    >

                    <input
                        type="password"
                        name="password"
                        id="register-password"
                        placeholder="Password"
                    >

                    <button type="submit">Register</button> 
                </form>

                <div id="register-foot">Already have an account? login here!</div>

            </div>

            <?php endif; ?>

        </div>


    </main>    

</body>

</html>