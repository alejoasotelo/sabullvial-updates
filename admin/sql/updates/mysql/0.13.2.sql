CREATE TABLE IF NOT EXISTS `#__sabullvial_estadocliente`  (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL,
	`aprobado` tinyint(4) UNSIGNED NULL DEFAULT '0',
 	`pendiente` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`rechazado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`cancelado` tinyint(4) UNSIGNED NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadocliente` (`nombre`, `color`, `aprobado`, `pendiente`, `rechazado`, `cancelado`, `created`) VALUES
('Creado', '#339c03', 0, 0, 0, 0, now()),
('Aprobado', '#3c763d', 1, 0, 0, 0, now()),
('Pendiente de aprobación', 'orange', 0, 1, 0, 0, now()),
('Rechazado', '#a94442', 0, 0, 1, 0, now()),
('Cancelado', '#bd362f', 0, 0, 0, 1, now());