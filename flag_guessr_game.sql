-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 03 jul 2025 om 21:10
-- Serverversie: 10.4.32-MariaDB
-- PHP-versie: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flag_guessr_game`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `flags`
--

CREATE TABLE `flags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `flags`
--

INSERT INTO `flags` (`id`, `name`, `image_path`) VALUES
(11, 'The Netherlands', 'flags/the_netherlands.webp'),
(12, 'Germany', 'flags/germany.webp'),
(13, 'France', 'flags/france.webp'),
(14, 'Spain', 'flags/spain.webp'),
(15, 'Italy', 'flags/italy.webp'),
(16, 'Belgium', 'flags/belgium.webp'),
(17, 'Luxembourg', 'flags/luxembourg.webp'),
(18, 'Switzerland', 'flags/switzerland.webp'),
(19, 'Austria', 'flags/austria.webp'),
(20, 'United Kingdom', 'flags/united_kingdom.webp');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `flags`
--
ALTER TABLE `flags`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `flags`
--
ALTER TABLE `flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
