CREATE VIEW `sabullvial_tango_cotizaciones` AS 
	SELECT c.id,
    c.id_cliente,
    c.cliente,
    c.id_estadocotizacion,
    ec.nombre estadocotizacion,
    c.id_estado_tango,
    IF(
        c.id_estado_tango = 1,
        'Si',
        IF(c.id_estado_tango = 5, 'Automática', 'Prueba')
    ) estado_tango,
    c.id_condicionventa,
    cv1.nombre condicionventa,
    c.id_condicionventa_fake,
    cv2.nombre condicionventa_fake,
    c.id_direccion,
    cd.direccion,
    c.id_transporte,
    CONCAT("(", st.COD_TRANSP, ")", " ", st.DOM_TRANS, " - ", st.NOMBRE_TRA) transporte,
    c.id_lista_precio,
    c.documento_tipo,
    IF (c.documento_tipo = 80, 'CUIT', IF(c.documento_tipo = 96, 'DNI', '-')) documento_tipo_texto,
    c.documento_numero,
    c.email,
    c.delivery_term plazo_entrega,
    c.iva,
    c.dolar,
    c.total,
    c.note mensaje_interno,
    c.observations observaciones,
    c.created_by id_vendedor,
    c.created_by_alias vendedor,
    c.created fecha_creacion
FROM `#__sabullvial_cotizacion` c
    left join `#__sabullvial_estadocotizacion` ec ON (ec.id = c.id_estadocotizacion)
    left join `bullvial_bullvial`.`condiciones_venta` cv1 ON (cv1.id = c.id_condicionventa)
    left join `bullvial_bullvial`.`condiciones_venta` cv2 ON (cv2.id = c.id_condicionventa_fake)
    left join `bullvial_bullvial`.`cliente_direcciones` cd ON (cd.id_direccion = c.id_direccion)
    left join `bullvial_bullvial`.`SIT_TRANSPORTES` st ON (st.COD_TRANSP = c.id_transporte)
WHERE id_estado_tango IN (1, 5, 6);

CREATE VIEW `sabullvial_tango_cotizaciones_detalles` AS
SELECT cd.id,
    cd.id_cotizacion,
    cd.id_producto,
    codigo_sap,
    nombre,
    marca,
    precio,
    cantidad,
    subtotal
FROM `#__sabullvial_cotizaciondetalle` cd
WHERE cd.id_cotizacion IN (
        SELECT id
        FROM `sabullvial_tango_cotizaciones`
    )
ORDER BY cd.id_cotizacion;