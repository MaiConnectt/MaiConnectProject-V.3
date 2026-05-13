<?php
/*
 * Archivo: nueva_contrasena.php
 * Propósito: Vista del formulario para ingresar la nueva contraseña.
 *            Valida el token JWT de la URL antes de mostrar el form.
 *            Si el token es inválido o expiró, muestra error.
 */

$vendor_path = realpath(__DIR__ . '/../../../vendor/autoload.php');
require_once $vendor_path;
require_once __DIR__ . '/../config/conexion.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$token    = $_GET['token'] ?? '';
$token_ok = false;
$token_error = '';

if (empty($token)) {
    $token_error = 'El enlace de recuperación es inválido o está incompleto.';
} else {
    try {
        $env_arr = [];
        foreach (file(realpath(__DIR__ . '/../../../.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
            $l = trim($l);
            if ($l === '' || $l[0] === '#' || $l[0] === ';') continue;
            $p = strpos($l, '='); if ($p === false) continue;
            $env_arr[trim(substr($l, 0, $p))] = trim(substr($l, $p + 1));
        }
        $decoded = JWT::decode($token, new Key($env_arr['JWT_SECRET'], 'HS256'));
        $token_ok = true;
    } catch (ExpiredException $e) {
        $token_error = 'El enlace ha expirado (15 minutos). Solicita uno nuevo.';
    } catch (Exception $e) {
        $token_error = 'El enlace de recuperación no es válido.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Mai Shop</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/landing.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .strength-bar {
            height: 5px;
            border-radius: 3px;
            margin-top: 8px;
            transition: all 0.3s ease;
            background: #eee;
        }
        .strength-bar.weak   { background: linear-gradient(90deg, #e53e3e 30%, #eee 30%); }
        .strength-bar.medium { background: linear-gradient(90deg, #dd6b20 66%, #eee 66%); }
        .strength-bar.strong { background: linear-gradient(90deg, #38a169 100%, #eee 0%); }
        .strength-text { font-size: 0.85rem; color: #888; margin-top: 4px; }

        .toggle-pass {
            position: absolute; right: 1.2rem; top: 50%;
            transform: translateY(-50%);
            cursor: pointer; color: #aaa;
            font-size: 1rem; transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--primary-color); }

        .token-error-box {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            border-radius: var(--radius-md);
            padding: 1.4rem 1.6rem;
            color: #c53030;
            text-align: left;
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        .token-error-box i { font-size: 1.3rem; margin-top: 0.1rem; flex-shrink: 0; }

        #btn-save .spinner {
            display: none;
            width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #btn-save.loading .btn-text { display: none; }
        #btn-save.loading .spinner { display: block; }
    </style>
</head>
<body>
    <div class="login-container">

        <div class="login-header">
            <i class="fas fa-lock-open" style="font-size:3.5rem;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;display:inline-block;margin-bottom:1.5rem;"></i>
            <h2>Nueva Contraseña</h2>
            <p><?= $token_ok ? 'Crea tu nueva contraseña segura' : 'Enlace inválido' ?></p>
        </div>

        <?php if (!$token_ok): ?>
            <!-- Token inválido o expirado -->
            <div class="token-error-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Enlace no válido</strong><br>
                    <?= htmlspecialchars($token_error) ?>
                </div>
            </div>
            <a href="recuperar.php" class="btn-submit" style="display:block;text-align:center;text-decoration:none;margin-top:0;">
                <i class="fas fa-redo" style="margin-right:0.5rem;"></i> Solicitar nuevo enlace
            </a>

        <?php else: ?>
            <!-- Token válido: mostrar formulario -->
            <div id="result-area" style="display:none;"></div>

            <form id="newPassForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <!-- Nueva contraseña -->
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Mínimo 8 caracteres" required autocomplete="new-password"
                            style="padding-right:3.5rem;">
                        <span class="toggle-pass" onclick="togglePass('password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="strength-bar" id="strengthBar"></div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <!-- Confirmar contraseña -->
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-check-circle"></i>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                            placeholder="Repite la contraseña" required autocomplete="new-password"
                            style="padding-right:3.5rem;">
                        <span class="toggle-pass" onclick="togglePass('password_confirm', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btn-save">
                    <span class="btn-text"><i class="fas fa-save" style="margin-right:0.5rem;"></i> Guardar contraseña</span>
                    <span class="spinner"></span>
                </button>
            </form>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/Php/login/login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

    <script>
        // Mostrar/ocultar contraseña
        function togglePass(fieldId, icon) {
            const input = document.getElementById(fieldId);
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            icon.querySelector('i').className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
        }

        // Indicador de fortaleza de contraseña
        const passInput = document.getElementById('password');
        if (passInput) {
            passInput.addEventListener('input', function () {
                const val = this.value;
                const bar = document.getElementById('strengthBar');
                const text = document.getElementById('strengthText');
                if (!val) { bar.className = 'strength-bar'; text.textContent = ''; return; }
                const strong = val.length >= 10 && /[A-Z]/.test(val) && /[0-9]/.test(val) && /[^a-zA-Z0-9]/.test(val);
                const medium = val.length >= 8 && (/[A-Z]/.test(val) || /[0-9]/.test(val));
                if (strong) { bar.className = 'strength-bar strong'; text.textContent = '✅ Contraseña fuerte'; }
                else if (medium) { bar.className = 'strength-bar medium'; text.textContent = '⚠️ Contraseña media'; }
                else { bar.className = 'strength-bar weak'; text.textContent = '❌ Contraseña débil'; }
            });
        }

        // Envío AJAX del formulario
        const form = document.getElementById('newPassForm');
        if (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const password = document.getElementById('password').value;
                const confirm  = document.getElementById('password_confirm').value;
                const btn      = document.getElementById('btn-save');
                const result   = document.getElementById('result-area');

                if (password.length < 8) {
                    result.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i> La contraseña debe tener al menos 8 caracteres.</div>';
                    result.style.display = 'block'; return;
                }
                if (password !== confirm) {
                    result.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i> Las contraseñas no coinciden.</div>';
                    result.style.display = 'block'; return;
                }

                btn.classList.add('loading');
                btn.disabled = true;
                result.style.display = 'none';

                const formData = new FormData(this);
                try {
                    const res = await fetch('nueva_contrasena_accion.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    
                    if (data.success) {
                        form.style.display = 'none';
                        result.innerHTML = `
                            <div style="background:#f0fff4;border-left:4px solid #38a169;border-radius:12px;padding:1.4rem;color:#276749;text-align:left;display:flex;gap:0.8rem;align-items:flex-start;">
                                <i class="fas fa-check-circle" style="font-size:1.4rem;margin-top:0.1rem;"></i>
                                <div><strong>¡Contraseña actualizada!</strong><br>Serás redirigido al inicio de sesión en 3 segundos...</div>
                            </div>`;
                        result.style.display = 'block';
                        setTimeout(() => window.location.href = '<?= BASE_URL ?>/src/Php/login/login.php', 3000);
                    } else {
                        result.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                        result.style.display = 'block';
                    }
                } catch (error) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    result.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i> Error de conexión. Intenta de nuevo.</div>';
                    result.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>
