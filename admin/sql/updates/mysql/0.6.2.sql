CREATE TABLE IF NOT EXISTS `#__sabullvial_vehiculo` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_chofer` INT(11) NOT NULL DEFAULT 0,
	`patente` VARCHAR(20) NOT NULL,
	`marca` VARCHAR(255) NULL DEFAULT '',
	`modelo` VARCHAR(255) NULL DEFAULT '',
	`monto_seguro` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__sabullvial_hojaderuta` ADD `id_vehiculo` INT(11) NULL DEFAULT 0 AFTER `id`;