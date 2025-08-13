ALTER TABLE `#__sabullvial_cotizacion` DROP `servicio`;
ALTER TABLE `#__sabullvial_cotizacion` CHANGE `name` `reference` VARCHAR(255) NOT NULL;
ALTER TABLE `#__sabullvial_cotizacion` ADD COLUMN `cliente` VARCHAR(255) NOT NULL AFTER `reference`;