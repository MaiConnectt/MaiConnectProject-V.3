<?php
/**
 * ===================================================================
 * Archivo: editar.php (Equipo)
 * Propósito: Formulario para la edición de los datos de un vendedor 
 *            existente (nombre, documento, email, teléfono, etc.).
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/src/Php/dashboard/equipo/equipo.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, u.nombre, u.apellido, u.email 
    FROM tbl_miembro m 
    JOIN tbl_usuario u ON m.id_usuario = u.id_usuario 
    WHERE m.id_miembro = ?
");
$stmt->execute([$id]);
$seller = $stmt->fetch();

if (!$seller) {
    header('Location: ' . BASE_URL . '/src/Php/dashboard/equipo/equipo.php');
    exit;
}
?>
<?php
$page_title = 'Editar Vendedor - Mai Shop';
$extra_css  = [BASE_URL . '/styles/equipo.css'];
require_once __DIR__ . '/../includes/head.php';
?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<main class="main-content" style="padding: 2rem 2rem;">
    <div style="max-width:640px; margin:0 auto; width:100%;">
    <a href="equipo.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al equipo</a>

    <div class="form-page-header" style="max-width:100%; margin-bottom:1.75rem;">
        <h2><i class="fas fa-user-edit"></i> Editar Vendedor</h2>
        <p>Modifica la información de <?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?></p>
    </div>

    <div class="form-container" style="max-width:100%; margin:0;">
        <form id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_miembro" value="<?php echo $seller['id_miembro']; ?>">

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
                            <input type="text" name="nombre" class="form-control" required
                                placeholder="Ej. Juan"
                                value="<?php echo htmlspecialchars($seller['nombre']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature label-icon"></i> Apellido
                            </label>
                            <input type="text" name="apellido" class="form-control" required
                                placeholder="Ej. Pérez"
                                value="<?php echo htmlspecialchars($seller['apellido']); ?>">
                        </div>
                    </div>
                    <?php
                    // Verificar si los campos de documento ya tienen valor guardado
                    $tiene_documento = !empty($seller['tipo_documento']) && !empty($seller['numero_documento']);
                    ?>
                    <div class="form-row col-1-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card label-icon"></i> Tipo Documento
                                <?php if ($tiene_documento): ?>
                                    <span style="margin-left:0.4rem;color:#c97c89;"><i class="fas fa-lock" style="font-size:0.75rem;"></i></span>
                                <?php endif; ?>
                            </label>
                            <?php if ($tiene_documento): ?>
                                <!-- Documento YA registrado: campo bloqueado -->
                                <select class="form-control" disabled
                                    style="background:#f5f5f5; color:#888; cursor:not-allowed; opacity:0.8;">
                                    <option value="CC" <?php echo $seller['tipo_documento'] === 'CC' ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                    <option value="TI" <?php echo $seller['tipo_documento'] === 'TI' ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                                    <option value="CE" <?php echo $seller['tipo_documento'] === 'CE' ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                </select>
                                <input type="hidden" name="tipo_documento" value="<?php echo htmlspecialchars($seller['tipo_documento']); ?>">
                            <?php else: ?>
                                <!-- Documento VACÍO: permitir que el admin lo llene -->
                                <select name="tipo_documento" class="form-control" required>
                                    <option value="">Seleccionar</option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-hashtag label-icon"></i> Número de Documento
                                <?php if ($tiene_documento): ?>
                                    <span title="El número de documento no puede modificarse" style="margin-left:0.4rem;color:#c97c89;"><i class="fas fa-lock" style="font-size:0.75rem;"></i></span>
                                <?php endif; ?>
                            </label>
                            <?php if ($tiene_documento): ?>
                                <!-- Documento YA registrado: campo bloqueado -->
                                <input type="text" class="form-control" readonly
                                    value="<?php echo htmlspecialchars($seller['numero_documento']); ?>"
                                    style="background:#f5f5f5; color:#888; cursor:not-allowed;"
                                    title="La cédula no puede modificarse">
                                <input type="hidden" name="numero_documento" value="<?php echo htmlspecialchars($seller['numero_documento']); ?>">
                                <span class="form-hint" style="color:#c97c89;"><i class="fas fa-lock"></i> Este campo no puede modificarse</span>
                            <?php else: ?>
                                <!-- Documento VACÍO: permitir ingreso por primera vez -->
                                <input type="text" name="numero_documento" class="form-control" required
                                    maxlength="15"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                    placeholder="Número de identificación">
                                <span class="form-hint">Ingresa el documento. Una vez guardado, no podrá modificarse.</span>
                            <?php endif; ?>
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
                        <input type="email" name="email" class="form-control" required
                            placeholder="email@ejemplo.com"
                            value="<?php echo htmlspecialchars($seller['email']); ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fab fa-whatsapp label-icon"></i> Teléfono / WhatsApp
                            </label>
                            <input type="tel" name="telefono" class="form-control" required
                                maxlength="10" minlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                placeholder="3001234567"
                                value="<?php echo htmlspecialchars($seller['telefono'] ?? ''); ?>">
                            <span class="form-hint">10 dígitos sin espacios ni guiones</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-university label-icon"></i> Universidad
                            </label>
                            <input type="text" name="universidad" class="form-control"
                                placeholder="Ej. Universidad Central"
                                value="<?php echo htmlspecialchars($seller['universidad'] ?? ''); ?>">
                        </div>
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
                            <i class="fas fa-toggle-on label-icon"></i> Estado de la Cuenta
                        </label>
                        <select name="estado" class="form-control">
                            <option value="activo"   <?php echo $seller['estado'] === 'activo'   ? 'selected' : ''; ?>>✅ Activo</option>
                            <option value="inactivo" <?php echo $seller['estado'] === 'inactivo' ? 'selected' : ''; ?>>⛔ Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </form>
    </div><!-- /form-container -->
    </div><!-- /max-width wrapper -->
</main>


<script>
    document.getElementById('editForm').addEventListener('submit', function (e) {
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
                        title: '¡Cambios Guardados!',
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