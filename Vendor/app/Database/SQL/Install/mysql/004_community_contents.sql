-- Product Store: community content tables (MySQL / MariaDB). Token __DB_PREFIX__ is replaced at install time (may be empty).
CREATE TABLE IF NOT EXISTS `__DB_PREFIX__community_contents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(180) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Unknown',
  `body` MEDIUMTEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'published',
  `author_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `__DB_PREFIX__community_contents_category_idx` (`category`),
  KEY `__DB_PREFIX__community_contents_status_idx` (`status`),
  KEY `__DB_PREFIX__community_contents_author_idx` (`author_id`),
  KEY `__DB_PREFIX__community_contents_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
