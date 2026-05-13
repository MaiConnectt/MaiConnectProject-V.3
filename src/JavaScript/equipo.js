/*
 * Archivo: equipo.js
 * Propósito: Define las interacciones para el módulo de "Equipo".
 *            Contiene la lógica asíncrona (AJAX) para eliminar 
 *            y restaurar vendedores, confirmaciones mediante 
 *            modal, y atajos de teclado para búsqueda rápida.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Botones de eliminar
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            MaiModal.confirm({
                title: 'Eliminar Vendedor',
                message: `¿Estás seguro de que deseas eliminar a ${sellerName}? Esta acción no se puede deshacer de forma simple.`,
                confirmText: 'Eliminar',
                onConfirm: () => {
                    deleteSeller(sellerId);
                }
            });
        });
    });

    // Botones de restaurar
    const restoreButtons = document.querySelectorAll('.btn-restore');
    restoreButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            MaiModal.confirm({
                title: 'Restaurar Vendedor',
                message: `¿Deseas restaurar a ${sellerName} y regresarlo al estado inactivo?`,
                confirmText: 'Restaurar',
                onConfirm: () => {
                    restoreSeller(sellerId);
                }
            });
        });
    });

    /**
     * Envía una petición POST (vía Fetch API) a `acciones.php`
     * para realizar el borrado lógico de un vendedor.
     * @param {number|string} sellerId ID del miembro a eliminar
     */
async function deleteSeller(sellerId) {
    // Mostrar estado de carga
    MaiModal.showLoading('Eliminando...');

    try {
        // Enviar solicitud de eliminación
        const response = await fetch('acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id_miembro=${sellerId}`
        });
        
        const data = await response.json();

        if (data.success) {
            // Recargar página para mostrar la lista actualizada
            window.location.reload();
        } else {
            MaiModal.alert({
                title: 'Error',
                message: 'Error al eliminar vendedor: ' + (data.message || 'Error desconocido'),
                type: 'danger'
            });
            MaiModal.hideLoading('Eliminar');
        }
    } catch (error) {
        console.error('Error:', error);
        MaiModal.alert({
            title: 'Error',
            message: 'Error al eliminar vendedor. Por favor, intenta de nuevo.',
            type: 'danger'
        });
        MaiModal.hideLoading('Eliminar');
    }
}

/**
 * Envía una petición POST (vía Fetch API) a `acciones.php`
 * para reactivar (restaurar) a un vendedor previamente eliminado.
 * @param {number|string} sellerId ID del miembro a restaurar
 */
async function restoreSeller(sellerId) {
    // Mostrar estado de carga
    MaiModal.showLoading('Restaurando...');

    try {
        // Enviar solicitud de restauración
        const response = await fetch('acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=restore&id_miembro=${sellerId}`
        });
        
        const data = await response.json();

        if (data.success) {
            // Recargar página para mostrar la lista actualizada
            window.location.reload();
        } else {
            MaiModal.alert({
                title: 'Error',
                message: 'Error al restaurar vendedor: ' + (data.message || 'Error desconocido'),
                type: 'danger'
            });
            MaiModal.hideLoading('Restaurar');
        }
    } catch (error) {
        console.error('Error:', error);
        MaiModal.alert({
            title: 'Error',
            message: 'Error al restaurar vendedor. Por favor, intenta de nuevo.',
            type: 'danger'
        });
        MaiModal.hideLoading('Restaurar');
    }
}

// Funcionalidad de búsqueda
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.form.submit();
        }, 500);
    });
}

// Atajos de teclado
document.addEventListener('keydown', function (e) {
    // ESC para cerrar la ventana modal
    if (e.key === 'Escape') {
        MaiModal.close();
    }

    // Ctrl/Cmd + K para enfocar la búsqueda
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
});

// Animar tarjetas al hacer scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '0';
            entry.target.style.transform = 'translateY(20px)';

            setTimeout(() => {
                entry.target.style.transition = 'all 0.5s ease';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, 100);

            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

const sellerCards = document.querySelectorAll('.seller-card');
sellerCards.forEach(card => {
    observer.observe(card);
});
});
