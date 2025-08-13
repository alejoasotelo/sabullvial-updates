ALTER TABLE `#__sabullvial_estadocotizacion` ADD COLUMN `access` tinyint(4) NOT NULL DEFAULT '0' AFTER `published`;
UPDATE `#__sabullvial_estadocotizacion` SET `access` = 1;
/*
INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES ('SABullvial Estado Cotizacion', 'com_sabullvial.estadocotizacion', '', '', '{"common": {"core_content_item_id": "id", "core_title": "nombre", "core_state": "published", "core_alias": "null", "core_language":"null", "core_created_time": "created", "core_body": "null", "core_access": "access", "core_catid": "null"}}', '', '');*/