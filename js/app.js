document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', showLoadingPage);
        });
    });

function showLoadingPage(event){
    const destination = this.getAttribute('href');
    
    event.preventDefault();

    const loadingPage = document.createElement('div');
    loadingPage.classList.add('loading-page');
    loadingPage.style.backgroundColor = '#fff';
    loadingPage.innerHTML = `
        <div class="loading-content">
            <h1><span class="logo-blue">Blue</span><span class="logo-reading">Reading</span></h1>
            <div class="loader"></div>
        </div>
    `;

    document.body.appendChild(loadingPage);

    setTimeout(() => {
        window.location.href = destination;
    }, 800);
    

    
}

function handleReturnButton(event){
    event.preventDefault();
    if(document.referrer){
        window.location.href = document.referrer;
    }else{
        window.location.href = '../index.html';
    }
}

function showFileUploadForm(){
    const form = document.getElementById('fileUploadForm');
    if (form) {
        form.classList.remove('d-none');
    } else {
        console.error("L'élément 'fileUploadForm' n'a pas été trouvé");
    }
}

function hideFileUploadForm(){
    const form = document.getElementById('fileUploadForm');
    if (form) {
        form.classList.add('d-none');
    } else {
        console.error("L'élément 'fileUploadForm' n'a pas été trouvé");
    }
}
