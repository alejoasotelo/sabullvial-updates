ALTER TABLE `#__sabullvial_estadocotizacion` CHANGE `color` `color` VARCHAR(255) NULL; 

INSERT INTO `#__sabullvial_estadocotizacion` (`nombre`, `color`, `created`) VALUES
('Normal', '', now()),
('Pendiente de aprobación', 'orange', now()),
('Aprobado', '#3c763d', now()),
('Facturado', '#31708f', now()),
('Rechazado', '#a94442', now()),
('Fac. Automático', 'violet', now()),
('Prueba', 'muted', now());