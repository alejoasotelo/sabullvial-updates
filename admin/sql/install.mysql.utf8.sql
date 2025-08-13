DROP TABLE IF EXISTS `#__sabullvial_cotizacion`;

CREATE TABLE `#__sabullvial_cotizacion` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`parent_id` INT(11) NOT NULL,
	`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`id_cliente` VARCHAR(6) NOT NULL,
	`id_estadocotizacion` INT(11)  NOT NULL,
	`id_estado_tango` INT(11)  NOT NULL,
	`id_condicionventa` INT(11)  NOT NULL,
	`id_condicionventa_fake` INT(11) NOT NULL,
	`id_direccion` INT(11) NULL,
	`id_transporte` VARCHAR(10) NULL,
	`id_deposito` INT(11) NOT NULL,
	`id_deposito_tango` INT(11) NOT NULL,
	`id_lista_precio` INT(11) NULL,
	`id_estadocotizacionpago` INT(11) NOT NULL,
	`tango_enviar` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`tango_fecha_sincronizacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`reference` VARCHAR(255) NOT NULL,
	`cliente` VARCHAR(255) NOT NULL,
	`documento_tipo` INT(11) NULL,
	`documento_numero` VARCHAR(50) NULL,
	`email` VARCHAR(100) NOT NULL,
	`delivery_term` VARCHAR(255) NULL,
	`iva` tinyint NOT NULL DEFAULT 0,
	`dolar` tinyint NOT NULL DEFAULT 0,
	`iibb` DECIMAL(20, 6) NULL,
	`iibb_revision` DECIMAL(20, 6) NULL,
	`subtotal` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`subtotal_revision` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`total` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`total_revision` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`descuento` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`porcentaje_iibb` DECIMAL(20, 6) NULL,
	`ordendecompra_numero` VARCHAR(255) NULL, 
	`ordendecompra_file_name` VARCHAR(255) NULL, 
	`ordendecompra_file_hash` VARCHAR(255) NULL,
	`ordendecompra_file_ext` VARCHAR(10) NULL,
	`solicitante` varchar(255) NULL DEFAULT '',
	`note` varchar(5120) NULL DEFAULT '',
	`observations` varchar(5120) NULL DEFAULT '',
	`esperar_pagos`  tinyint(4) UNSIGNED NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
	ENGINE =MyISAM
	AUTO_INCREMENT =0
	DEFAULT CHARSET =utf8;

DROP TABLE IF EXISTS `#__sabullvial_cliente`;

