ALTER TABLE `#__sabullvial_estadoremito` ADD `preparacion` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `color_texto`;
ALTER TABLE `#__sabullvial_estadoremito` ADD `transito` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `preparacion`;
ALTER TABLE `#__sabullvial_estadoremito` ADD `entregado` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `transito`;

UPDATE `#__sabullvial_estadoremito` SET `preparacion` = '1' WHERE nombre like '%en preparacion%'; 
UPDATE `#__sabullvial_estadoremito` SET `transito` = '1' WHERE nombre = 'En transito'; 
UPDATE `#__sabullvial_estadoremito` SET `entregado` = '1' WHERE nombre = 'Entregado'; 