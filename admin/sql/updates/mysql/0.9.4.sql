ALTER TABLE `#__sabullvial_revisiondetalle` ADD INDEX `id_cotizacion_detalle_idx` (`id_cotizacion_detalle`);
ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD INDEX `id_cotizacion_idx` (`id_cotizacion`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_estadocotizacion_idx` (`id_estadocotizacion`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_estado_tango_idx` (`id_estado_tango`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `id_cliente_idx` (`id_cliente`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `published_idx` (`published`);
ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `created_by_idx` (`created_by`);

