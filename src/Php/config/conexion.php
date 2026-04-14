<?php
/**
 * ===================================================================
 * Archivo: conexion.php
 * Propósito: Configurar y establecer la conexión a la base de datos 
 *            PostgreSQL utilizando PDO. También define constantes 
 *            globales para el manejo de rutas en el proyecto.
 * ===================================================================
 */

// Parámetros de conexión a la base de datos
$host = 'localhost';
$port = 5432;
$dbname = 'MaiConnect';
$user = 'postgres';
$password = '3205560180';
try {
    // Instanciar el objeto PDO para la conexión con PostgreSQL
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Obtener resultados como array asociativo por defecto
        ]
    );
} catch (PDOException $e) {
    // Terminar la ejecución si falla la conexión
    die("ERROR DB: " . $e->getMessage());
}
// Asegurar que el modo de error esté configurado en excepción (redundancia de seguridad)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Definir BASE_PATH para inclusiones del sistema de archivos (ruta absoluta a /Front)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__));
}

// Definir BASE_URL para rutas absolutas
if (!defined('BASE_URL')) {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    // Buscar /src/Php/ para determinar la URL base
    $pos = strpos($script_name, '/src/Php/');
    if ($pos !== false) {
        $base_url = substr($script_name, 0, $pos);
    } else {
        // Si no está en src/Php, podríamos estar en index.php en la raíz
        $base_url = rtrim(dirname($script_name), '/\\');
        if ($base_url === '\\')
            $base_url = '';
    }
    define('BASE_URL', $base_url);
}

// Definir $base_path para rutas absolutas del sistema de archivos interno
// Autodetectar raíz del proyecto: __DIR__ = src/Php/config → subir 3 niveles → raíz del proyecto
$base_path = realpath(__DIR__ . '/../../..');
