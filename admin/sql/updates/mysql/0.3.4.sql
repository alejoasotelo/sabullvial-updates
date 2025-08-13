DROP TABLE IF EXISTS `#__sabullvial_productoimagen`;

CREATE TABLE `#__sabullvial_productoimagen` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_producto` INT(11) NOT NULL,
	`images` TEXT NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
