CREATE TABLE IF NOT EXISTS `#__sabullvial_estadocotizacionpago` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL DEFAULT '#ffffff',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadocotizacionpago` (`nombre`, `color`, `color_texto`, `created`) VALUES
('En espera', '#ff9900', '#ffffff', now()),
('Pagado', '#46a546', '#ffffff', now());