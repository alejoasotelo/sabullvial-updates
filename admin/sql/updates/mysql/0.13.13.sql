ALTER TABLE `#__sabullvial_cliente` CHANGE `codigo_vendedor` `codigo_vendedor` VARCHAR(10) NULL DEFAULT '' AFTER `id_condicionventa`;
ALTER TABLE `#__sabullvial_cliente` ADD `codigo_categoria_iva` VARCHAR(10) NULL DEFAULT 'RI' AFTER `codigo_vendedor`;
ALTER TABLE `#__sabullvial_cliente` ADD `codigo_rubro` VARCHAR(10) NULL DEFAULT '' AFTER `codigo_categoria_iva`;
ALTER TABLE `#__sabullvial_cliente` ADD `codigo_zona` VARCHAR(10) NULL DEFAULT '' AFTER `codigo_rubro`;
ALTER TABLE `#__sabullvial_cliente` ADD `nombre_comercial` VARCHAR(255) NULL AFTER `razon_social`;
ALTER TABLE `#__sabullvial_cliente` ADD `documento_tipo` INT(10) NULL DEFAULT 80 AFTER `actividad_comercial`;
ALTER TABLE `#__sabullvial_cliente` ADD `codigo_lista` int(10) NOT NULL DEFAULT 1 AFTER `id_condicionventa`;
ALTER TABLE `#__sabullvial_cliente` CHANGE `cuit` `documento_numero` VARCHAR(20) NULL;