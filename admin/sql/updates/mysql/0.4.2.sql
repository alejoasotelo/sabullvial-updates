ALTER TABLE `#__sabullvial_cotizacion` CHANGE `id_cliente` `id_cliente` VARCHAR(6) NOT NULL; 

DROP VIEW IF EXISTS `sabullvial_tango_cotizaciones`;
CREATE VIEW `sabullvial_tango_cotizaciones` AS 
	SELECT 
	c.id,
    c.id_cliente codigo_cliente,
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
    cv1.DESC_COND condicionventa,
    c.id_condicionventa_fake,
    cv2.DESC_COND condicionventa_fake,
    c.id_direccion id_direccion_entrega,
    cd.DIR_ENTREGA direccion_entrega,
    c.id_transporte codigo_transporte,
    CONCAT("(", st.COD_TRANSP, ")", " ", st.DOM_TRANS, " - ", st.NOMBRE_TRA) transporte,
    c.id_lista_precio,
    c.documento_tipo,
    IF (c.documento_tipo = 80, 'CUIT', IF(c.documento_tipo = 96, 'DNI', '-')) documento_tipo_texto,
    c.documento_numero,
    c.email,
    c.delivery_term plazo_entrega,
    c.iva tiene_iva,
    c.dolar tiene_dolar,
	IF(c.iva = 1, 0, c.subtotal * 0.21) iva,
	c.iibb,
	c.subtotal,
    c.total,
	IF(c.iva = 1, 0, c.subtotal_revision * 0.21) iva_revision,
	c.iibb_revision,
	c.subtotal_revision,
	c.total_revision,
    c.note mensaje_interno,
    c.observations observaciones,
    c.created_by id_vendedor,
    c.created_by_alias vendedor,
    c.created fecha_creacion
FROM `#__sabullvial_cotizacion` c
    left join `#__sabullvial_estadocotizacion` ec ON (ec.id = c.id_estadocotizacion)
    left join `SIT_CONDICIONES_VENTA` cv1 ON (cv1.COND_VTA = c.id_condicionventa)
    left join `SIT_CONDICIONES_VENTA` cv2 ON (cv2.COND_VTA = c.id_condicionventa_fake)
    left join `SIT_CLIENTES_DIRECCION_ENTREGA` cd ON (cd.ID_DIRECCION_ENTREGA = c.id_direccion)
    left join `SIT_TRANSPORTES` st ON (st.COD_TRANSP = c.id_transporte)
WHERE id_estado_tango IN (1, 5, 6)
ORDER BY c.id DESC;