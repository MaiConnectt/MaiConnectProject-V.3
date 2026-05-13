/**
 * Archivo: productos_form.js
 * Propósito: Scripts de validación y envío (AJAX / Fetch) para el 
 *            formulario de crear y editar un producto.
 */
document.addEventListener('DOMContentLoaded', function () {
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('acciones.php', {
                    method: 'POST',
                    body: formData
                });
                const text = await res.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // Extract JSON if it was prepended with PHP Warnings like `<br /> <b>Warning</b>...`
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            data = JSON.parse(match[0]);
                        } catch (err) {
                            throw new Error("Recibimos una advertencia del servidor y falló la lectura de datos.");
                        }
                    } else {
                        if (text.includes("<b>Warning</b>") || text.includes("<b>Fatal error</b>")) {
                            throw new Error("El servidor bloqueó la acción. (Posiblemente la imagen es muy grande o hay un problema de base de datos).");
                        }
                        throw new Error("El servidor devolvió un texto inesperado: " + text.substring(0, 80));
                    }
                }

                if (data.success) {
                    MaiModal.alert({
                        title: '¡Producto Creado!',
                        message: data.message,
                        type: 'success',
                        onConfirm: () => {
                            window.location.href = 'productos.php';
                        }
                    });
                } else {
                    MaiModal.alert({
                        title: 'Error',
                        message: data.message,
                        type: 'danger'
                    });
                }
            } catch (err) {
                MaiModal.alert({
                    title: 'Error Técnico',
                    message: err.message || 'Ocurrió un error al procesar la solicitud.',
                    type: 'danger'
                });
            }
        });
    }
});
