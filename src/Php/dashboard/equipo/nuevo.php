<?php
/**
 * ===================================================================
 * Archivo: nuevo.php (Equipo)
 * Propósito: Formulario para registrar e incorporar un nuevo vendedor 
 *            (usuario logueable) al sistema de Mai Shop.
 * ===================================================================
 */

/* Verifica que el usuario tenga sesión activa (si no, redirige al login) */
require_once __DIR__ . '/../auth.php';

/* Conecta a la base de datos PostgreSQL y define BASE_URL */
require_once __DIR__ . '/../../config/conexion.php';
?>
<?php
/* Configura el título de la pestaña del navegador */
$page_title = 'Nuevo Vendedor - Mai Shop';

/* Carga la hoja de estilos CSS específica para el módulo de equipo */
$extra_css  = [BASE_URL . '/styles/equipo.css'];

/* Incluye el <head> HTML reutilizable (fuentes, íconos, meta tags) */
require_once __DIR__ . '/../includes/head.php';
?>

    <!-- Contenedor principal del dashboard (sidebar + contenido) -->
    <div class="dashboard-container">

        <!-- Incluye la barra lateral de navegación (menú izquierdo) -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Área principal de contenido -->
        <main class="main-content" style="padding: 2rem 2rem;">

            <!-- Wrapper que centra el formulario y limita su ancho máximo -->
            <div style="max-width:640px; margin:0 auto; width:100%;">

            <!-- Botón para regresar a la lista de vendedores -->
            <a href="equipo.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al equipo</a>

            <!-- Encabezado de la página con título e instrucción -->
            <div class="form-page-header" style="max-width:100%; margin-bottom:1.75rem;">
                <h2><i class="fas fa-user-plus"></i> Nuevo Vendedor</h2>
                <p>Registra un nuevo vendedor en el sistema</p>
            </div>

            <!-- Contenedor del formulario -->
            <div class="form-container" style="max-width:100%; margin:0;">

                <!-- 
                    Formulario principal: Se envía vía JavaScript (AJAX) a acciones.php
                    - autocomplete="off" evita que el navegador autocomplete los campos
                    - El campo oculto "action" indica a acciones.php qué operación ejecutar (create)
                -->
                <form id="sellerForm" autocomplete="off">
                    <input type="hidden" name="action" value="create">

                    <!-- ========================================== -->
                    <!-- SECCIÓN 1: Datos Personales del Vendedor   -->
                    <!-- ========================================== -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-user"></i>
                            <span>Datos Personales</span>
                        </div>
                        <div class="form-card-body">
                            <!-- Fila con 2 columnas: Nombre y Apellido -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-signature label-icon"></i> Nombre
                                    </label>
                                    <!-- Campo obligatorio (required) para el nombre del vendedor -->
                                    <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-signature label-icon"></i> Apellido
                                    </label>
                                    <!-- Campo obligatorio para el apellido -->
                                    <input type="text" name="apellido" class="form-control" required placeholder="Ej. Pérez">
                                </div>
                            </div>

                            <!-- Fila con 2 columnas: Tipo y Número de Documento -->
                            <div class="form-row col-1-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-card label-icon"></i> Tipo Documento
                                    </label>
                                    <!-- Lista desplegable con los tipos de documento colombianos -->
                                    <select name="tipo_documento" class="form-control" required>
                                        <option value="">Seleccionar</option>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag label-icon"></i> Número de Documento
                                    </label>
                                    <!-- 
                                        oninput: Filtra en tiempo real para aceptar SOLO números
                                        maxlength="15": Máximo 15 caracteres
                                    -->
                                    <input type="text" name="numero_documento" class="form-control" required
                                        maxlength="15"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        placeholder="Número de identificación">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- SECCIÓN 2: Información de Contacto         -->
                    <!-- ========================================== -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-address-book"></i>
                            <span>Información de Contacto</span>
                        </div>
                        <div class="form-card-body">
                            <!-- Campo de correo electrónico (se usará como usuario de login) -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope label-icon"></i> Correo Electrónico
                                </label>
                                <input type="email" name="email" class="form-control" required placeholder="email@ejemplo.com" autocomplete="off">
                            </div>

                            <!-- Fila con 2 columnas: Teléfono y Universidad -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-whatsapp label-icon"></i> Teléfono / WhatsApp
                                    </label>
                                    <!-- 
                                        Validación del teléfono:
                                        - minlength/maxlength: Exactamente 10 dígitos
                                        - oninput: Solo permite números (elimina letras en tiempo real)
                                    -->
                                    <input type="tel" name="telefono" class="form-control" required
                                        maxlength="10" minlength="10"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        placeholder="3001234567">
                                    <span class="form-hint">10 dígitos sin espacios ni guiones</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-university label-icon"></i> Universidad
                                    </label>
                                    <!-- Campo opcional: la universidad del vendedor -->
                                    <input type="text" name="universidad" class="form-control"
                                        placeholder="Ej. Universidad Central">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- SECCIÓN 3: Credenciales de Acceso (Login)  -->
                    <!-- ========================================== -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-lock"></i>
                            <span>Credenciales de Acceso</span>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-key label-icon"></i> Contraseña
                                </label>
                                <!-- 
                                    La contraseña se enviará a acciones.php donde será 
                                    encriptada con password_hash() antes de guardarla en la BD
                                    autocomplete="new-password" evita que el navegador autocomplete
                                -->
                                <input type="password" name="password" class="form-control" required placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- SECCIÓN 4: Configuración Inicial           -->
                    <!-- ========================================== -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-sliders-h"></i>
                            <span>Configuración</span>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group" style="max-width: 220px;">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on label-icon"></i> Estado Inicial
                                </label>
                                <!-- Define si el vendedor puede loguearse de inmediato (activo) o no (inactivo) -->
                                <select name="estado" class="form-control">
                                    <option value="activo">✅ Activo</option>
                                    <option value="inactivo">⛔ Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Botón que dispara el envío del formulario -->
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Crear Vendedor
                    </button>
                </form>
            </div><!-- /form-container -->
            </div><!-- /max-width wrapper -->
        </main>
    </div>


