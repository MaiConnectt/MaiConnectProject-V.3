

-- Vista 1: Totales por pedido
-- Calcula el total económico de cada pedido sumando sus líneas
-- de detalle activas. Usada ampliamente en dashboard y seller.

CREATE OR REPLACE VIEW vw_totales_pedido AS
SELECT
    p.id_pedido,
    p.id_vendedor,
    p.telefono_contacto,
    p.direccion_entrega,
    p.fecha_entrega,
    p.fecha_creacion,
    p.estado,
    p.estado_pago,
    p.notas,
    COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) AS total
FROM tbl_pedido p
LEFT JOIN tbl_detalle_pedido dp
    ON p.id_pedido = dp.id_pedido AND dp.estado = 'activo'
WHERE p.estado_logico = 'activo'
GROUP BY
    p.id_pedido, p.id_vendedor, p.telefono_contacto, p.direccion_entrega,
    p.fecha_entrega, p.fecha_creacion, p.estado, p.estado_pago, p.notas;

-- Vista 2: Comisiones por vendedor
-- Agrega ventas, comisiones ganadas, pagadas y saldo pendiente
-- por cada miembro activo del equipo.
-- Depende de: vw_totales_pedido (debe existir primero).
CREATE OR REPLACE VIEW vw_comisiones_vendedor AS
SELECT
    m.id_miembro,
    u.nombre,
    u.apellido,
    u.email,
    m.universidad,
    m.telefono,
    m.porcentaje_comision,
    m.estado,
    m.fecha_contratacion,
    COUNT(DISTINCT CASE WHEN o.estado != 3 THEN o.id_pedido END)                                        AS total_pedidos,
    COALESCE(SUM(CASE WHEN o.estado = 2 THEN ot.total ELSE 0 END), 0)                                   AS total_ventas,
    COALESCE(SUM(CASE WHEN o.estado = 2 THEN ot.total * m.porcentaje_comision / 100 ELSE 0 END), 0)     AS total_comisiones_ganadas,
    COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NOT NULL THEN o.monto_comision ELSE 0 END), 0) AS total_pagado,
    COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NULL  THEN o.monto_comision ELSE 0 END), 0)    AS saldo_pendiente
FROM tbl_miembro m
JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
LEFT JOIN tbl_pedido o
    ON m.id_miembro = o.id_vendedor AND o.estado_logico = 'activo'
LEFT JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
WHERE m.estado = 'activo'
GROUP BY
    m.id_miembro, u.nombre, u.apellido, u.email,
    m.universidad, m.telefono, m.porcentaje_comision, m.estado, m.fecha_contratacion;
