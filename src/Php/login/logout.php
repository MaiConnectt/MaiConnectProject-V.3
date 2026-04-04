<?php
/**
 * ===================================================================
 * Archivo: logout.php
 * Propósito: Destruye la sesión activa del usuario y lo redirige 
 *            a la pantalla de inicio de sesión.
 * ===================================================================
 */
session_start();
session_destroy();
header('Location: login.php');
exit;
