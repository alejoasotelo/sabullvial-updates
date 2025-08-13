INSERT INTO `#__sabullvial_estadocotizacion` (`nombre`, `color`, `aprobado`, `pendiente`, `rechazado`, `revisado`, `created`) VALUES
('Aprobado completo', '#00b078', 1, 0, 0, 1, now()),
('Aprobado con faltantes', '#3b50f5', 1, 0, 0, 2, now());

UPDATE `#__sabullvial_estadocotizacion` SET `nombre` = 'Cotizado' WHERE `nombre` = 'Normal';
UPDATE `#__sabullvial_estadocotizacion` SET `revisado` = 1 WHERE `nombre` = 'Pedido completo';
UPDATE `#__sabullvial_estadocotizacion` SET `revisado` = 2 WHERE `nombre` = 'Pedidos con faltantes';