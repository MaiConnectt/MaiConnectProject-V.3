/**
 * ===================================================================
 * Archivo: landing.js
 * Propósito: Contiene los comportamientos e interacciones de la 
 *            página principal pública (index.php), como el efecto 
 *            del navbar al scrollear, menú móvil, botón flotante 
 *            y animaciones de revelado con Intersection Observer.
 * ===================================================================
 */

// Efecto de scroll de la barra de navegación
const navbar = document.getElementById('navbar');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }

    lastScroll = currentScroll;
});

// Alternar menú móvil
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Cerrar menú móvil al hacer clic en un enlace
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Botón de volver arriba
const scrollTopBtn = document.getElementById('scrollTop');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        scrollTopBtn.classList.add('visible');
    } else {
        scrollTopBtn.classList.remove('visible');
    }
});

scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Transición suave para enlaces ancla
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));

        if (target) {
            const offsetTop = target.offsetTop - 80;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// Intersection Observer para animaciones de desvanecimiento
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observar elementos para su animación
const animateElements = document.querySelectorAll('.feature-card, .product-card, .testimonial-card, .gallery-item');
animateElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// Añadir animación de carga
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
    }, 100);
});

// Prevenir comportamiento por defecto de enlaces a WhatsApp (dejarlos abrir normalmente)
document.querySelectorAll('a[href^="https://wa.me"]').forEach(link => {
    link.addEventListener('click', (e) => {
        // Dejar que ocurra el comportamiento por omisión (abrir en pestaña nueva)
        console.log('Opening WhatsApp...');
    });
});

console.log('Mai Shop - Website loaded successfully! 🎂');

// Interacciones de la ventana modal de Login
const loginLink = document.querySelector('a[href*=\x27src/Php/login/login.php\x27]');
const loginModal = document.getElementById('loginModal');
const closeModal = document.getElementById('closeModal');

if (loginLink && loginModal) {
    loginLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginModal.classList.add('active');
    });

    closeModal.addEventListener('click', () => {
        loginModal.classList.remove('active');
    });

    // Cerrar al hacer clic fuera
    loginModal.addEventListener('click', (e) => {
        if (e.target === loginModal) {
            loginModal.classList.remove('active');
        }
    });
}
