<?php
/**
 * ===================================================================
 * Archivo: simulador.php
 * Propósito: Script CLI de Prueba de Estrés (Stress Test).
 *            Genera exactamente 50 pedidos concurrentes para validar
 *            la robustez y velocidad de los triggers en PostgreSQL.
 * Ejecución: php scripts/simulador.php
 * ===================================================================
 */

// Evitar ejecución desde navegador
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ser ejecutado desde la linea de comandos (CLI).\n");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Códigos de colores ANSI
$c_verde = "\033[32m";
$c_amarillo = "\033[33m";
$c_cyan = "\033[36m";
$c_rojo = "\033[31m";
$c_reset = "\033[0m";
$c_bold = "\033[1m";

// Constante estricta solicitada
define('TOTAL_REQUESTS', 50);

echo "\n{$c_cyan}{$c_bold}=========================================================={$c_reset}\n";
echo "{$c_cyan}{$c_bold}  MAI SHOP - PRUEBA DE ESTRÉS BACKEND (BLACK FRIDAY)     {$c_reset}\n";
echo "{$c_cyan}{$c_bold}=========================================================={$c_reset}\n\n";

echo "{$c_amarillo}➜ Conectando a PostgreSQL...{$c_reset} ";
require_once __DIR__ . '/../src/Php/config/conexion.php';

if (!isset($pdo)) {
    die("{$c_rojo}[ERROR] No se pudo establecer la conexion a la BD.{$c_reset}\n");
}
echo "{$c_verde}[OK]{$c_reset}\n\n";

echo "{$c_amarillo}➜ Iniciando inyección masiva de " . TOTAL_REQUESTS . " pedidos...{$c_reset}\n";

$tiempo_inicio = microtime(true);
$exitos = 0;
$errores = 0;
$comisiones_totales = 0;

try {
    // Preparar consultas fuera del ciclo para mayor rendimiento
    $stmt_vendedores = $pdo->query("SELECT id_miembro FROM tbl_miembro WHERE id_estado_miembro = 1");
    $vendedores = $stmt_vendedores->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt_productos = $pdo->query("SELECT id_producto, precio FROM tbl_producto WHERE estado = 'activo'");
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    if (empty($vendedores) || empty($productos)) {
        throw new Exception("Faltan vendedores o productos en la base de datos para simular.");
    }

    $fecha_actual = date('Y-m-d H:i:s');
    $fecha_entrega = date('Y-m-d', strtotime('+3 days'));

    // Iniciar Transacción General para acelerar la inserción masiva
    $pdo->beginTransaction();

    for ($i = 1; $i <= TOTAL_REQUESTS; $i++) {
        
        // 1. Vendedor y producto aleatorio
        $id_vendedor = $vendedores[array_rand($vendedores)];
        $prod_random = $productos[array_rand($productos)];
        $cantidad = rand(1, 4);

        // 2. Insertar Pedido
        $next_id = (int) $pdo->query("SELECT COALESCE(MAX(id_pedido), 0) + 1 FROM tbl_pedido")->fetchColumn();
        $stmt_ped = $pdo->prepare("INSERT INTO tbl_pedido (id_pedido, id_vendedor, telefono_contacto, fecha_entrega, direccion_entrega, notas, estado, estado_pago, monto_comision, fecha_creacion) VALUES (?, ?, '3000000000', ?, 'Stress Test CLI', 'Autogenerado', 1, 0, 0, ?)");
        $stmt_ped->execute([$next_id, $id_vendedor, $fecha_entrega, $fecha_actual]);

        // 3. Insertar Detalle
        $next_det = (int) $pdo->query("SELECT COALESCE(MAX(id_detalle_pedido), 0) + 1 FROM tbl_detalle_pedido")->fetchColumn();
        $stmt_det = $pdo->prepare("INSERT INTO tbl_detalle_pedido (id_detalle_pedido, id_pedido, id_producto, cantidad, precio_unitario, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
        $stmt_det->execute([$next_det, $next_id, $prod_random['id_producto'], $cantidad, $prod_random['precio']]);

        // 4. Completar pedido (Dispara Triggers)
        $stmt_upd = $pdo->prepare("UPDATE tbl_pedido SET estado = 2 WHERE id_pedido = ?");
        $stmt_upd->execute([$next_id]);

        // 5. Validar que el Trigger funcionó
        $stmt_check = $pdo->prepare("SELECT monto_comision FROM tbl_pedido WHERE id_pedido = ?");
        $stmt_check->execute([$next_id]);
        $comision = (float) $stmt_check->fetchColumn();

        if ($comision > 0) {
            $exitos++;
            $comisiones_totales += $comision;
            // Imprimir progreso en una sola línea que se sobreescribe
            echo "\r   Procesando: [{$i}/" . TOTAL_REQUESTS . "] Pedido #{$next_id} completado. Comisión calculada: $" . number_format($comision, 2) . "   ";
        } else {
            $errores++;
        }
        
        // Pequeña pausa visual para que la audiencia vea correr los números
        usleep(30000); // 30 milisegundos
    }

    $pdo->commit();

    $tiempo_fin = microtime(true);
    $tiempo_total = round($tiempo_fin - $tiempo_inicio, 2);

    echo "\n\n{$c_cyan}{$c_bold}=========================================================={$c_reset}\n";
    echo "{$c_verde}{$c_bold}✔ PRUEBA DE ESTRÉS FINALIZADA{$c_reset}\n";
    echo "{$c_cyan}{$c_bold}=========================================================={$c_reset}\n";
    echo "   Pedidos inyectados: {$c_bold}{$exitos}/" . TOTAL_REQUESTS . "{$c_reset}\n";
    echo "   Errores:            {$c_rojo}{$errores}{$c_reset}\n";
    echo "   Tiempo de ejecución: {$c_bold}{$tiempo_total} segundos{$c_reset}\n";
    echo "   Dinero total repartido en comisiones: {$c_verde}$" . number_format($comisiones_totales, 2) . "{$c_reset}\n";
    echo "{$c_cyan}=========================================================={$c_reset}\n\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n{$c_rojo}[ERROR FATAL]{$c_reset} " . $e->getMessage() . "\n";
}