CREATE TABLE `#__sabullvial_cliente` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_estadocliente` INT(11) NOT NULL,
	`tango_enviar` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`tango_fecha_sincronizacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`id_condicionventa` INT(11) NOT NULL,
	`condicionventa_deseada` VARCHAR(255) NOT NULL DEFAULT '',
	`cupo_credito` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`codigo_lista` int(10) NOT NULL DEFAULT 1,
	`codigo_vendedor` VARCHAR(10) NULL DEFAULT '',
	`codigo_categoria_iva` VARCHAR(10) NULL DEFAULT 'RI',
	`codigo_rubro` VARCHAR(10) NULL DEFAULT '',
	`codigo_zona` VARCHAR(10) NULL DEFAULT '',
    `razon_social` VARCHAR(255) NULL,
    `nombre_comercial` VARCHAR(255) NULL,
    `actividad_comercial` VARCHAR(255) NULL,
    `documento_tipo` INT(10) NULL DEFAULT 80,
    `documento_numero` VARCHAR(20) NULL,
    `pagina_web` VARCHAR(255) NULL,
    `grupo_empresario` VARCHAR(255) NULL,
    `aclaracion` VARCHAR(255) NULL,
	`monto` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`plazo` VARCHAR(255) NULL DEFAULT '',
    `direccion_comercial_calle` VARCHAR(255) NULL,
    `direccion_comercial_calle_numero` VARCHAR(10),
    `direccion_comercial_ciudad` VARCHAR(255) NULL,
    `direccion_comercial_codigo_postal` VARCHAR(10),
    `direccion_comercial_provincia` VARCHAR(255) NULL,
    `direccion_comercial_telefono` VARCHAR(20),
    `direccion_comercial_celular` VARCHAR(20),
    `direccion_entrega_calle` VARCHAR(255) NULL,
    `direccion_entrega_calle_numero` VARCHAR(10),
    `direccion_entrega_ciudad` VARCHAR(255) NULL,
    `direccion_entrega_codigo_postal` VARCHAR(10),
    `direccion_entrega_provincia` VARCHAR(255) NULL,
    `encargado_compras_nombre` VARCHAR(255) NULL,
    `encargado_compras_telefono` VARCHAR(20),
    `encargado_compras_celular` VARCHAR(20),
    `encargado_compras_interno` VARCHAR(10),
    `encargado_compras_email` VARCHAR(255) NULL,
    `encargado_pagos_nombre` VARCHAR(255) NULL,
    `encargado_pagos_telefono` VARCHAR(20),
    `encargado_pagos_celular` VARCHAR(20),
    `encargado_pagos_interno` VARCHAR(10),
    `encargado_pagos_email` VARCHAR(255) NULL,
    `referencia_comercial_1_empresa` VARCHAR(255) NULL,
    `referencia_comercial_1_contacto` VARCHAR(255) NULL,
    `referencia_comercial_1_telefono` VARCHAR(20),
    `referencia_comercial_2_empresa` VARCHAR(255) NULL,
    `referencia_comercial_2_contacto` VARCHAR(255) NULL,
    `referencia_comercial_2_telefono` VARCHAR(20),
    `referencia_comercial_3_empresa` VARCHAR(255) NULL,
    `referencia_comercial_3_contacto` VARCHAR(255) NULL,
    `referencia_comercial_3_telefono` VARCHAR(20),
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_cliente` ADD INDEX `id_estadocliente_idx` (`id_estadocliente`);
ALTER TABLE `#__sabullvial_cliente` ADD INDEX `id_condicionventa_idx` (`id_condicionventa`);
ALTER TABLE `#__sabullvial_cliente` ADD INDEX `created_by_idx` (`created_by`);

DROP TABLE IF EXISTS `#__sabullvial_logistica`;

CREATE TABLE `#__sabullvial_logistica` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_notificacion`;

CREATE TABLE `#__sabullvial_notificacion` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

/*DROP TABLE IF EXISTS `#__sabullvial_producto`;

CREATE TABLE `#__sabullvial_producto` (
	`id_producto` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;*/

DROP TABLE IF EXISTS `#__sabullvial_cotizaciondetalle`;

CREATE TABLE `#__sabullvial_cotizaciondetalle` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion` INT(11) NOT NULL,
	`id_producto` VARCHAR(15) NULL,
	`codigo_sap` VARCHAR(255) NOT NULL,
	`nombre` VARCHAR(255) NOT NULL,
	`marca` VARCHAR(255) NOT NULL,
	`precio` decimal(20, 6) NOT NULL DEFAULT '0.000000',
	`cantidad` int(10) unsigned NOT NULL DEFAULT '0',
	`descuento` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`subtotal` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_estadocotizacion`;

