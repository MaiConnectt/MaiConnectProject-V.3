-- ============================================================
-- MIGRACIÓN: Eliminar tablas de stock no utilizadas
-- Fecha: 2026-04-24
-- Descripción: tbl_movimiento_stock y tbl_tipo_movimiento_stock
--              fueron definidas pero nunca implementadas en el
--              código PHP. Se eliminan de forma segura.
-- ============================================================

-- 1. Primero la tabla hija (tiene FK hacia tbl_tipo_movimiento_stock)
DROP TABLE IF EXISTS tbl_movimiento_stock;

-- 2. Luego la tabla padre (catálogo de tipos)
DROP TABLE IF EXISTS tbl_tipo_movimiento_stock;
