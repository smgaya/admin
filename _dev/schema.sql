DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT,
    `slug` VARCHAR(50),
    `name` VARCHAR(50),
    `cover` VARCHAR(150),
    `description` TEXT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `gallery_media`;
CREATE TABLE `gallery_media` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT,
    `gallery` BIGINT,
    `media` VARCHAR(150),
    `title` VARCHAR(150),
    `description` TEXT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT NOT NULL,
    `original_name` VARCHAR(150),
    `name` VARCHAR(50),
    `path` VARCHAR(75),
    `type` VARCHAR(100),
    `object` BIGINT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `site_menu`;
CREATE TABLE `site_menu` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(25),
    `label` VARCHAR(50),
    `url` VARCHAR(200),
    `parent` INTEGER,
    `index` TINYINT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `site_params`;
CREATE TABLE `site_params` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50),
    `value` TEXT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `site_ranks`;
CREATE TABLE `site_ranks` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `vendor` VARCHAR(50),
    `rank_international` INTEGER DEFAULT 0,
    `rank_local` INTEGER DEFAULT 0,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `site_enum`;
CREATE TABLE `site_enum` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(50),
    `value` VARCHAR(50),
    `label` VARCHAR(25),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `slideshow`;
CREATE TABLE `slideshow` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT NOT NULL,
    `group` VARCHAR(50),
    `index` SMALLINT,
    `image` VARCHAR(125),
    `title` VARCHAR(100),
    `link` VARCHAR(120),
    `description` TEXT,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(25) NOT NULL UNIQUE,
    `fullname` VARCHAR(50),
    `password` VARCHAR(125),
    `avatar` VARCHAR(100),
    `about` TEXT,
    `website` VARCHAR(125),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `user_perms`;
CREATE TABLE `user_perms` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT NOT NULL,
    `perms` VARCHAR(50),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `user_session`;
CREATE TABLE `user_session` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user` BIGINT NOT NULL,
    `hash` VARCHAR(150) NOT NULL UNIQUE,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);