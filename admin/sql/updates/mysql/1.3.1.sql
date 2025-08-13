ALTER TABLE `#__sabullvial_tarea` ADD INDEX `codigo_cliente_idx` (`codigo_cliente`);
ALTER TABLE `#__sabullvial_tarea` ADD INDEX `id_cliente_idx` (`id_cliente`);
ALTER TABLE `#__sabullvial_tarea` ADD INDEX `id_cotizacion_idx` (`id_cotizacion`); 

ALTER TABLE `#__sabullvial_tareanota` ADD INDEX `id_tarea_idx` (`id_tarea`); 