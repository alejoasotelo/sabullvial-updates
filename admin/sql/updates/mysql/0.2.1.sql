ALTER TABLE `#__sabullvial_revisiondetalle` ADD `id_cotizacion_detalle` INT(11)  NOT NULL AFTER `id`;
ALTER TABLE `#__sabullvial_revisiondetalle` DROP COLUMN `id_cotizacion`;
ALTER TABLE `#__sabullvial_revisiondetalle` DROP COLUMN `id_producto`;