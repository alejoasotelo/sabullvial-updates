ALTER TABLE `#__sabullvial_estadoremito` ADD `proceso` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `color_texto`;

UPDATE `#__sabullvial_estadoremito` SET `proceso` = 1, `preparacion` = 0 WHERE `preparacion` = 1;

INSERT INTO `#__sabullvial_estadoremito` 
(`nombre`, `color`, `color_texto`, `proceso`, `preparacion`, `transito`, `entregado`, `entregado_mostrador`, `created`) 
VALUES ('En preparación', '#ff9900', '#ffffff', 0, 1, 0, 0, 0, now());