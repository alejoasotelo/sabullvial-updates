ALTER TABLE `#__sabullvial_cotizacion` ADD INDEX `tango_fecha_sincronizacion_idx` (`tango_fecha_sincronizacion`);

ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD INDEX `id_producto_idx` (`id_producto`);
ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD INDEX `cantidad_idx` (`cantidad`);