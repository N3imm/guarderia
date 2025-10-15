document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling para los enlaces del navbar
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 100; // Ajuste para navbar fijo
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Animaciones al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    // Observar elementos que deben animarse
    document.querySelectorAll('.card, .service-icon, .bg-primary.rounded-circle').forEach(el => {
        observer.observe(el);
    });
    
    // Navbar activo según scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    // --- Lógica para el Modo Oscuro ---
    const darkModeToggle = document.getElementById('darkModeToggle');
    let darkMode = localStorage.getItem('darkMode'); 

    const enableDarkMode = () => {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
        if(darkModeToggle) darkModeToggle.checked = true;
    }

    const disableDarkMode = () => {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', null);
        if(darkModeToggle) darkModeToggle.checked = false;
    }

    // Aplicar el tema al cargar la página
    if (darkMode === 'enabled') {
        enableDarkMode();
    }

    // Event listener para el interruptor
    if(darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
            darkMode = localStorage.getItem('darkMode'); 
            if (darkMode !== 'enabled') {
                enableDarkMode();
            } else {  
                disableDarkMode(); 
            }
        });
    }
});