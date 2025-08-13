CREATE TABLE IF NOT EXISTS `#__sabullvial_cotizacionhistorico` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cotizacion` INT(11) NOT NULL,
	`id_estadocotizacion` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;