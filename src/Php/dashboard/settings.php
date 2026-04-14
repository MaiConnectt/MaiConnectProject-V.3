<?php
/**
 * ===================================================================
 * Archivo: settings.php
 * Propósito: Página de configuración del perfil del usuario. 
 *            Permite la actualización de datos personales (nombre, 
 *            apellido, email) y cambio de contraseña.
 * ===================================================================
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/conexion.php';

$message = '';
$messageType = '';

// Obtener datos actuales del usuario
try {
    $stmt = $pdo->prepare("SELECT nombre, apellido, email FROM tbl_usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    $message = 'Error al cargar datos: ' . $e->getMessage();
    $messageType = 'error';
    $user_data = ['nombre' => '', 'apellido' => '', 'email' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Actualizar Perfil
        $nombre = trim($_POST['first_name']);
        $apellido = trim($_POST['last_name']);
        $email = trim($_POST['email']);

        try {
            $update = $pdo->prepare("UPDATE tbl_usuario SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?");
            $update->execute([$nombre, $apellido, $email, $_SESSION['user_id']]);

            // Actualizar datos locales para reflejar cambios
            $user_data['nombre'] = $nombre;
            $user_data['apellido'] = $apellido;
            $user_data['email'] = $email;

            $message = 'Perfil actualizado correctamente.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error al actualizar perfil: ' . $e->getMessage();
            $messageType = 'error';
        }

    } elseif (isset($_POST['update_password'])) {
        // Actualizar Contraseña
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $message = 'Las nuevas contraseñas no coinciden.';
            $messageType = 'error';
        } else {
            try {
                // Obtener contraseña actual
                $stmt = $pdo->prepare("SELECT contrasena FROM tbl_usuario WHERE id_usuario = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $db_user = $stmt->fetch();

                if ($db_user && password_verify($current_password, $db_user['contrasena'])) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE tbl_usuario SET contrasena = ? WHERE id_usuario = ?");
                    $update->execute([$new_hash, $_SESSION['user_id']]);

                    $message = 'Contraseña actualizada correctamente.';
                    $messageType = 'success';
                } else {
                    $message = 'La contraseña actual es incorrecta.';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Error al actualizar contraseña: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>
<?php
$page_title = 'Configuración - Mai Shop';
require_once __DIR__ . '/includes/head.php';
?>
<!-- Barra lateral -->
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main-content" style="padding: 2rem 2.5rem;">

    <!-- ===== HERO BANNER ===== -->
    <div style="
        background: linear-gradient(135deg, #c97c89 0%, #a65c68 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(201,124,137,0.35);
    ">
        <div style="position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
        <div style="position:absolute;right:80px;bottom:-60px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>

        <!-- Avatar -->
        <div style="
            width:80px; height:80px;
            border-radius:50%;
            background:rgba(255,255,255,0.25);
            backdrop-filter:blur(10px);
            border:3px solid rgba(255,255,255,0.5);
            display:flex; align-items:center; justify-content:center;
            font-size:1.75rem; font-weight:700; color:white;
            flex-shrink:0; letter-spacing:1px;
            position:relative; z-index:1;
        ">
            <?php echo strtoupper(substr($user_data['nombre'], 0, 1) . substr($user_data['apellido'], 0, 1)); ?>
        </div>

        <div style="position:relative; z-index:1;">
            <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:white;margin:0 0 0.25rem 0;font-weight:700;">
                <?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellido']); ?>
            </h1>
            <p style="color:rgba(255,255,255,0.8);font-size:0.875rem;margin:0;">
                <i class="fas fa-envelope" style="margin-right:0.4rem;opacity:0.8;"></i>
                <?php echo htmlspecialchars($user_data['email']); ?>
                &nbsp;&nbsp;
                <i class="fas fa-shield-alt" style="margin-right:0.4rem;opacity:0.8;"></i>
                Administrador
            </p>
        </div>
    </div>

    <!-- ===== MENSAJE ===== -->
    <?php if ($message): ?>
        <div style="
            display:flex; align-items:center; gap:0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            <?php echo $messageType === 'success'
                ? 'background:#c6f6d5; color:#22543d; border:1px solid #9ae6b4;'
                : 'background:#fed7d7; color:#742a2a; border:1px solid #fc8181;'; ?>
        ">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>" style="font-size:1.1rem;"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- ===== DOS CARDS LADO A LADO ===== -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

        <!-- CARD: Información Personal -->
        <div style="background:white; border-radius:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; border-left:4px solid #c97c89;">
            <div style="padding:1.5rem 1.75rem; border-bottom:1px solid #faf0f2; display:flex; align-items:center; gap:0.75rem;">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#f9e4e8,#f5c6ce);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-circle" style="color:#c97c89;font-size:1rem;"></i>
                </div>
                <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;color:#2d3748;margin:0;">Información Personal</h3>
            </div>

            <form method="POST" action="settings.php" style="padding:1.75rem;">
                <input type="hidden" name="update_profile" value="1">

                <div style="margin-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-signature"></i> Nombre
                    </label>
                    <input type="text" name="first_name" required
                        value="<?php echo htmlspecialchars($user_data['nombre']); ?>"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-signature"></i> Apellido
                    </label>
                    <input type="text" name="last_name" required
                        value="<?php echo htmlspecialchars($user_data['apellido']); ?>"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                </div>

                <div style="margin-bottom:1.75rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" name="email" required
                        value="<?php echo htmlspecialchars($user_data['email']); ?>"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                </div>

                <button type="submit" style="
                    width:100%; padding:0.9rem;
                    background:linear-gradient(135deg,#c97c89,#a65c68);
                    color:white; border:none; border-radius:14px;
                    font-family:'Poppins',sans-serif; font-size:0.95rem; font-weight:600;
                    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;
                    box-shadow:0 4px 14px rgba(201,124,137,0.35);
                    transition:all 0.25s ease;
                "
                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(201,124,137,0.45)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(201,124,137,0.35)'">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>

        <!-- CARD: Seguridad -->
        <div style="background:white; border-radius:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; border-left:4px solid #c97c89;">
            <div style="padding:1.5rem 1.75rem; border-bottom:1px solid #faf0f2; display:flex; align-items:center; gap:0.75rem;">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#f9e4e8,#f5c6ce);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-lock" style="color:#c97c89;font-size:1rem;"></i>
                </div>
                <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;color:#2d3748;margin:0;">Seguridad</h3>
            </div>

            <form method="POST" action="settings.php" style="padding:1.75rem;" autocomplete="off">
                <input type="hidden" name="update_password" value="1">

                <div style="margin-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-key"></i> Contraseña Actual
                    </label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-lock"></i> Nueva Contraseña
                    </label>
                    <input type="password" name="new_password" required minlength="6" autocomplete="new-password"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                    <div style="font-size:0.78rem;color:#a0aec0;margin-top:0.35rem;"><i class="fas fa-info-circle"></i> Mínimo 6 caracteres</div>
                </div>

                <div style="margin-bottom:1.75rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">
                        <i class="fas fa-check-double"></i> Confirmar Contraseña
                    </label>
                    <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password"
                        style="width:100%;padding:0.75rem 1rem;border:1.5px solid #f0e4e6;border-radius:12px;font-size:0.9rem;color:#2d3748;outline:none;transition:border-color 0.2s;background:#fdfbfb;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#c97c89'" onblur="this.style.borderColor='#f0e4e6'">
                </div>

                <button type="submit" style="
                    width:100%; padding:0.9rem;
                    background:linear-gradient(135deg,#c97c89,#a65c68);
                    color:white; border:none; border-radius:14px;
                    font-family:'Poppins',sans-serif; font-size:0.95rem; font-weight:600;
                    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;
                    box-shadow:0 4px 14px rgba(201,124,137,0.35);
                    transition:all 0.25s ease;
                "
                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(201,124,137,0.45)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(201,124,137,0.35)'">
                    <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                </button>
            </form>
        </div>

    </div>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>