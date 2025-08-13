ALTER TABLE `#__sabullvial_cliente` ADD `monto` DECIMAL(20, 6) NOT NULL DEFAULT '0.000000' AFTER `aclaracion`;
ALTER TABLE `#__sabullvial_cliente` ADD `plazo` VARCHAR(255) NULL DEFAULT '' AFTER `monto`;

INSERT INTO `#__sabullvial_estadocliente` (`id`, `nombre`, `color`, `color_texto`, `aprobado`, `pendiente`, `rechazado`, `cancelado`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `published`) VALUES (NULL, 'Aprobado automático', '#3c763d', NULL, '1', '0', '0', '0', '2023-06-26 15:51:22', '0', '', '0000-00-00 00:00:00', '0', '1'); 