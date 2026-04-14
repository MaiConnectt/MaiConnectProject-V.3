<?php
/**
 * ===================================================================
 * Archivo: perfil.php (Seller)
 * Propósito: Muestra y permite editar la información personal del 
 *            vendedor (como teléfono y universidad). Además, resume
 *            sus estadísticas acumuladas de ventas y comisiones.
 * ===================================================================
 */
require_once __DIR__ . '/seller_auth.php';

$success_message = null;
$error_message = null;

// Actualizar perfil (Teléfono en tbl_miembro y Contraseña en tbl_usuario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if (isset($_POST['phone'])) {
            $phone = trim($_POST['phone']);
            $stmt = $pdo->prepare("UPDATE tbl_miembro SET telefono = ? WHERE id_miembro = ?");
            $stmt->execute([$phone, $_SESSION['seller_id']]);
        }

        if (!empty($_POST['new_password'])) {
            $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = ? WHERE id_usuario = ?");
            $stmt->execute([$newPassword, $_SESSION['user_id']]);
        }

        $pdo->commit();
        $success_message = "Tu perfil se ha actualizado correctamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Hubo un error al guardar los cambios.";
    }
}

// Obtener información real del vendedor mediante la vista
try {
    $stmt = $pdo->prepare("SELECT * FROM vw_comisiones_vendedor WHERE id_miembro = ?");
    $stmt->execute([$_SESSION['seller_id']]);
    $seller_info = $stmt->fetch();
} catch (PDOException $e) {
    $seller_info = null;
}