CREATE TABLE IF NOT EXISTS `#__sabullvial_estadocotizacion`  (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL,
	`aprobado` tinyint(4) UNSIGNED NULL DEFAULT '0',
 	`pendiente` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`rechazado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`cancelado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`revisado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	`access` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadocotizacion` (`nombre`, `color`, `aprobado`, `pendiente`, `rechazado`, `revisado`, `cancelado`, `created`) VALUES
('Cotizado', '#339c03', 0, 0, 0, 0, 0, now()),
('Pendiente de aprobación', 'orange', 0, 1, 0, 0, 0, now()),
('Aprobado', '#3c763d', 1, 0, 0, 0, 0, now()),
('Facturado', '#31708f', 0, 0, 0, 0, 0, now()),
('Rechazado', '#a94442', 0, 0, 1, 0, 0, now()),
('Rechazado por forma de pago', '#a94442', 0, 0, 2, 0, 0, now()),
('Cancelado', '#bd362f', 0, 0, 0, 0, 1, now()),
('Fac. Automático', 'violet', 0, 0, 0, 0, 0, now()),
('Pedido completo', 'violet', 0, 0, 0, 1, 0, now()),
('Pedido incompleto', 'violet', 0, 0, 0, 2, 0, now()),
('Aprobado completo', '#00b078', 1, 0, 0, 1, 0, now()),
('Aprobado con faltantes', '#3b50f5', 1, 0, 0, 2, 0, now()),
('Aprobado automático', '#00b078', 1, 0, 0, 3, 0, now()),
('Prueba', 'muted', 0, 0, 0, 0, 0, now()),
('Orden de trabajo', '#31708f', 0, 0, 0, 0, 0, now()),
('Duplicado', 'muted', 0, 0, 0, 0, 0, now());

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES ('SABullvial Estado Cotizacion', 'com_sabullvial.estadocotizacion', '', '', '{"common": {"core_content_item_id": "id", "core_title": "nombre", "core_state": "published", "core_alias": "null", "core_language":"null", "core_created_time": "created", "core_body": "null", "core_access": "access", "core_catid": "null"}}', '', '');

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES ('SABullvial Cotizacion', 'com_sabullvial.cotizacion', '{"special":{"dbtable":"#__sabullvial_cotizacion","key":"id","type":"Cotizacion","prefix":"SabullvialTable","config":"array()"}}', '', '', '', '');

DROP TABLE IF EXISTS `#__sabullvial_hojaderuta`;

CREATE TABLE `#__sabullvial_hojaderuta` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_vehiculo` INT(11) NULL DEFAULT 0,
	`id_chofer` INT(11) NULL DEFAULT 0,
	`nombre` VARCHAR(255) NULL,
	`chofer` VARCHAR(255) NULL,
	`patente` VARCHAR(20) NOT NULL,
	`delivery_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`delivered_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_estadoremito`;

CREATE TABLE `#__sabullvial_estadoremito` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL DEFAULT '#ffffff',
 	`proceso` tinyint(4) UNSIGNED NULL DEFAULT '0',
 	`preparacion` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`transito` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`entregado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`entregado_mostrador` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_estadoremito` ADD INDEX `nombre_idx` (`nombre`); 

INSERT INTO `#__sabullvial_estadoremito` (`nombre`, `color`, `color_texto`, `proceso`, `preparacion`, `transito`, `entregado`, `entregado_mostrador`, `created`) VALUES
('En proceso', '#ff9900', '#ffffff', 1, 0, 0, 0, 0, now()),
('En preparación', '#ff9900', '#ffffff', 0, 1, 0, 0, 0, now()),
('En tránsito', '#ffd700', '#242424', 0, 0, 1, 0, 0, now()),
('Entregado', '#46a546', '#ffffff', 0, 0, 0, 1, 0, now()),
('Entregado por mostrador', '#46a546', '#ffffff', 0, 0, 0, 0, 1, now()),
('No entregado', '#bd362f', '#ffffff', 0, 0, 0, 0, 0, now());

DROP TABLE IF EXISTS `#__sabullvial_hojaderutaremito`;

CREATE TABLE `#__sabullvial_hojaderutaremito` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_hojaderuta` INT(11) NOT NULL,
	`numero_remito` VARCHAR(14) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_hojaderutaremito` ADD INDEX `numero_remito_idx` (`numero_remito`);

DROP TABLE IF EXISTS `#__sabullvial_revisiondetalle`;

CREATE TABLE `#__sabullvial_revisiondetalle` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion_detalle` INT(11) NULL,
	`cantidad` int(10) unsigned NOT NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_productoimagen`;

CREATE TABLE `#__sabullvial_productoimagen` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_producto` VARCHAR(15) NOT NULL,
	`images` TEXT NOT NULL,
	`url` TEXT NULL AFTER
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_productoimagen` ADD INDEX `id_producto_idx` (`id_producto`); 

DROP TABLE IF EXISTS `sabullvial_tango_cotizaciones`;
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
        IF(c.id_estado_tango = 5, 'Automática', 
			IF(c.id_estado_tango = 6, 'Prueba', 
		 		IF(c.id_estado_tango = 100, 'Sincronizado', '')
			)
        )
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
WHERE id_estado_tango IN (1, 5, 6, 100)
ORDER BY c.id DESC;

DROP TABLE IF EXISTS `sabullvial_tango_cotizaciones_detalles`;
DROP VIEW IF EXISTS `sabullvial_tango_cotizaciones_detalles`;
CREATE VIEW `sabullvial_tango_cotizaciones_detalles` AS
SELECT 
    cd.id,
    cd.id_cotizacion,
    cd.id_producto codigo_articulo,
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

DROP TABLE IF EXISTS `#__sabullvial_chofer`;

CREATE TABLE `#__sabullvial_chofer` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_remitoestado`;
CREATE TABLE `#__sabullvial_remitoestado` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`numero_remito` VARCHAR(14) NOT NULL,
	`id_estadoremito` INT(11) NOT NULL,
	`delivery_date` datetime NULL DEFAULT '0000-00-00 00:00:00',
	`image` VARCHAR(255) NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_remitoestado` ADD INDEX `numero_remito_idx` (`numero_remito`);

DROP TABLE IF EXISTS `#__sabullvial_cotizacionhistorico`;

CREATE TABLE IF NOT EXISTS `#__sabullvial_cotizacionhistorico` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion` INT(11) NOT NULL,
	`id_estadocotizacion` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_cotizacionhistorico` ADD INDEX `id_cotizacion_idx` (`id_cotizacion`);

DROP TABLE IF EXISTS `#__sabullvial_cotizaciontangohistorico`;

CREATE TABLE `#__sabullvial_cotizaciontangohistorico` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion` INT(11) NOT NULL,
	`id_estado_tango` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_remitohistorico`;

CREATE TABLE `#__sabullvial_remitohistorico` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`numero_remito` VARCHAR(14) NOT NULL,
	`id_estadoremito` INT(11) NOT NULL,
	`image` VARCHAR(255) NULL,
	`image_optimized` TINYINT(1) NOT NULL DEFAULT 0,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_remitohistorico` ADD INDEX `numero_remito_idx` (`numero_remito`);

DROP TABLE IF EXISTS `#__sabullvial_vehiculo`;

CREATE TABLE `#__sabullvial_vehiculo` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_chofer` INT(11) NOT NULL DEFAULT 0, 
	`patente` VARCHAR(20) NOT NULL,
	`marca` VARCHAR(255) NULL DEFAULT '',
	`modelo` VARCHAR(255) NULL DEFAULT '',
	`monto_seguro` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_revisiondetalle` ADD INDEX `id_cotizacion_detalle_idx` (`id_cotizacion_detalle`);
ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD INDEX `id_cotizacion_idx` (`id_cotizacion`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_estadocotizacion_idx` (`id_estadocotizacion`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_estado_tango_idx` (`id_estado_tango`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_cliente_idx` (`id_cliente`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `published_idx` (`published`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `created_by_idx` (`created_by`);

DROP TABLE IF EXISTS `#__sabullvial_estadocliente`;

CREATE TABLE IF NOT EXISTS `#__sabullvial_estadocliente`  (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL,
	`aprobado` tinyint(4) UNSIGNED NULL DEFAULT '0',
 	`pendiente` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`rechazado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`cancelado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadocliente` (`nombre`, `color`, `aprobado`, `pendiente`, `rechazado`, `cancelado`, `created`) VALUES
('Creado', '#339c03', 0, 0, 0, 0, now()),
('Aprobado', '#3c763d', 1, 0, 0, 0, now()),
('Aprobado automático', '#3c763d', 1, 0, 0, 0, now()),
('Pendiente de aprobación', 'orange', 0, 1, 0, 0, now()),
('Rechazado', '#a94442', 0, 0, 1, 0, now()),
('Cancelado', '#bd362f', 0, 0, 0, 1, now());

DROP TABLE IF EXISTS `#__sabullvial_formapago`;

CREATE TABLE `#__sabullvial_formapago` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_formapago` (`nombre`, `created`) VALUES
('Cheque físico', now()),
('eCheck', now()),
('Transferencia Bancaria', now());

DROP TABLE IF EXISTS `#__sabullvial_clienteformapago`;

CREATE TABLE `#__sabullvial_clienteformapago` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cliente` INT(11) NOT NULL,
	`id_formapago` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Optimizaciones para las tablas SIT
ALTER TABLE `SIT_CLIENTES` CHANGE `COD_CLIENT` `COD_CLIENT` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `SIT_CLIENTES_DIRECCION_ENTREGA` CHANGE `COD_CLIENT` `COD_CLIENT` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
DROP TABLE IF EXISTS `#__sabullvial_tarea`;

CREATE TABLE IF NOT EXISTS `#__sabullvial_tarea` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_regla` INT(11) NULL, 
	`id_cliente` INT(11) NULL ,
	`codigo_cliente` VARCHAR(6) NULL,
	`id_cotizacion` INT(11) NULL,
	`group_id` int unsigned NOT NULL DEFAULT 0,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT NULL DEFAULT NULL,
	`start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`expiration_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`task_type` varchar(255) NOT NULL DEFAULT '',
	`task_value` varchar(255) NOT NULL DEFAULT '',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_tarea` ADD INDEX `codigo_cliente_idx` (`codigo_cliente`);
ALTER TABLE `#__sabullvial_tarea` ADD INDEX `id_cliente_idx` (`id_cliente`);
ALTER TABLE `#__sabullvial_tarea` ADD INDEX `id_cotizacion_idx` (`id_cotizacion`); 

CREATE TABLE IF NOT EXISTS `#__sabullvial_tareausuario` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_tarea` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_regla`;

CREATE TABLE `#__sabullvial_regla` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT NULL DEFAULT NULL,
	`event_create` VARCHAR(255) NOT NULL,
	`event_create_value` VARCHAR(255) NOT NULL,
	`event_close` VARCHAR(255) NOT NULL,
	`event_close_value` VARCHAR(255) NOT NULL,
	`data` MEDIUMTEXT NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `SIT_CLIENTES` ADD INDEX `COD_VENDED_idx` (`COD_VENDED`);
ALTER TABLE `SIT_CLIENTES` ADD INDEX `INHABILITADO_idx` (`INHABILITADO`); 

ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `tango_enviar_idx` (`tango_enviar`); 
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `created_idx` (`created`);

DROP TABLE IF EXISTS `#__sabullvial_tareanota`;

CREATE TABLE `#__sabullvial_tareanota` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_tarea` INT(11) NOT NULL,
  	`body` text NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_tareanota` ADD INDEX `id_tarea_idx` (`id_tarea`); 

DROP TABLE IF EXISTS `#__sabullvial_deposito`;

CREATE TABLE `#__sabullvial_deposito` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`id_tango` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_deposito` (`id_tango`, `name`, `created`) VALUES
(1, 'Esteban Echeverría', now()),
(3, 'Spegazzini', now());

DROP TABLE IF EXISTS `#__sabullvial_cotizacionpagohistorico`;

CREATE TABLE `#__sabullvial_cotizacionpagohistorico` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion` INT(11) NOT NULL,
	`id_estadocotizacionpago` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sabullvial_estadocotizacionpago`;

CREATE TABLE `#__sabullvial_estadocotizacionpago` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL DEFAULT '#ffffff',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadocotizacionpago` (`nombre`, `color`, `color_texto`, `created`) VALUES
('En espera', '#ff9900', '#ffffff', now()),
('Pagado', '#46a546', '#ffffff', now());