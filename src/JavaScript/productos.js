/**
 * ===================================================================
 * Archivo: productos.js
 * Propósito: Interacciones para la gestión del catálogo de productos.
 *            Incluye confirmación para eliminar usando MaiModal,
 *            animación scroll y envío diferido del buscador (debounce).
 * ===================================================================
 */
document.addEventListener('DOMContentLoaded', function () {
    // Confirmación de eliminación de producto
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;

            MaiModal.confirm({
                title: 'Eliminar Producto',
                message: `¿Estás seguro de que deseas eliminar "${productName}"? Esta acción no se puede deshacer.`,
                confirmText: 'Eliminar',
                onConfirm: () => {
                    deleteProduct(productId);
                }
            });
        });
    });

    // Debounce de búsqueda
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
});

/**
 * Llama al backend en `acciones.php` para realizar el borrado
 * de un producto definido. Funciona con validación modal previa.
 * @param {string|number} productId ID del producto a eliminar
 */
function deleteProduct(productId) {
    // Mostrar carga en la ventana modal
    MaiModal.showLoading('Eliminando...');

    // Enviar solicitud de eliminación
    fetch('acciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&id_producto=${productId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaiModal.close();
                showNotification('Producto eliminado exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MaiModal.alert({
                    title: 'Error',
                    message: data.message || 'Error al eliminar producto',
                    type: 'danger'
                });
                MaiModal.hideLoading('Eliminar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            MaiModal.alert({
                title: 'Error de Red',
                message: 'No se pudo comunicar con el servidor.',
                type: 'danger'
            });
            MaiModal.hideLoading('Eliminar');
        });
}

/**
 * Crea una alerta flotante en pantalla y la retira de manera segura tras 3 segundos.
 * @param {string} message Texto que leerá el usuario.
 * @param {string} type Tipo visual ('info', 'success', 'error').
 */
function showNotification(message, type = 'info') {
    // Eliminar notificaciones existentes
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    const icon = type === 'success' ? 'fa-check-circle' :
        type === 'error' ? 'fa-exclamation-circle' :
            'fa-info-circle';

    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    // Mostrar notificación
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Ocultar notificación después de 3 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}
