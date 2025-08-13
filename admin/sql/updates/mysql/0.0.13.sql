ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD `codigo_sap` VARCHAR(255)  NOT NULL AFTER `id_producto`;
ALTER TABLE `#__sabullvial_cotizaciondetalle` ADD `marca` VARCHAR(255)  NOT NULL AFTER `nombre`;
ALTER TABLE `#__sabullvial_cotizaciondetalle` CHANGE `precio_total` `subtotal` DECIMAL(20,6) NOT NULL DEFAULT '0.000000'; 