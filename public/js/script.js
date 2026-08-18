// ~ Page Variables ~
const modal = document.getElementById("image-modal");
const modalImage = document.getElementById("modal-image");
const closeModal = document.getElementById("close-modal");

const nextImage = document.getElementById("image-after");
const beforeImage = document.getElementById("image-before");
const deleteIcon = document.getElementById("delete-icon");      //null unless uploader

const profileIcon = document.getElementById("profile-icon");
const profileModal = document.getElementById("profile-modal");
const closeProfile = document.querySelectorAll(".close-profile-modal");

const loginBar = document.getElementById("login-bar");          //null if logged in
const loginFoot = document.getElementById("login-foot");        //null if logged in
const loginForm = document.getElementById("login-form");        //null if logged in

const registerBar = document.getElementById("register-bar");    //null if logged in
const registerFoot = document.getElementById("register-foot");  //null if logged in
const registerForm = document.getElementById("register-form");  //null if logged in

const accountBar = document.getElementById("account-bar");      // null if guest
const logoutButton = document.getElementById("logout-button");  // null if guest

const uploadForm = document.getElementById('upload-form');      // null unless uploader
const fileUpload = document.getElementById('file-upload');      // null unless uploader

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const photos = document.querySelectorAll(".photo img");

let currentIndex = 0;

//input html encoding 
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

//Profile modal
profileIcon.addEventListener("click", () => {
    profileModal.classList.add("show");
    if (accountBar) {
        accountBar.classList.add("show");
    } else if (loginBar) {
        loginBar.classList.add("show");
    }
});

if (loginFoot) {
    loginFoot.addEventListener("click", () => {
        registerBar.classList.add("show");
        loginBar.classList.remove("show");
    });
}

if (registerFoot) {
    registerFoot.addEventListener("click", () => {
        loginBar.classList.add("show");
        registerBar.classList.remove("show");
    });
}

closeProfile.forEach(closeButton => {
    closeButton.addEventListener("click", () => {
        profileModal.classList.remove("show");
        loginBar?.classList.remove("show");
        registerBar?.classList.remove("show");
        accountBar?.classList.remove("show");
    });
});

if (logoutButton) {
    logoutButton.addEventListener("click", async () => {
        try {
            await fetch('logout.php', { 
                method: 'POST',
                headers: { "X-CSRF-Token": csrfToken } 
            });
        } finally {
            location.reload();
        }
    });
}

// photo click - open modal
photos.forEach((photo, index) => {
    photo.addEventListener("click", () => {
        currentIndex = index;
        modalImage.src = photo.src;
        modal.classList.add("show");
        if (deleteIcon) deleteIcon.dataset.filename = photo.dataset.filename;
    });
});

// next/prev buttons
nextImage.addEventListener("click", () => {
    currentIndex++;
    if (currentIndex >= photos.length) currentIndex = 0;
    if (photos.length) modalImage.src = photos[currentIndex].src;
});

beforeImage.addEventListener("click", () => {
    currentIndex--;
    if(currentIndex < 0) currentIndex = photos.length - 1;
    if (photos.length) modalImage.src = photos[currentIndex].src;
});

// delete photo
if (deleteIcon){
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
                        headers: {'Content-Type': 'application/json', "X-CSRF-Token": csrfToken},
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
}

// keyboard navigation
document.addEventListener("keydown", (event) => {
    if (!photos.length) return;
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
if (fileUpload){
    fileUpload.addEventListener('change', function(){
        if(!this.files.length) return;
    
        const max_size = 8 * 1024 * 1024;

        for(const file of this.files){
            if(file.size > max_size){
                Swal.fire({
                    icon: 'warning',
                    title: 'File size too big!',
                    text: 'Max 8MB'
                });

                this.value = '';
                return;
            }
        }        

        //auto submit file
        this.closest('form').requestSubmit();
    });
}


// ─── UPLOAD FORM SUBMIT ───
if(uploadForm){
    uploadForm.addEventListener('submit', async function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                headers: { "X-CSRF-Token": csrfToken },
                body: formData
            });
            
            const text = await response.text();
            const result = JSON.parse(text.trim());
            
            if (result.success && result.failed.length === 0) {
                // full success
                Swal.fire({
                    icon: 'success',
                    title: 'Uploaded!',
                    text: `${result.uploaded} photo(s) uploaded`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());

            } else if (result.success && result.failed.length > 0) {
                // partial success
                Swal.fire({
                    icon: 'warning',
                    title: 'Upload partially completed',
                    html: `
                        <p>${result.uploaded} photo(s) uploaded successfully.</p>
                        <p>The following files failed:</p>
                        <ul style="text-align: left;">
                            ${result.failed.map(file => `<li>${escapeHtml(file)}</li>`).join('')}
                        </ul>
                    `,
                    confirmButtonText: "OK"
                }).then(() => location.reload());

            } else {
                // total failure
                Swal.fire({
                    icon: 'error',
                    title: 'Upload failed',
                    html: result.failed && result.failed.length
                        ? `<ul style="text-align:left;">${result.failed.map(f => `<li>${escapeHtml(f)}</li>`).join('')}</ul>`
                        : (result.message || 'Something went wrong')
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
}


// Register Form Submit
if(registerForm){
    registerForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const username = document.getElementById("register-username").value;
        const password = document.getElementById("register-password").value;

        try {
            const response = await fetch("register.php", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json", 
                    "X-CSRF-Token": csrfToken
                },
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
}


// Login Form Submit
if(loginForm){
    loginForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const username = document.getElementById("login-username").value;
        const password = document.getElementById("login-password").value;

        try {
            const response = await fetch("login.php", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json", 
                    "X-CSRF-Token": csrfToken
                },
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
}