<!-- ========================================== -->
<!-- JAVASCRIPT: Envío del formulario vía AJAX  -->
<!-- ========================================== -->
<script>
    /**
     * Escucha el evento "submit" del formulario.
     * En lugar de recargar la página (comportamiento por defecto),
     * envía los datos al servidor de forma asíncrona (AJAX con Fetch API).
     */
    document.getElementById('sellerForm').addEventListener('submit', function (e) {
        // Previene el envío tradicional del formulario (que recargaría la página)
        e.preventDefault();

        // FormData captura todos los campos del formulario automáticamente
        const formData = new FormData(this);

        // Envía los datos por POST a acciones.php (que procesa la creación)
        fetch('acciones.php', {
            method: 'POST',
            body: formData
        })
            // Convierte la respuesta del servidor a JSON
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Si fue exitoso: muestra modal de éxito y redirige a la lista de equipo
                    MaiModal.alert({
                        title: '¡Vendedor Creado!',
                        message: data.message,
                        type: 'success',
                        onConfirm: () => {
                            window.location.href = 'equipo.php';
                        }
                    });
                } else {
                    // Si hubo un error controlado: muestra el mensaje de error
                    MaiModal.alert({
                        title: 'Error',
                        message: data.message,
                        type: 'danger'
                    });
                }
            })
            // Si hubo un error técnico (red, servidor caído, etc.)
            .catch(err => {
                MaiModal.alert({
                    title: 'Error Técnico',
                    message: err.message,
                    type: 'danger'
                });
            });
    });
</script>

<!-- Incluye el footer reutilizable (cierra tags HTML, carga scripts comunes como mai-modal.js) -->
<?php
$extra_scripts = [];
require_once __DIR__ . '/../includes/footer.php';
?>