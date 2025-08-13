ALTER TABLE `#__sabullvial_estadocotizacion` ADD COLUMN `aprobado` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `color_texto`;
ALTER TABLE `#__sabullvial_estadocotizacion` ADD COLUMN `pendiente` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `aprobado`;
ALTER TABLE `#__sabullvial_estadocotizacion` ADD COLUMN `rechazado` tinyint(4) UNSIGNED NULL DEFAULT '0' AFTER `pendiente`;