DROP VIEW IF EXISTS `sabullvial_tango_cotizaciones_detalles`;

CREATE VIEW `sabullvial_tango_cotizaciones_detalles` AS
SELECT 
    cd.id,
    cd.id_cotizacion,
    cd.id_producto,
    cd.codigo_sap,
    cd.nombre,
    cd.marca,
    cd.precio,
    rd.cantidad,
    rd.cantidad * cd.precio subtotal
FROM `#__sabullvial_revisiondetalle` rd
INNER JOIN `#__sabullvial_cotizaciondetalle` cd ON (cd.id = rd.id_cotizacion_detalle)
WHERE rd.cantidad > 0 AND cd.id_cotizacion IN (
        SELECT id
        FROM `sabullvial_tango_cotizaciones`
    )
ORDER BY cd.id_cotizacion;