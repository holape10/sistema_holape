-- ============================================================================
-- OPTIMIZACIÓN DE QUERIES KIOSKO
-- ============================================================================
-- Este script agrega índices críticos para mejorar el rendimiento
-- de la carga de pedidos y detalles en el módulo Kiosko.
-- ============================================================================

-- Índices en pedidos_detalle (tabla crítica)
ALTER TABLE pedidos_detalle 
ADD INDEX idx_ped_id_estado (ped_id, estadoitem),
ADD INDEX idx_producto_id (IdProducto),
ADD INDEX idx_item_facturado (ped_id, item_facturado);

-- Índices en productos (para JOINs rápidos)
ALTER TABLE productos 
ADD INDEX idx_id_producto (IdProducto),
ADD INDEX idx_empresa_producto (IdEmpresa, IdProducto);

-- Índices en producto_stock
ALTER TABLE producto_stock 
ADD INDEX idx_almacen_empresa (id_almacen, id_empresa_negocio, IdProducto),
ADD INDEX idx_producto_almacen (IdProducto, id_almacen);

-- Índices en pedidos (para estado y búsquedas)
ALTER TABLE pedidos 
ADD INDEX idx_estado_fecha (ped_est, fecha_hora_modificacion),
ADD INDEX idx_mesa_id (mes_id),
ADD INDEX idx_empresa_negocio (id_empresa_negocio);

-- Índice en mesas para búsquedas por estado
ALTER TABLE mesas 
ADD INDEX idx_empresa_estado (id_empresa_negocio, mes_est),
ADD INDEX idx_union_estado (ind_union, mes_est);

-- ============================================================================
-- INFORMACIÓN DE ÍNDICES AGREGADOS
-- ============================================================================
-- 
-- 1. pedidos_detalle:
--    - idx_ped_id_estado: Para filtrar rápidamente por ped_id y estado
--    - idx_producto_id: Para JOINs con productos
--    - idx_item_facturado: Para calcular cantidad_pendiente
--
-- 2. productos:
--    - idx_id_producto: Para búsquedas directas
--    - idx_empresa_producto: Para filtrar por empresa
--
-- 3. producto_stock:
--    - idx_almacen_empresa: Para JOINs complejos
--    - idx_producto_almacen: Para búsquedas rápidas
--
-- 4. pedidos:
--    - idx_estado_fecha: Para búsquedas por estado ordenadas
--    - idx_mesa_id: Para búsquedas por mesa
--    - idx_empresa_negocio: Para filtrar por negocio
--
-- 5. mesas:
--    - idx_empresa_estado: Para listar mesas libres/ocupadas
--    - idx_union_estado: Para mesas unidas
--
-- IMPACTO ESPERADO:
-- - Reducción de tiempo de carga de 80%+ en pedidos con muchos items
-- - Eliminación del problema N+1 (ahora es una sola query)
-- - Mejora en búsquedas de mesas libres
-- ============================================================================
