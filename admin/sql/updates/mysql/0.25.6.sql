ALTER TABLE `#__sabullvial_tarea` CHANGE `id_cliente` `codigo_cliente` varchar(6) NULL;
ALTER TABLE `#__sabullvial_tarea` ADD `id_cliente` INT(11) NULL AFTER `id`;
