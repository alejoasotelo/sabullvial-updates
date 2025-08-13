ALTER TABLE `#__sabullvial_remitoestado` DROP `delivery_images`;

ALTER TABLE `#__sabullvial_remitoestado` ADD `image` VARCHAR(255) NULL AFTER `delivery_date`;
ALTER TABLE `#__sabullvial_remitohistorico` ADD `image` VARCHAR(255) NULL AFTER `id_estadoremito`;