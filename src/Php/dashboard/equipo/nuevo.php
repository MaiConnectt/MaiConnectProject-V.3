<?php
/**
 * ===================================================================
 * Archivo: nuevo.php (Equipo)
 * Propósito: Formulario para registrar e incorporar un nuevo vendedor 
 *            (usuario logueable) al sistema de Mai Shop.
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';
?>
<?php
$page_title = 'Nuevo Vendedor - Mai Shop';
$extra_css  = [BASE_URL . '/styles/equipo.css'];
require_once __DIR__ . '/../includes/head.php';
?>

    <div class="dashboard-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <main class="main-content" style="padding: 2rem 2rem;">
            <div style="max-width:640px; margin:0 auto; width:100%;">
            <a href="equipo.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al equipo</a>

            <div class="form-page-header" style="max-width:100%; margin-bottom:1.75rem;">
                <h2><i class="fas fa-user-plus"></i> Nuevo Vendedor</h2>
                <p>Registra un nuevo vendedor en el sistema</p>
            </div>

            <div class="form-container" style="max-width:100%; margin:0;">
                <form id="sellerForm" autocomplete="off">
                    <input type="hidden" name="action" value="create">

                    <!-- SECCIÓN: Datos Personales -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-user"></i>
                            <span>Datos Personales</span>
                        </div>
                        <div class="form-card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-signature label-icon"></i> Nombre
                                    </label>
                                    <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-signature label-icon"></i> Apellido
                                    </label>
                                    <input type="text" name="apellido" class="form-control" required placeholder="Ej. Pérez">
                                </div>
                            </div>
                            <div class="form-row col-1-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-card label-icon"></i> Tipo Documento
                                    </label>
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
                                    <input type="text" name="numero_documento" class="form-control" required
                                        maxlength="15"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        placeholder="Número de identificación">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Contacto -->
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-address-book"></i>
                            <span>Información de Contacto</span>
                        </div>
                        <div class="form-card-body">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope label-icon"></i> Correo Electrónico
                                </label>
                                <input type="email" name="email" class="form-control" required placeholder="email@ejemplo.com" autocomplete="off">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-whatsapp label-icon"></i> Teléfono / WhatsApp
                                    </label>
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
                                    <input type="text" name="universidad" class="form-control"
                                        placeholder="Ej. Universidad Central">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Acceso -->
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
                                <input type="password" name="password" class="form-control" required placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Configuración -->
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
                                <select name="estado" class="form-control">
                                    <option value="activo">✅ Activo</option>
                                    <option value="inactivo">⛔ Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Crear Vendedor
                    </button>
                </form>
            </div><!-- /form-container -->
            </div><!-- /max-width wrapper -->
        </main>
    </div>


<script>
    document.getElementById('sellerForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('acciones.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    MaiModal.alert({
                        title: '¡Vendedor Creado!',
                        message: data.message,
                        type: 'success',
                        onConfirm: () => {
                            window.location.href = 'equipo.php';
                        }
                    });
                } else {
                    MaiModal.alert({
                        title: 'Error',
                        message: data.message,
                        type: 'danger'
                    });
                }
            })
            .catch(err => {
                MaiModal.alert({
                    title: 'Error Técnico',
                    message: err.message,
                    type: 'danger'
                });
            });
    });
</script>
<?php
$extra_scripts = [];
require_once __DIR__ . '/../includes/footer.php';
?>