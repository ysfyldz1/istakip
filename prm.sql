-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 09 Tem 2025, 23:34:51
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `prm`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `phone`, `email`, `contact_person`, `created_at`, `updated_at`) VALUES
(511, 'Örnek Fimra', 'istnabul', '3434534543534', 'sdfsd@sdgsgd.com', 'Yusuf Yıldız', '2025-07-09 21:13:07', '2025-07-09 21:13:07');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `daily_tasks`
--

CREATE TABLE `daily_tasks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `task_date` date NOT NULL,
  `task_description` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `daily_tasks`
--

INSERT INTO `daily_tasks` (`id`, `user_id`, `company_id`, `task_date`, `task_description`, `notes`, `created_at`, `updated_at`) VALUES
(508, 679, 511, '2025-07-10', 'deneme gorevi', 'dsfs', '2025-07-09 21:19:57', '2025-07-09 21:19:57'),
(509, 682, 511, '2025-07-10', 'test gorevi', 'deneme yapılıyor.', '2025-07-09 21:26:06', '2025-07-09 21:26:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','user','customer') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@demo.com', '$2y$10$5sNSpl3Ip5Af5bkZZyHiSOvG0lOUQN5K7bm4XjRoNHVkv29Z1A9Ru', 'admin', '2025-07-09 20:26:14', '2025-07-09 20:39:02'),
(677, 'Hasan Karaçam', 'hasan@prmymm.com', '$2y$10$jXq8ncoBrHo8aWdJuqW6veiWErerZqjCh2gfqjKnuGyQ2FuPuzeT6', 'manager', '2025-07-09 20:55:32', '2025-07-09 20:55:32'),
(678, 'Duygu Şakar', 'duygu@prmymm.com', '$2y$10$XjRgmTDWSnFAGsNakv3vQOT8RmKoqjKvraqkeg.9DHOhXt1s1pEtK', 'user', '2025-07-09 20:56:09', '2025-07-09 21:21:20'),
(679, 'Kenan Kılıç', 'kenan@prmymm.com', '$2y$10$bgIq3UXF67.9Iob7fiM5D.0XqqixYoTscQPri0jfhd/1V8DFXIIUy', 'user', '2025-07-09 21:00:25', '2025-07-09 21:00:25'),
(680, 'Emircan Göktaş', 'emir@prmymm.com', '$2y$10$ukSEMVaxgOi9ckX/9HMOvOVToKVk8EmzkgO.g4WMYVoibXJIETFWG', 'user', '2025-07-09 21:21:49', '2025-07-09 21:21:49'),
(681, 'Faruk Konya', 'faruk@prmymm.com', '$2y$10$B7KsigKPIIujl7Xx/1FNg.eL9br26THbc3kcUULkKs9FZb77fDgWK', 'user', '2025-07-09 21:22:11', '2025-07-09 21:22:11'),
(682, 'İlknur Karaçam', 'ilknur@prmymm.com', '$2y$10$QfRtEyLJCTpeTasmVJvY.eCxEfXSwoXgYdNi8Y6nkU7jgkFUhu.Ra', 'user', '2025-07-09 21:22:41', '2025-07-09 21:22:41'),
(683, 'Neşe Karaçam', 'nese@prmymm.com', '$2y$10$rnB7BTZGmOyYky88oYYLG.tWq4yyy0PTa2gxRc940q3Q2l57j8YWK', 'user', '2025-07-09 21:23:03', '2025-07-09 21:23:03');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `daily_tasks`
--
ALTER TABLE `daily_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- Tablo için AUTO_INCREMENT değeri `daily_tasks`
--
ALTER TABLE `daily_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=510;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=684;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `daily_tasks`
--
ALTER TABLE `daily_tasks`
  ADD CONSTRAINT `daily_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_tasks_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
