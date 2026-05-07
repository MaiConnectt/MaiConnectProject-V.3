
//  * Archivo: dashboard.js
//  * Propósito: Scripts principales para la interfaz del Dashboard.
//  *            Manejo del menú lateral, dropdown de perfil, animaciones
//  *            al hacer scroll, y animación de contadores numéricos.


document.addEventListener('DOMContentLoaded', function () {

    // ===== ALTERNAR BARRA LATERAL (MÓVIL) =====
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // Cerrar la barra lateral al hacer clic fuera en dispositivos móviles
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // ===== MENÚ DESPLEGABLE DE PERFIL =====
    const profileButton = document.getElementById('profileButton');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileButton && profileDropdown) {
        profileButton.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        // Cerrar el menú desplegable al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!profileButton.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // ===== ANIMACIONES SUAVES AL HACER SCROLL =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observar tarjetas de estadísticas y de contenido
    document.querySelectorAll('.stat-card, .content-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });

    // ===== ELEMENTO DE NAVEGACIÓN ACTIVO =====
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-item').forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(currentPage)) {
            item.classList.add('active');
        }
    });

    // ===== SALUDO DINÁMICO =====
    const greetingElement = document.getElementById('greeting');
    if (greetingElement) {
        const hour = new Date().getHours();
        let greeting = 'Buenos días';

        if (hour >= 12 && hour < 18) {
            greeting = 'Buenas tardes';
        } else if (hour >= 18) {
            greeting = 'Buenas noches';
        }

        greetingElement.textContent = greeting;
    }

    // ===== ANIMACIÓN DE TARJETAS DE ESTADÍSTICAS =====
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Animar los valores de las estadísticas cuando entran en la pantalla
    const statObserver = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const valueElement = entry.target.querySelector('.stat-value');
                if (valueElement && !valueElement.dataset.animated) {
                    const endValue = parseInt(valueElement.textContent.replace(/[^0-9]/g, ''));
                    valueElement.dataset.animated = 'true';
                    animateValue(valueElement, 0, endValue, 1500);
                }
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-card').forEach(card => {
        statObserver.observe(card);
    });

    // ===== FUNCIONALIDAD DE TOOLTIPS =====
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function () {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';

            this.tooltipElement = tooltip;
        });

        element.addEventListener('mouseleave', function () {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.tooltipElement = null;
            }
        });
    });

    // ===== CONFIRMAR CIERRE DE SESIÓN =====
    const logoutLink = document.querySelector('a[href*="logout.php"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            MaiModal.confirm({
                title: 'Cerrar Sesión',
                message: '¿Estás seguro de que deseas cerrar sesión?',
                onConfirm: () => {
                    window.location.href = url;
                }
            });
        });
    }

    // ===== ACTUALIZACIÓN AUTOMÁTICA DE DATOS (Opcional) =====
    // ... (resto del archivo)
});
