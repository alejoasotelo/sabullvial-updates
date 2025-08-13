ALTER TABLE `#__sabullvial_cotizacion` ADD `id_lista_precio` INT(11) NULL AFTER `id_transporte`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `documento_tipo` INT(11) NULL AFTER `cliente`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `documento_numero` VARCHAR(50) NULL AFTER `documento_tipo`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `porcentaje_iibb` DECIMAL(20, 6) NULL AFTER `descuento`;