-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 10:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taskmate`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `user_id`, `title`, `event_date`, `event_time`, `description`) VALUES
(4, 1, 'dfgsdfgsdfg', '2026-03-03', NULL, NULL),
(5, 1, 'sdfgfdgsrrrr', '2026-03-03', NULL, NULL),
(7, 1, 'dfgdfgdfg', '2026-03-03', NULL, NULL),
(8, 1, 'dfgertg', '2026-02-08', NULL, NULL),
(9, 1, 'ersretygrtes', '2026-02-05', NULL, NULL),
(10, 7, 'Father\'s Day', '2026-01-12', NULL, 'Public holiday'),
(11, 7, 'Mother\'s Day', '2026-03-21', NULL, 'Public holiday'),
(12, 7, 'Festival of Breaking the Fast', '2026-03-30', NULL, 'Public holiday'),
(13, 7, 'Second Day of the Festival of Breaking the Fast', '2026-03-31', NULL, 'Public holiday'),
(14, 7, 'Third Day of the Festival of Breaking the Fast', '2026-04-01', NULL, 'Public holiday'),
(15, 7, 'Labor Day', '2026-05-01', NULL, 'Public holiday'),
(16, 7, 'Unity Day', '2026-05-22', NULL, 'Public holiday'),
(17, 7, 'Feast of the Sacrifice', '2026-06-06', NULL, 'Public holiday'),
(18, 7, 'Second Day of the Feast of the Sacrifice', '2026-06-07', NULL, 'Public holiday'),
(19, 7, 'Third Day of the Feast of the Sacrifice', '2026-06-08', NULL, 'Public holiday'),
(20, 7, 'Islamic New Year\'s Day', '2026-06-26', NULL, 'Public holiday'),
(21, 7, 'Birth of the Prophet', '2026-09-04', NULL, 'Public holiday'),
(22, 7, 'Yemen Revolution Day', '2026-09-26', NULL, 'Public holiday'),
(23, 7, 'Yemen Independence Day', '2026-11-30', NULL, 'Public holiday'),
(24, 7, 'Father\'s Day', '2027-01-12', NULL, 'Public holiday'),
(25, 7, 'Mother\'s Day', '2027-03-21', NULL, 'Public holiday'),
(26, 7, 'Festival of Breaking the Fast', '2027-03-30', NULL, 'Public holiday'),
(27, 7, 'Second Day of the Festival of Breaking the Fast', '2027-03-31', NULL, 'Public holiday'),
(28, 7, 'Third Day of the Festival of Breaking the Fast', '2027-04-01', NULL, 'Public holiday'),
(29, 7, 'Labor Day', '2027-05-01', NULL, 'Public holiday'),
(30, 7, 'Unity Day', '2027-05-22', NULL, 'Public holiday'),
(31, 7, 'Feast of the Sacrifice', '2027-06-06', NULL, 'Public holiday'),
(32, 7, 'Second Day of the Feast of the Sacrifice', '2027-06-07', NULL, 'Public holiday'),
(33, 7, 'Third Day of the Feast of the Sacrifice', '2027-06-08', NULL, 'Public holiday'),
(34, 7, 'Islamic New Year\'s Day', '2027-06-26', NULL, 'Public holiday'),
(35, 7, 'Birth of the Prophet', '2027-09-04', NULL, 'Public holiday'),
(36, 7, 'Yemen Revolution Day', '2027-09-26', NULL, 'Public holiday'),
(37, 7, 'Yemen Independence Day', '2027-11-30', NULL, 'Public holiday'),
(38, 3, 'dsfdssdf', '2026-02-09', '17:55:00', NULL),
(39, 3, 'dfgdf', '2026-02-10', '16:44:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `user_id`, `title`, `content`, `updated_at`) VALUES
(8, 1, 'test', 'lorem', '2026-02-07 07:30:26');

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `due_date` datetime DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `due_date`, `is_completed`) VALUES
(11, 1, 'rrytyutyuty', NULL, 0),
(12, 1, 'ghfghfghfgh', '0000-00-00 00:00:00', 1),
(13, 1, 'ghf', '0000-00-00 00:00:00', 1),
(15, 1, 'hiii', '0000-00-00 00:00:00', 1),
(17, 1, 'gfhfhfg', '2026-02-25 00:00:00', 0),
(18, 1, 'gggg', NULL, 0),
(19, 1, 'aaaaa', '2026-02-08 00:00:00', 0),
(20, 3, 'hjfghjfghj', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `country_code` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `created_at`, `country_code`) VALUES
(1, 'raafat.2023@gmail.com', '$2y$10$VgfeCGpHBvlnj7XPVKDob.42dbl8GQ7xpwbwwE9tZiy./s19Zu2Be', '2026-02-04 14:58:30', ''),
(2, 'yemenaisolutions@outlook.com', '$2y$10$UpZwnDRlodxkJQqwjAfMCu2WQDtJ0HGNBDVDHuuoYGxS2MEZGqPvu', '2026-02-08 18:15:41', 'YE'),
(3, 'admin@hggzk.com', '$2y$10$9AG/7ZzAVAy7yU8eUys55usbYoy9i/QKEbSfnYTaznn.VZJz/r1eC', '2026-02-08 18:24:43', 'YE'),
(4, 'hggzkyemen@gmail.com', '$2y$10$yX2/3e2fm04Mgc3iAAfemedV5/k84KE7DoTBWZkHiU2.axpaQGqOq', '2026-02-08 18:29:59', 'YE'),
(5, 'hggzk@gmail.com', '$2y$10$UizrvJgieedjr9Sah.HYMui2NyPrSa9oOvI1B/YJBGf3sXRYlZBf.', '2026-02-08 18:31:08', 'YE'),
(6, 'hggszk@gmail.com', '$2y$10$jLzN4YZ3TxcSy6839FO4rOMZ3u03qMOcaxUuh1R/YqqXTglVJK5Ee', '2026-02-08 18:37:13', 'YE'),
(7, 'hggzssk@gmail.com', '$2y$10$zU7wX8c1BG.UjcHJhoqYyu9Fe/XtZuszJwgU9N9L32UgxGlLogdtu', '2026-02-08 18:38:57', 'YE');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
