-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 19, 2025 at 11:36 PM
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
-- Database: `Ritual`
--

-- --------------------------------------------------------

--
-- Table structure for table `ImagesPost`
--

CREATE TABLE `ImagesPost` (
  `idPost` bigint(20) UNSIGNED NOT NULL,
  `Name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  `route` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ImagesPost`
--

INSERT INTO `ImagesPost` (`idPost`, `Name`, `order`, `route`) VALUES
(1, 'header.jpg', 1, ''),
(2, 'screen1.png', 1, ''),
(2, 'screen2.png', 2, ''),
(4, 'notes.png', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `Likes`
--

CREATE TABLE `Likes` (
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `post` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Likes`
--

INSERT INTO `Likes` (`idUser`, `post`, `date`, `time`) VALUES
(2, 2, '2025-10-17', '15:16:46'),
(2, 4, '2025-10-17', '15:16:46'),
(3, 1, '2025-10-17', '15:16:46'),
(3, 5, '2025-10-17', '15:16:46'),
(4, 1, '2025-10-17', '15:16:46'),
(4, 4, '2025-10-17', '15:16:46'),
(5, 2, '2025-10-17', '15:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `Password`
--

CREATE TABLE `Password` (
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Password`
--

INSERT INTO `Password` (`idUser`, `hash`) VALUES
(1, '3372089fdaf1b8a60cc6790b01909bb6d0b6e6683a90645dee81bf451cf6ce68'),
(2, '6fec2a9601d5b3581c94f2150fc07fa3d6e45808079428354b868e412b76e6bb'),
(3, 'df733656293a19c54f69093ba916f0a1a2a3c151fc95c13f3a794c2631eeb3a6'),
(4, 'aa9d1bfeff9b2e5e988382d2d407998802bcc6383699b8ca7f56c402f11815bf'),
(5, '59ed1b0da7d4af7549a74f5a091d47459fb6e886f48a10a61c1bff5e9e2a7f19'),
(6, '$2y$10$D2hOor7AqAuYZ7ADIcXvcOzm3vaXPqUTAopIZw66xf3LwxYAWpQ3a'),
(7, '$2y$10$u1c9OAeuJdcuHmiVDUwoIuZfrgXYl.p3Xumv/W8IVuiQHQPgWF8IG'),
(8, '$2y$10$0XTvbvTwdM8rsHHfZlm/POLnItc.VYO/hvU6Nd2Zw4fGQDhJpilj.'),
(10, '$2y$10$XpSJS2.D53nbbdkNII9HOeP0RmdIaoTiCK.Ze84lpEXeLdcHbzGFK'),
(11, '$2y$10$yvIeT1LM5m0UyuVijFUv7.LAu0I2g2PgFqzYRCtOVfhQdNrXll2Y2'),
(14, '$2y$10$vZd0IPi0Wds4Kk/z68LgcutBfib31/ixfOtUH3UVRYYcNrTzdpy..'),
(15, '$2y$10$pzkaEIEOlnkiZ//FIZnvzu0bTw66VKh1dVUve8QGMK8h1eu1nTIky'),
(16, '$2y$10$O/D6raHxTxt7Cz4dcSJOIedImQQH3odqdZqORQDLpr4mzZh9XaS9y'),
(17, '$2y$10$/Ci7dH0SLQ3NET85U8ZJWunE9dhE.1XaEJLaMZvf2lAVpMQTI0sXu'),
(18, '$2y$10$UDfNEn8jHsb5SgBT2iNQj./0niWkgtnlBr4o/A7C9xDIiTrQCTrpO'),
(19, '$2y$10$2evhCSzzzOgkB4vyFcVQ.eSo3tsZZEatTusEGctPGhDfOcDGyxN2i'),
(20, '$2y$10$ZyrejxuRuYpArdrKMLVzMOy9otQf7RucwaqXQmfUSQnzXzjJHWVr2'),
(21, '$2y$10$3Tqww7ytk6EmO.ZM5Rhtw.pueR5eITpvVYV7nCQ4TwkGR4f.hGDaG'),
(22, '$2y$10$zIzCwtJfwpsx8mhtOCRlcOqxFYNzuubZlT9ZP.WOVDYURvKvLmqGO');

-- --------------------------------------------------------

--
-- Table structure for table `Post`
--

CREATE TABLE `Post` (
  `idPost` bigint(20) UNSIGNED NOT NULL,
  `idBelogingPost` bigint(20) UNSIGNED DEFAULT NULL,
  `idUserOwner` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Post`
--

INSERT INTO `Post` (`idPost`, `idBelogingPost`, `idUserOwner`, `content`, `date`) VALUES
(1, NULL, 2, '¡Hola, mundo! Probando el timeline de la app.', '2025-10-17 15:16:46'),
(2, NULL, 3, 'Hoy deployé una API. Nada se rompió (creo).', '2025-10-17 15:16:46'),
(3, 1, 4, 'Bienvenido al feed 😄 ¿Qué estás construyendo?', '2025-10-17 15:16:46'),
(4, NULL, 5, 'Estudiando pruebas A/B y p-values. Se aceptan tips.', '2025-10-17 15:16:46'),
(5, 2, 2, '¡Felicitaciones! ¿Usaste CI/CD o deploy manual?', '2025-10-17 15:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `Profile`
--

CREATE TABLE `Profile` (
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `Descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Profile`
--

INSERT INTO `Profile` (`idUser`, `Descripcion`) VALUES
(1, 'Facultad de Ciencias y Tecnología - Universidad Autónoma de Entre Ríos'),
(2, 'Docente de programación y fan de C++'),
(3, 'Desarrolladora fullstack, café y TypeScript'),
(4, 'Gamer, QA amateur y dev en formación'),
(5, 'Estudiante de datos, ama la estadística'),
(6, ''),
(7, ''),
(8, ''),
(10, ''),
(11, ''),
(14, ''),
(15, ''),
(16, ''),
(17, ''),
(18, ''),
(19, ''),
(20, ''),
(21, ''),
(22, '');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `idUser` bigint(20) UNSIGNED NOT NULL,
  `userTag` varchar(30) NOT NULL,
  `username` varchar(100) NOT NULL,
  `profileImageRoute` varchar(1024) DEFAULT NULL,
  `dateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`idUser`, `userTag`, `username`, `profileImageRoute`, `dateCreated`) VALUES
(1, 'fcytuader', 'fcytuader', 'Resources/profilePictures/uader.png', '2025-10-17 15:16:46'),
(2, 'eljuampi', 'eljuampi', NULL, '2025-10-17 15:16:46'),
(3, 'mariapower', 'mariapower', NULL, '2025-10-17 15:16:46'),
(4, 'maxi', 'maxi', NULL, '2025-10-17 15:16:46'),
(5, 'anita', 'anita', NULL, '2025-10-17 15:16:46'),
(6, 'Valentino Pettinato', 'Valentino Pettinato', NULL, '2025-10-19 00:39:19'),
(7, 'tomi', 'tomi', NULL, '2025-10-19 00:39:19'),
(8, 'marianocaminos', 'marianocaminos', NULL, '2025-10-19 00:39:19'),
(10, 'chinoo_vg', 'chinoo_vg', NULL, '2025-10-19 00:39:19'),
(11, 'tomitomi', 'tomitomi', 'Resources/profilePictures/Ritual.png', '2025-10-19 00:39:19'),
(14, 'prueba', 'prueba', NULL, '2025-10-19 00:39:19'),
(15, 'usuarioejemplo', 'usuarioejemplo', NULL, '2025-10-19 00:39:19'),
(16, 'PelaAprobanosPorfa', 'PelaAprobanosPorfa', 'Resources/profilePictures/VinDiesel.webp', '2025-10-19 00:39:19'),
(17, 'tomitomitomi', 'tomitomitomi', NULL, '2025-10-19 00:39:19'),
(18, 'daniel', 'daniel', NULL, '2025-10-19 00:39:19'),
(19, 'tomitomi1', 'tomitomi1', NULL, '2025-10-19 18:22:20'),
(20, 'tomisch', 'tomisch', NULL, '2025-10-19 18:31:38'),
(21, 'tomitest', 'tomitest', NULL, '2025-10-19 18:32:52'),
(22, 'tomites', 'tomites', NULL, '2025-10-19 18:35:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ImagesPost`
--
ALTER TABLE `ImagesPost`
  ADD PRIMARY KEY (`idPost`,`Name`),
  ADD KEY `idx_imagespost_post` (`idPost`);

--
-- Indexes for table `Likes`
--
ALTER TABLE `Likes`
  ADD PRIMARY KEY (`idUser`,`post`),
  ADD KEY `idx_likes_post` (`post`);

--
-- Indexes for table `Password`
--
ALTER TABLE `Password`
  ADD PRIMARY KEY (`idUser`);

--
-- Indexes for table `Post`
--
ALTER TABLE `Post`
  ADD PRIMARY KEY (`idPost`),
  ADD KEY `idx_post_belonging` (`idBelogingPost`),
  ADD KEY `idx_post_owner` (`idUserOwner`);

--
-- Indexes for table `Profile`
--
ALTER TABLE `Profile`
  ADD PRIMARY KEY (`idUser`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `username_UNIQUE` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Post`
--
ALTER TABLE `Post`
  MODIFY `idPost` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `idUser` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ImagesPost`
--
ALTER TABLE `ImagesPost`
  ADD CONSTRAINT `fk_imagespost_post` FOREIGN KEY (`idPost`) REFERENCES `Post` (`idPost`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Likes`
--
ALTER TABLE `Likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post`) REFERENCES `Post` (`idPost`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`idUser`) REFERENCES `User` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Password`
--
ALTER TABLE `Password`
  ADD CONSTRAINT `fk_password_user` FOREIGN KEY (`idUser`) REFERENCES `User` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Post`
--
ALTER TABLE `Post`
  ADD CONSTRAINT `fk_post_parent` FOREIGN KEY (`idBelogingPost`) REFERENCES `Post` (`idPost`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`idUserOwner`) REFERENCES `User` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Profile`
--
ALTER TABLE `Profile`
  ADD CONSTRAINT `fk_profile_user` FOREIGN KEY (`idUser`) REFERENCES `User` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
