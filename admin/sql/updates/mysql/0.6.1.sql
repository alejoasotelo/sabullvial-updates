ALTER TABLE `#__sabullvial_hojaderuta` DROP COLUMN `codigo_transporte`;
ALTER TABLE `#__sabullvial_hojaderuta` ADD `id_chofer` INT(11) NULL DEFAULT 0 AFTER `id`;

ALTER TABLE `#__sabullvial_chofer` DROP COLUMN `codigo_transporte`;
ALTER TABLE `#__sabullvial_chofer` DROP COLUMN `patente`;