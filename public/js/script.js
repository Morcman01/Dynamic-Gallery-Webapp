// ~ Page Variables ~
const modal = document.getElementById("image-modal");
const modalImage = document.getElementById("modal-image");
const closeModal = document.getElementById("close-modal");

const nextImage = document.getElementById("image-after");
const beforeImage = document.getElementById("image-before");
const deleteIcon = document.getElementById("delete-icon");

const profileIcon = document.getElementById("profile-icon");
const profileModal = document.getElementById("profile-modal");
const closeProfile = document.querySelectorAll(".close-profile-modal");

const loginBar = document.getElementById("login-bar");
const loginFoot = document.getElementById("login-foot");
const registerForm = document.getElementById("register-form");

const registerBar = document.getElementById("register-bar");
const registerFoot = document.getElementById("register-foot");
const loginForm = document.getElementById("login-form");

const photos = document.querySelectorAll(".photo img");

let currentIndex = 0;

//Profile modal
profileIcon.addEventListener("click", () =>{
    profileModal.classList.add("show");
    loginBar.classList.add("show");
});
loginFoot.addEventListener("click", () => {
    registerBar.classList.add("show");
    loginBar.classList.remove("show");
});
registerFoot.addEventListener("click", () => {
    loginBar.classList.add("show");
    registerBar.classList.remove("show");
});
closeProfile.forEach(closeButton => {
    closeButton.addEventListener("click", () => {
        profileModal.classList.remove("show");
        loginBar.classList.remove("show");
        registerBar.classList.remove("show");
    });
});

// photo click - open modal
photos.forEach((photo, index) => {
    photo.addEventListener("click", () => {
        currentIndex = index;
        modalImage.src = photo.src;
        modal.classList.add("show");
        deleteIcon.dataset.filename = photo.dataset.filename;
    });
});

// next/prev buttons
nextImage.addEventListener("click", () => {
    currentIndex++;
    if(currentIndex >= photos.length) currentIndex = 0;
    modalImage.src = photos[currentIndex].src;
});

beforeImage.addEventListener("click", () => {
    currentIndex--;
    if(currentIndex < 0) currentIndex = photos.length - 1;
    modalImage.src = photos[currentIndex].src;
});

// delete photo
deleteIcon.addEventListener("click", () => {
    const filename = deleteIcon.dataset.filename;

    Swal.fire({
        icon:'warning',
        title: 'Erase photo?',
        text: 'Photo will be deleted permanently',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak',
        confirmButtonColor: '#20571D'
    }).then(async (swalResult) => {
        if(swalResult.isConfirmed){
            try{
                const response = await fetch('delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({filename: filename})
                });

                const text = await response.text();

                console.log("DELETE RESPONSE:", text);  
                console.log("HTTP STATUS:", response.status);
                
                const data = JSON.parse(text.trim());

                // const data = await response.json;

                if(data.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            } catch(err){
                Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Something went wrong! ' + err.message
                });
            }
        }
    });
});

// keyboard navigation
document.addEventListener("keydown", (event) => {
    if(event.key === "ArrowLeft"){
        currentIndex--;
        if(currentIndex < 0) currentIndex = photos.length - 1;
        modalImage.src = photos[currentIndex].src;
    }
    if(event.key === "ArrowRight"){
        currentIndex++;
        if(currentIndex >= photos.length) currentIndex = 0;
        modalImage.src = photos[currentIndex].src;
    }
    if(event.key === "Escape"){
        modal.classList.remove("show");
    }
});

// close modal
closeModal.addEventListener("click", () => {
    modal.classList.remove("show");
});

modal.addEventListener("click", (event) => {
    if(event.target === modal){
        modal.classList.remove("show");
    }
});


// ─── FILE SIZE CHECK ───
document.getElementById('file-upload').addEventListener('change', function(){
    if(!this.files[0]) return;
    
    const file = this.files[0];
    const max_size = 8 * 1024 * 1024;

    if(file.size > max_size){
        Swal.fire({
            icon: 'warning',
            title: 'File size too big!',
            text: 'Max 8MB'
        });
        this.value = '';
        return;
    }

    //auto submit file
    this.closest('form').requestSubmit();
});


// ─── UPLOAD FORM SUBMIT ───
document.querySelector('form').addEventListener('submit', async function(e){
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('upload.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        const result = JSON.parse(text.trim());
        
        if(result.success){
            Swal.fire({
                icon: 'success',
                title: 'Uploaded!',
                text: 'Photo uploaded',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Upload failed',
                text: result.message
            });
        }
    } catch(err) {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Something went wrong! ' + err.message
        });
    }
});


// Register Form Submit
registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const username = document.getElementById("register-username").value;
    const password = document.getElementById("register-password").value;

    try {
        const response = await fetch("register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, password })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Account created!',
                text: result.message,
                timer: 1800,
                showConfirmButton: false
            }).then(() => {
                // switch over to login bar so they can sign in right away
                registerBar.classList.remove("show");
                loginBar.classList.add("show");
                registerForm.reset();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Registration failed',
                text: result.message
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Something went wrong! ' + err.message
        });
    }
});


// Login Form Submit
loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const username = document.getElementById("login-username").value;
    const password = document.getElementById("login-password").value;

    try {
        const response = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, password })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Welcome back!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Login failed',
                text: result.message
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Something went wrong! ' + err.message
        });
    }
});