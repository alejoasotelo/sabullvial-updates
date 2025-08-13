ALTER TABLE `#__sabullvial_cotizacion` ADD `id_estadocotizacion` INT(11)  NOT NULL AFTER `id_cliente`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `id_condicionventa` INT(11)  NOT NULL AFTER `id_estadocotizacion`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `delivery_term` VARCHAR(255) NULL AFTER `email`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `iva` tinyint NOT NULL DEFAULT 0 AFTER `delivery_term`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `dolar` tinyint NOT NULL DEFAULT 0 AFTER `iva`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `products` TEXT NULL AFTER `dolar`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `observations` varchar(5120) NULL DEFAULT '' AFTER `products`;