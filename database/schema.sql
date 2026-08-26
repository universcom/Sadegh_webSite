-- ---------------------------------------------------------------------------
-- Rahyaft Sanat — database schema
-- MySQL 5.7+ / MariaDB 10.2+ , InnoDB, utf8mb4.
--
-- Translation strategy: every translatable entity has a companion
-- <entity>_translations table keyed by (entity_id, lang). Adding a language is
-- a data change, never a schema change.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --- Administrators ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('owner','admin','editor') NOT NULL DEFAULT 'editor',
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `last_login_ip` VARCHAR(45) NULL DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login throttling. Rows older than 24h are pruned opportunistically.
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(190) NOT NULL,
  `ip_address`   VARCHAR(45) NOT NULL,
  `success`      TINYINT(1) NOT NULL DEFAULT 0,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempt_lookup` (`attempted_at`, `success`),
  KEY `idx_attempt_email` (`email`),
  KEY `idx_attempt_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Media library ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path`          VARCHAR(255) NOT NULL COMMENT 'relative to /uploads',
  `basename`      VARCHAR(190) NULL DEFAULT NULL COMMENT 'stem shared by responsive variants',
  `variants`      VARCHAR(190) NULL DEFAULT NULL COMMENT 'JSON array of generated widths',
  `original_name` VARCHAR(190) NULL DEFAULT NULL,
  `mime`          VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
  `kind`          ENUM('image','document') NOT NULL DEFAULT 'image',
  `size`          INT UNSIGNED NOT NULL DEFAULT 0,
  `width`         SMALLINT UNSIGNED NULL DEFAULT NULL,
  `height`        SMALLINT UNSIGNED NULL DEFAULT NULL,
  `alt_fa`        VARCHAR(255) NULL DEFAULT NULL,
  `alt_en`        VARCHAR(255) NULL DEFAULT NULL,
  `alt_ar`        VARCHAR(255) NULL DEFAULT NULL,
  `source_ref`    VARCHAR(255) NULL DEFAULT NULL COMMENT 'original raw-material path, for provenance',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_kind` (`kind`),
  KEY `idx_media_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Product categories -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id`  INT UNSIGNED NULL DEFAULT NULL,
  `slug`       VARCHAR(190) NOT NULL,
  `image_id`   INT UNSIGNED NULL DEFAULT NULL,
  `icon`       VARCHAR(40) NULL DEFAULT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_slug` (`slug`),
  KEY `idx_category_parent` (`parent_id`),
  KEY `idx_category_listing` (`is_active`, `sort_order`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_category_image`  FOREIGN KEY (`image_id`)  REFERENCES `media` (`id`)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `category_translations` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`     INT UNSIGNED NOT NULL,
  `lang`            VARCHAR(5) NOT NULL,
  `name`            VARCHAR(190) NOT NULL,
  `description`     TEXT NULL DEFAULT NULL,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(320) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_lang` (`category_id`, `lang`),
  KEY `idx_category_tr_lang` (`lang`),
  CONSTRAINT `fk_category_tr` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Products ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`    INT UNSIGNED NULL DEFAULT NULL,
  `slug`           VARCHAR(190) NOT NULL,
  `model_code`     VARCHAR(90) NULL DEFAULT NULL,
  `cover_image_id` INT UNSIGNED NULL DEFAULT NULL,
  `is_featured`    TINYINT(1) NOT NULL DEFAULT 0,
  `status`         ENUM('published','draft','archived') NOT NULL DEFAULT 'published',
  `sort_order`     SMALLINT NOT NULL DEFAULT 0,
  `needs_review`   TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'auto-imported content an operator should verify',
  `source_ref`     VARCHAR(255) NULL DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_slug` (`slug`),
  KEY `idx_product_category` (`category_id`),
  KEY `idx_product_listing` (`status`, `sort_order`),
  KEY `idx_product_featured` (`is_featured`, `status`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`)    REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_product_cover`    FOREIGN KEY (`cover_image_id`) REFERENCES `media` (`id`)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_translations` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`      INT UNSIGNED NOT NULL,
  `lang`            VARCHAR(5) NOT NULL,
  `name`            VARCHAR(190) NOT NULL,
  `summary`         VARCHAR(500) NULL DEFAULT NULL,
  `description`     MEDIUMTEXT NULL DEFAULT NULL,
  `applications`    TEXT NULL DEFAULT NULL COMMENT 'one application per line',
  `advantages`      TEXT NULL DEFAULT NULL COMMENT 'one advantage per line',
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(320) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_lang` (`product_id`, `lang`),
  KEY `idx_product_tr_lang` (`lang`),
  KEY `idx_product_tr_name` (`name`),
  CONSTRAINT `fk_product_tr` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `media_id`   INT UNSIGNED NOT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_media` (`product_id`, `media_id`),
  KEY `idx_product_image_order` (`product_id`, `sort_order`),
  CONSTRAINT `fk_pimage_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pimage_media`   FOREIGN KEY (`media_id`)   REFERENCES `media` (`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Specification groups let one product carry several spec tables
-- (e.g. the 5040 mill: TRAVELS / SPINDLE / TABLE / FEEDRATE / TOOL CHANGER).
CREATE TABLE IF NOT EXISTS `product_spec_groups` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_specgroup_product` (`product_id`, `sort_order`),
  CONSTRAINT `fk_specgroup_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_spec_group_translations` (
  `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT UNSIGNED NOT NULL,
  `lang`     VARCHAR(5) NOT NULL,
  `title`    VARCHAR(190) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_specgroup_lang` (`group_id`, `lang`),
  CONSTRAINT `fk_specgroup_tr` FOREIGN KEY (`group_id`) REFERENCES `product_spec_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_specs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `group_id`   INT UNSIGNED NULL DEFAULT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_spec_product` (`product_id`, `sort_order`),
  KEY `idx_spec_group` (`group_id`),
  CONSTRAINT `fk_spec_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)            ON DELETE CASCADE,
  CONSTRAINT `fk_spec_group`   FOREIGN KEY (`group_id`)   REFERENCES `product_spec_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_spec_translations` (
  `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `spec_id` INT UNSIGNED NOT NULL,
  `lang`    VARCHAR(5) NOT NULL,
  `label`   VARCHAR(190) NOT NULL,
  `value`   VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_spec_lang` (`spec_id`, `lang`),
  CONSTRAINT `fk_spec_tr` FOREIGN KEY (`spec_id`) REFERENCES `product_specs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_features` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_feature_product` (`product_id`, `sort_order`),
  CONSTRAINT `fk_feature_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_feature_translations` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `feature_id` INT UNSIGNED NOT NULL,
  `lang`       VARCHAR(5) NOT NULL,
  `text`       VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_feature_lang` (`feature_id`, `lang`),
  CONSTRAINT `fk_feature_tr` FOREIGN KEY (`feature_id`) REFERENCES `product_features` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_downloads` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `media_id`   INT UNSIGNED NOT NULL,
  `title_fa`   VARCHAR(190) NULL DEFAULT NULL,
  `title_en`   VARCHAR(190) NULL DEFAULT NULL,
  `title_ar`   VARCHAR(190) NULL DEFAULT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_download_product` (`product_id`, `sort_order`),
  CONSTRAINT `fk_download_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_download_media`   FOREIGN KEY (`media_id`)   REFERENCES `media` (`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Research & development projects ----------------------------------------
CREATE TABLE IF NOT EXISTS `research_projects` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`           VARCHAR(190) NOT NULL,
  `cover_image_id` INT UNSIGNED NULL DEFAULT NULL,
  `icon`           VARCHAR(40) NULL DEFAULT NULL,
  `sort_order`     SMALLINT NOT NULL DEFAULT 0,
  `status`         ENUM('published','draft') NOT NULL DEFAULT 'published',
  `source_ref`     VARCHAR(255) NULL DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_research_slug` (`slug`),
  KEY `idx_research_listing` (`status`, `sort_order`),
  CONSTRAINT `fk_research_cover` FOREIGN KEY (`cover_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research_project_translations` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`      INT UNSIGNED NOT NULL,
  `lang`            VARCHAR(5) NOT NULL,
  `title`           VARCHAR(190) NOT NULL,
  `summary`         VARCHAR(500) NULL DEFAULT NULL,
  `body`            MEDIUMTEXT NULL DEFAULT NULL,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(320) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_research_lang` (`project_id`, `lang`),
  CONSTRAINT `fk_research_tr` FOREIGN KEY (`project_id`) REFERENCES `research_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `research_project_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `media_id`   INT UNSIGNED NOT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_research_media` (`project_id`, `media_id`),
  KEY `idx_research_image_order` (`project_id`, `sort_order`),
  CONSTRAINT `fk_rimage_project` FOREIGN KEY (`project_id`) REFERENCES `research_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rimage_media`   FOREIGN KEY (`media_id`)   REFERENCES `media` (`id`)             ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Editorial pages --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(190) NOT NULL COMMENT 'home | about | contact | research',
  `is_system`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'system pages cannot be deleted',
  `status`     ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_translations` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`         INT UNSIGNED NOT NULL,
  `lang`            VARCHAR(5) NOT NULL,
  `title`           VARCHAR(190) NOT NULL,
  `subtitle`        VARCHAR(320) NULL DEFAULT NULL,
  `body`            MEDIUMTEXT NULL DEFAULT NULL,
  `seo_title`       VARCHAR(190) NULL DEFAULT NULL,
  `seo_description` VARCHAR(320) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_lang` (`page_id`, `lang`),
  CONSTRAINT `fk_page_tr` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modular page content. `type` selects the renderer; `settings` carries
-- per-type options as JSON (kept as TEXT for MySQL 5.6/MariaDB portability).
CREATE TABLE IF NOT EXISTS `page_sections` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`    INT UNSIGNED NOT NULL,
  `type`       VARCHAR(40) NOT NULL COMMENT 'hero|richtext|image_text|stats|features|gallery|cta|quote',
  `media_id`   INT UNSIGNED NULL DEFAULT NULL,
  `settings`   TEXT NULL DEFAULT NULL,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_section_page` (`page_id`, `sort_order`),
  CONSTRAINT `fk_section_page`  FOREIGN KEY (`page_id`)  REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_section_media` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_section_translations` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` INT UNSIGNED NOT NULL,
  `lang`       VARCHAR(5) NOT NULL,
  `heading`    VARCHAR(255) NULL DEFAULT NULL,
  `subheading` VARCHAR(500) NULL DEFAULT NULL,
  `body`       MEDIUMTEXT NULL DEFAULT NULL,
  `cta_label`  VARCHAR(120) NULL DEFAULT NULL,
  `cta_url`    VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_lang` (`section_id`, `lang`),
  CONSTRAINT `fk_section_tr` FOREIGN KEY (`section_id`) REFERENCES `page_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Contact enquiries ------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(190) NOT NULL,
  `phone`      VARCHAR(40) NULL DEFAULT NULL,
  `company`    VARCHAR(190) NULL DEFAULT NULL,
  `subject`    VARCHAR(190) NOT NULL,
  `message`    TEXT NOT NULL,
  `status`     ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `lang`       VARCHAR(5) NOT NULL DEFAULT 'fa',
  `product_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'set when sent from a product enquiry CTA',
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `notified`   TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'admin e-mail notification delivered',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at`    DATETIME NULL DEFAULT NULL,
  `replied_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_message_status` (`status`, `created_at`),
  KEY `idx_message_created` (`created_at`),
  KEY `idx_message_email` (`email`),
  CONSTRAINT `fk_message_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --- Settings ---------------------------------------------------------------
-- lang = '' for language-neutral values (logo, phone), or a language code for
-- translatable ones (site title, footer blurb).
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `skey`       VARCHAR(90) NOT NULL,
  `lang`       VARCHAR(5) NOT NULL DEFAULT '',
  `svalue`     TEXT NULL DEFAULT NULL,
  `group_name` VARCHAR(40) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting` (`skey`, `lang`),
  KEY `idx_setting_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
