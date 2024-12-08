function showLoadingPage(){
    const destination = this.getAttribute('href');
    
    event.preventDefault();

    const loadingPage = document.createElement('div');
    loadingPage.classList.add('loading-page');
    loadingPage.innerHTML = `
        <div class="loading-content">
            <h1><span class="logo-blue">Blue</span><span class="logo-reading">Reading</span></h1>
            <div class="loader"></div>
        </div>
    `;

    document.body.appendChild(loadingPage);

    setTimeout(() => {
        window.location.href = destination;
    }, 1000);

    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', showLoadingPage);
        });
    });
}


