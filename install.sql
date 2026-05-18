

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

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
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Аккаунт администратора: логин Admin26, пароль Demo20
-- Тестовые пользователи (пароль у всех: Test1234)
INSERT INTO `users` (`login`, `password`, `fio`, `birthdate`, `phone`, `email`, `is_admin`) VALUES
('Admin26', '$2y$10$KJn.iE6kvaUugWCp/bOn3eyd/W0C2ILw57Sike8U7AYEni8G4Beiy', 'Администратор Водить.РФ', '1990-01-01', '+7 (495) 123-45-67', 'admin@vodit.rf', 1),
('ivanov01', '$2y$10$TuzYz5yL7ntxKPF4RkB.DO396lDw87POi3YWFN1g0d0Xa.0H1VuwC', 'Иванов Иван Иванович', '1995-03-15', '+7 (916) 100-20-30', 'ivanov@example.com', 0),
('petrova22', '$2y$10$TuzYz5yL7ntxKPF4RkB.DO396lDw87POi3YWFN1g0d0Xa.0H1VuwC', 'Петрова Анна Сергеевна', '1992-07-22', '+7 (903) 200-30-40', 'petrova@example.com', 0),
('sidorov88', '$2y$10$TuzYz5yL7ntxKPF4RkB.DO396lDw87POi3YWFN1g0d0Xa.0H1VuwC', 'Сидоров Алексей Петрович', '1988-11-05', '+7 (985) 300-40-50', 'sidorov@example.com', 0);

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

-- Тестовые заявки для демонстрации админ-панели
INSERT INTO `applications` (`user_id`, `transport_type`, `start_date`, `payment_method`, `status`) VALUES
(2, 'Катер',           '2026-06-01', 'Предоплата по QR-коду',  'Новая'),
(2, 'Яхта',            '2026-07-10', 'Оплата картой МИР',      'Идет обучение'),
(3, 'Круизный лайнер', '2026-06-15', 'Постоплата в офисе',     'Обучение завершено'),
(3, 'Катер',           '2026-08-01', 'Оплата картой МИР',      'Новая'),
(4, 'Яхта',            '2026-05-25', 'Предоплата по QR-коду',  'Идет обучение');

-- Отзыв к завершённой заявке
INSERT INTO `reviews` (`application_id`, `user_id`, `rating`, `text`) VALUES
(3, 3, 5, 'Отличное обучение! Инструктор внимательный, программа интересная. Рекомендую.');
