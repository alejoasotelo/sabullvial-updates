ALTER TABLE `#__sabullvial_estadoremito` ADD `entregado_mostrador` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `entregado`;

INSERT INTO `#__sabullvial_estadoremito` (`nombre`, `color`, `color_texto`, `preparacion`, `transito`, `entregado`, `entregado_mostrador`, `created`) VALUES
('Entregado por mostrador', '#46a546', '#ffffff', 0, 0, 0, 1, now());
