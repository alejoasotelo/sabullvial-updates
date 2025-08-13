CREATE TABLE `#__sabullvial_estadoremito` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(255) NOT NULL,
	`color` VARCHAR(255) NULL,
	`color_texto` VARCHAR(255) NULL,
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` int unsigned NOT NULL DEFAULT 0,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__sabullvial_estadoremito` (`nombre`, `color`, `color_texto`, `created`) VALUES
('En preparación', '#ff9900', '#ffffff', now()),
('En tránsito', '#ffd700', '#242424', now()),
('Entregado', '#46a546', '#ffffff', now()),
('No entregado', '#bd362f', '#ffffff', now());
