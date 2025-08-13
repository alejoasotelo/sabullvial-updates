DROP TABLE IF EXISTS `#__sabullvial_remito`;

CREATE TABLE `#__sabullvial_remito` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_hojaderuta` INT(11) NOT NULL,
	`numero_remito` VARCHAR(14) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__sabullvial_hojaderuta`;

CREATE TABLE `#__sabullvial_hojaderuta` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NULL,
	`codigo_transporte` VARCHAR(10) NOT NULL,
	`chofer` VARCHAR(255) NULL,
	`patente_transporte` VARCHAR(20) NOT NULL,
	`delivery_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`delivered_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;