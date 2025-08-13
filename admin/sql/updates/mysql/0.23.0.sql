CREATE TABLE IF NOT EXISTS `#__sabullvial_tarea` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_cliente` int unsigned NOT NULL DEFAULT 0,
	`group_id` int unsigned NOT NULL DEFAULT 0,
	`name` VARCHAR(255) NOT NULL,
	`start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`expiration_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`task_type` tinyint(4) NOT NULL DEFAULT '0',
	`task_value` varchar(255) NOT NULL DEFAULT '',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__sabullvial_tareausuario` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_tarea` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;