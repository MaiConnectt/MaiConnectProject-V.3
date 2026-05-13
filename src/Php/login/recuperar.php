<?php
/**
 * Archivo: recuperar.php
 * Propósito: Vista del formulario "Recuperar contraseña".
 *            El admin/vendedor ingresa su email y recibe un enlace.
 */
require_once __DIR__ . '/../config/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Mai Shop</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/landing.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .recover-icon {
            font-size: 3.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #4a90d9;
            border-radius: var(--radius-md);
            padding: 1rem 1.2rem;
            margin-bottom: 2rem;
            font-size: 1rem;
            color: #2c5282;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
        }

        .info-box i {
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .success-box {
            background: #f0fff4;
            border-left: 4px solid #38a169;
            border-radius: var(--radius-md);
            padding: 1.2rem 1.4rem;
            margin-bottom: 2rem;
            font-size: 1rem;
            color: #276749;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
        }

        #btn-send {
            position: relative;
            overflow: hidden;
        }

        #btn-send .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        #btn-send.loading .btn-text {
            display: none;
        }

        #btn-send.loading .spinner {
            display: block;
        }
    </style>
</head>

<body>
    <div class="login-container">

        <div class="login-header">
            <i class="fas fa-key recover-icon"></i>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p>Te enviaremos un enlace para restablecerla</p>
        </div>

        <!-- Mensaje de información -->
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <span>Ingresa el correo electrónico asociado a tu cuenta y recibirás un enlace válido por <strong>15
                    minutos</strong>.</span>
        </div>

        <!-- Área de resultado (éxito o error) -->
        <div id="result-area" style="display:none;"></div>

        <!-- Formulario -->
        <form id="recoverForm">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com"
                        required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btn-send">
                <span class="btn-text"><i class="fas fa-paper-plane" style="margin-right:0.5rem;"></i> Enviar
                    enlace</span>
                <span class="spinner"></span>
            </button>
        </form>

        <a href="<?= BASE_URL ?>/src/Php/login/login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

    <script>
        document.getElementById('recoverForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = document.getElementById('btn-send');
            const resultArea = document.getElementById('result-area');
            const email = document.getElementById('email').value;

            // Estado de carga
            btn.classList.add('loading');
            btn.disabled = true;
            resultArea.style.display = 'none';

            try {
                const res = await fetch('recuperar_accion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                });

                const data = await res.json();

                btn.classList.remove('loading');
                btn.disabled = false;

                if (data.success) {
                    // Ocultar formulario y mostrar éxito
                    document.getElementById('recoverForm').style.display = 'none';
                    resultArea.innerHTML = `
                        <div class="success-box">
                            <i class="fas fa-check-circle" style="font-size:1.3rem;margin-top:0.1rem;"></i>
                            <div>
                                <strong>¡Correo enviado!</strong><br>
                                Revisa tu bandeja de entrada en <strong>${email}</strong>.
                                El enlace expira en <strong>15 minutos</strong>.
                            </div>
                        </div>`;
                    resultArea.style.display = 'block';
                } else {
                    resultArea.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            ${data.message}
                        </div>`;
                    resultArea.style.display = 'block';
                }
            } catch (error) {
                btn.classList.remove('loading');
                btn.disabled = false;
                resultArea.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        Error de conexión. Intenta de nuevo.
                    </div>`;
                resultArea.style.display = 'block';
            }
        });
    </script>
</body>

</html>