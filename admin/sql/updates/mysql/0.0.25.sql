ALTER TABLE `#__sabullvial_cotizacion` ADD `ordendecompra_file_name` VARCHAR(255) NULL AFTER `products`; 
ALTER TABLE `#__sabullvial_cotizacion` ADD `ordendecompra_file_hash` VARCHAR(255) NULL AFTER `ordendecompra_file_name`;
ALTER TABLE `#__sabullvial_cotizacion` ADD `ordendecompra_file_ext` VARCHAR(10) NULL AFTER `ordendecompra_file_hash`;