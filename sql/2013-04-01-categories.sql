CREATE TABLE `platform_categories` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform_id` INTEGER UNSIGNED NOT NULL,
  `platform_category_id` INTEGER UNSIGNED NOT NULL,
  `category_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX (`platform_id`, `platform_category_id`)
)
ENGINE = InnoDB;

ALTER TABLE `project_categories` ADD UNIQUE INDEX (`project_id`);