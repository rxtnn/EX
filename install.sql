-- Водить.РФ — схема базы данных
-- Импортировать через phpMyAdmin

CREATE DATABASE IF NOT EXISTS `vodit_rf` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vodit_rf`;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(64) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fio` VARCHAR(150) NOT NULL,
  `birthdate` DATE NOT NULL,
  `phone` VARCHAR(32) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `transport_type` ENUM('Катер','Круизный лайнер','Яхта') NOT NULL,
  `start_date` DATE NOT NULL,
  `payment_method` ENUM('Предоплата по QR-коду','Оплата картой МИР','Постоплата в офисе') NOT NULL,
  `status` ENUM('Новая','Идет обучение','Обучение завершено') NOT NULL DEFAULT 'Новая',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_app_user` (`user_id`),
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` INT UNSIGNED NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rev_app` (`application_id`),
  KEY `fk_rev_user` (`user_id`),
  CONSTRAINT `fk_rev_app` FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
