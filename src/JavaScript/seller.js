/**
 * ===================================================================
 * Archivo: seller.js
 * Propósito: Interacciones base del módulo de ventas (Vendedor).
 *            Menú lateral en mobile, y animación de estadísticas de la vista.
 * ===================================================================
 */
document.addEventListener('DOMContentLoaded', function () {
    // Alternar menú de móvil
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });

        // Cerrar la barra lateral al hacer clic fuera en dispositivos móviles
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }

    // Animar estadísticas al cargar
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        stat.style.opacity = '0';
        stat.style.transform = 'translateY(20px)';

        setTimeout(() => {
            stat.style.transition = 'all 0.5s ease';
            stat.style.opacity = '1';
            stat.style.transform = 'translateY(0)';
        }, 100);
    });
});