$pageTitle = 'Mi Perfil';
$extraStyles = '
    .profile-hero {
        background: linear-gradient(135deg, rgba(201, 124, 137, 0.9), rgba(166, 92, 104, 0.9));
        border-radius: 20px;
        padding: 3rem 2.5rem 6rem 2.5rem;
        color: white;
        margin-bottom: -4rem; /* Solapamiento de tarjetas */
        position: relative;
        box-shadow: 0 10px 30px rgba(166, 92, 104, 0.2);
    }
    
    .profile-main-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        position: relative;
        z-index: 10;
        padding: 0 2.5rem;
    }

    /* Tarjeta Identidad */
    .id-card {
        background: white;
        border-radius: 18px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(201,124,137,0.1);
    }
    .id-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c97c89, #a65c68);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        margin: 0 auto 1.5rem auto;
        box-shadow: 0 8px 20px rgba(201, 124, 137, 0.3);
        border: 4px solid white;
    }
    .id-name { font-size: 1.6rem; color: #a65c68; font-family: "Playfair Display", serif; margin-bottom: 0.3rem;}
    .id-role { font-size: 0.9rem; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;}
    .id-uni-badge {
        display: inline-block;
        background: rgba(201, 124, 137, 0.1);
        color: #c97c89;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-top: 1rem;
        border: 1px solid rgba(201, 124, 137, 0.2);
    }
    
    .id-contact { margin-top: 2rem; text-align: left; }
    .id-contact p { margin-bottom: 0.8rem; font-size: 0.9rem; color: #555; display: flex; align-items: center; gap: 0.75rem;}
    .id-contact i { color: #c97c89; width: 20px; text-align: center;}

    /* Estadísticas Modernas */
    .stats-layout {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .mini-stat {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.03);
        border: 1px solid rgba(201,124,137,0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease;
    }
    .mini-stat:hover { transform: translateY(-3px); }
    .ms-icon { font-size: 1.8rem; color: #c97c89; margin-bottom: 1rem; }
    .ms-val { font-size: 1.7rem; font-family: "Playfair Display", serif; color: #a65c68; font-weight: 700; margin-bottom: 0.2rem;}
    .ms-lbl { font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px;}

    /* Ajustes Grid */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
    .form-label { font-weight: 600; color: #555; font-size: 0.85rem; }
    .form-input {
        padding: 0.8rem 1.2rem;
        border: 1px solid rgba(201,124,137,0.2);
        border-radius: 10px;
        font-family: inherit;
        background: #fdfdfd;
        color: #333;
        transition: all 0.3s;
    }
    .form-input:focus {
        border-color: #c97c89;
        background: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(201, 124, 137, 0.1);
    }
    .btn-save {
        margin-top: 1.5rem;
        background: linear-gradient(135deg, #c97c89, #a65c68);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(201, 124, 137, 0.3);
        transition: all 0.3s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(201, 124, 137, 0.4); }

    @media(max-width: 900px) {
        .profile-main-grid { grid-template-columns: 1fr; }
        .stats-layout { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
    }
';

include __DIR__ . '/includes/header.php';
?>

<div class="profile-hero">
    <h1 style="margin: 0; font-family: 'Playfair Display', serif;">Mi Espacio</h1>
    <p style="margin-top: 0.5rem; opacity: 0.9;">Controla tus datos y celebra tus logros, Mai Connect es tuyo.</p>
</div>

<?php if ($seller_info): ?>
    <div class="profile-main-grid">
        
        <!-- COLUMNA IZQ: ID CARD -->
        <div>
            <div class="id-card">
                <div class="id-avatar">
                    <?php echo strtoupper(substr($seller_info['nombre'], 0, 1) . substr($seller_info['apellido'], 0, 1)); ?>
                </div>
                <div class="id-name"><?php echo htmlspecialchars($seller_info['nombre'] . ' ' . $seller_info['apellido']); ?></div>
                <div class="id-role">Vendedor Oficial</div>
                <div class="id-uni-badge">
                    <i class="fas fa-university"></i> Sede: <?php echo htmlspecialchars($seller_info['universidad'] ?? 'Sin asignar'); ?>
                </div>

                <div class="id-contact">
                    <div style="border-top: 1px dashed rgba(201, 124, 137, 0.2); margin: 1.5rem 0;"></div>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($seller_info['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($seller_info['telefono'] ?? 'Sin teléfono'); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Desde <?php echo date('M Y', strtotime($seller_info['fecha_contratacion'])); ?></p>
                </div>
            </div>
        </div>

        <!-- COLUMNA DER: ESTADÍSTICAS Y AJUSTES -->
        <div>
            
            <?php if ($success_message): ?>
                <div class="alert-success" style="background: rgba(32, 186, 90, 0.1); color: #20ba5a; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #20ba5a; font-weight: 500;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert-error" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #e74c3c; font-weight: 500;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Muro de Logros -->
            <div class="stats-layout">
                <div class="mini-stat">
                    <i class="fas fa-box-open ms-icon"></i>
                    <div class="ms-val"><?php echo number_format($seller_info['total_pedidos']); ?></div>
                    <div class="ms-lbl">Pedidos Exitosos</div>
                </div>
                <div class="mini-stat">
                    <i class="fas fa-hand-holding-usd ms-icon"></i>
                    <div class="ms-val"><?php echo formato_moneda($seller_info['total_ventas']); ?></div>
                    <div class="ms-lbl">Ventas Generadas</div>
                </div>
                <div class="mini-stat">
                    <i class="fas fa-star ms-icon"></i>
                    <div class="ms-val" style="color: var(--accent-dark);"><?php echo formato_moneda($seller_info['total_comisiones_ganadas']); ?></div>
                    <div class="ms-lbl">Oro Ganado</div>
                </div>
            </div>

            <!-- Formulario Ajustes -->
            <div class="content-card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.03); border-radius: 18px;">
                <div class="card-header" style="border-bottom: 1px solid rgba(201,124,137,0.1); background: #fcfcfc;">
                    <h3 class="card-title" style="margin: 0; font-family: 'Playfair Display', serif; color: var(--primary-dark);">Ajustes de Seguridad</h3>
                </div>
                <div style="padding: 2rem;">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Actualizar Teléfono (Opcional)</label>
                                <input type="tel" name="phone" class="form-input" placeholder="Ej. 3001234567" value="<?php echo htmlspecialchars($seller_info['telefono'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nueva Contraseña (Opcional)</label>
                                <input type="password" name="new_password" class="form-input" placeholder="Déjalo en blanco para mantener la actual">
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-save"><i class="fas fa-shield-alt"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
    <?php else: ?>
        <div style="padding: 4rem; text-align: center; color: #888;">
            <i class="fas fa-ghost" style="font-size: 3rem; margin-bottom: 1rem; color: #ccc;"></i>
            <h3>No se encontró el perfil de vendedor.</h3>
        </div>
    <?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>