ALTER TABLE `projects` ADD UNIQUE (`external_id`, `platform_id`);

ALTER TABLE `projects` CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `projects` MODIFY COLUMN `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL;
ALTER TABLE `projects` MODIFY COLUMN `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;