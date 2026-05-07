<?php
/**
 * ===================================================================
 * Archivo: conexion.php
 * Propósito: Configurar y establecer la conexión a la base de datos 
 *            PostgreSQL utilizando PDO. También define constantes 
 *            globales para el manejo de rutas en el proyecto.
 * ===================================================================
 */

// Cargar credenciales desde el archivo .env (3 niveles arriba: config → Php → src → raíz)
$env_path = realpath(__DIR__ . '/../../../.env');
if (!$env_path || !file_exists($env_path)) {
    die('ERROR: No se encontró el archivo .env. Verifica que exista en la raíz del proyecto.');
}

// Lector de .env propio: soporta comentarios con # y valores con = en ellos
$env = [];
foreach (file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    $linea = trim($linea);
    // Ignorar líneas vacías y comentarios (# o ;)
    if ($linea === '' || $linea[0] === '#' || $linea[0] === ';')
        continue;
    // Separar solo en el PRIMER '=' para permitir valores con '=' dentro
    $pos = strpos($linea, '=');
    if ($pos === false)
        continue;
    $clave = trim(substr($linea, 0, $pos));
    $valor = trim(substr($linea, $pos + 1));
    $env[$clave] = $valor;
}

// Parámetros de conexión leídos del .env
$host = $env['DB_HOST'];
$port = $env['DB_PORT'];
$dbname = $env['DB_NAME'];
$user = $env['DB_USER'];
$password = $env['DB_PASSWORD'];
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
