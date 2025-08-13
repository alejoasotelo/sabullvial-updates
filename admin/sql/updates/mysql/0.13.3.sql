ALTER TABLE `#__sabullvial_cliente` ADD `id_estadocliente` INT(11) NOT NULL AFTER `id`;

ALTER TABLE `#__sabullvial_cliente` ADD INDEX `id_estadocliente_idx` (`id_estadocliente`);
ALTER TABLE `#__sabullvial_cliente` ADD INDEX `id_condicionventa_idx` (`id_condicionventa`);
ALTER TABLE `#__sabullvial_cliente` ADD INDEX `created_by_idx` (`created_by`);