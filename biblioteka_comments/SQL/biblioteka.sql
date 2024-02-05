-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2024 at 11:18 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `biblioteka`
--

-- --------------------------------------------------------

--
-- Table structure for table `administratori`
--

CREATE TABLE `administratori` (
  `id` int(11) NOT NULL,
  `korisnicko_ime` varchar(255) NOT NULL,
  `lozinka` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administratori`
--

INSERT INTO `administratori` (`id`, `korisnicko_ime`, `lozinka`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3'),
(2, 'admin1', '21232f297a57a5a743894a0e4a801fc3');

-- --------------------------------------------------------

--
-- Table structure for table `clanarine`
--

CREATE TABLE `clanarine` (
  `id` int(11) NOT NULL,
  `trajanje` int(11) NOT NULL,
  `cena` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clanarine`
--

INSERT INTO `clanarine` (`id`, `trajanje`, `cena`) VALUES
(1, 1, 60),
(2, 3, 160),
(3, 6, 300),
(4, 12, 500);

-- --------------------------------------------------------

--
-- Table structure for table `knjige`
--

CREATE TABLE `knjige` (
  `id` int(11) NOT NULL,
  `naziv` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `godina` int(11) NOT NULL,
  `broj_primeraka` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `knjige`
--

INSERT INTO `knjige` (`id`, `naziv`, `autor`, `godina`, `broj_primeraka`) VALUES
(1, 'Knjiga 1', 'Autor 1', 2024, 8),
(2, 'Knjiga 2', 'Autor 1', 2010, 0),
(3, 'Knjiga 3', 'Autor 2', 1950, 12),
(4, 'Bele noći', 'Fjodor Dostojevski', 1848, 5),
(5, 'Knjiga sa dugackim nazivom 123', 'Autor sa dugackim nazivom 123', 2020, 1);

-- --------------------------------------------------------

--
-- Table structure for table `korisnici`
--

CREATE TABLE `korisnici` (
  `id` int(11) NOT NULL,
  `korisnicko_ime` varchar(255) NOT NULL,
  `lozinka` varchar(255) NOT NULL,
  `ime` varchar(255) DEFAULT NULL,
  `telefon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `korisnici`
--

INSERT INTO `korisnici` (`id`, `korisnicko_ime`, `lozinka`, `ime`, `telefon`) VALUES
(3, 'test', '202cb962ac59075b964b07152d234b70', 'Test Test', '06412345678'),
(4, 'test1', '202cb962ac59075b964b07152d234b70', 'Test Test', '123'),
(6, '1221', '698d51a19d8a121ce581499d7b701668', '12', '1212'),
(7, '1212121', '8ce87b8ec346ff4c80635f667d1592ae', '121313', '121312321'),
(8, 'korisnik', '202cb962ac59075b964b07152d234b70', 'Korisnik 1', '123'),
(9, '1231312', '220466675e31b9d20c051d5e57974150', 'korisnik', '312312312'),
(10, 'korisnik1', '202cb962ac59075b964b07152d234b70', 'Korisnik 2', '123'),
(11, 'korisnik2', '202cb962ac59075b964b07152d234b70', 'Ime', '123');

-- --------------------------------------------------------

--
-- Table structure for table `korisnici_clanarine`
--

CREATE TABLE `korisnici_clanarine` (
  `id` int(11) NOT NULL,
  `korisnici_id` int(11) NOT NULL,
  `clanarine_id` int(11) NOT NULL,
  `vazi_do` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `korisnici_clanarine`
--

INSERT INTO `korisnici_clanarine` (`id`, `korisnici_id`, `clanarine_id`, `vazi_do`) VALUES
(4, 8, 4, '2023-01-21'),
(7, 8, 4, '2025-01-24'),
(8, 3, 2, '2024-04-27'),
(9, 7, 3, '2024-07-29'),
(10, 10, 1, '2024-03-04'),
(11, 11, 2, '2024-05-04');

-- --------------------------------------------------------

--
-- Table structure for table `obavestenja`
--

CREATE TABLE `obavestenja` (
  `id` int(11) NOT NULL,
  `preuzimanje_id` int(11) NOT NULL,
  `aktivno` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `obavestenja`
--

INSERT INTO `obavestenja` (`id`, `preuzimanje_id`, `aktivno`) VALUES
(29, 10, 0),
(30, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `preuzimanje`
--

CREATE TABLE `preuzimanje` (
  `id` int(11) NOT NULL,
  `korisnici_id` int(11) NOT NULL,
  `knjige_id` int(11) NOT NULL,
  `rok` date NOT NULL,
  `vraceno` tinyint(1) NOT NULL DEFAULT 0,
  `datum_uzeto` date NOT NULL DEFAULT curdate(),
  `datum_vraceno` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preuzimanje`
--

INSERT INTO `preuzimanje` (`id`, `korisnici_id`, `knjige_id`, `rok`, `vraceno`, `datum_uzeto`, `datum_vraceno`) VALUES
(5, 8, 3, '2024-02-09', 1, '2024-01-26', '2024-01-26'),
(6, 8, 5, '2024-02-09', 1, '2024-01-26', '2024-01-26'),
(7, 8, 5, '2024-02-09', 1, '2024-01-26', '2024-01-26'),
(8, 8, 4, '2024-02-09', 1, '2024-01-26', '2024-01-26'),
(9, 8, 5, '2024-02-09', 1, '2024-01-26', '2024-01-26'),
(10, 8, 3, '2024-01-28', 1, '2024-01-26', '2024-01-29'),
(11, 8, 3, '2024-02-12', 1, '2024-01-29', '2024-01-29'),
(12, 8, 3, '2024-02-06', 1, '2024-01-21', '2024-02-04'),
(13, 10, 1, '2024-02-18', 0, '2024-02-04', NULL),
(14, 11, 3, '2024-02-18', 1, '2024-02-04', '2024-02-04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administratori`
--
ALTER TABLE `administratori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `korisnicko_ime` (`korisnicko_ime`);

--
-- Indexes for table `clanarine`
--
ALTER TABLE `clanarine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `knjige`
--
ALTER TABLE `knjige`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `korisnici`
--
ALTER TABLE `korisnici`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `korisnicko_ime` (`korisnicko_ime`);

--
-- Indexes for table `korisnici_clanarine`
--
ALTER TABLE `korisnici_clanarine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `korisnici_clanarine_clanarine` (`clanarine_id`),
  ADD KEY `korisnici_clanarine_korisnici` (`korisnici_id`);

--
-- Indexes for table `obavestenja`
--
ALTER TABLE `obavestenja`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `U_preuzimanje_id` (`preuzimanje_id`);

--
-- Indexes for table `preuzimanje`
--
ALTER TABLE `preuzimanje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `preuzimanje_korisnici` (`korisnici_id`),
  ADD KEY `preuzimanje_knjige` (`knjige_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administratori`
--
ALTER TABLE `administratori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clanarine`
--
ALTER TABLE `clanarine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `knjige`
--
ALTER TABLE `knjige`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `korisnici`
--
ALTER TABLE `korisnici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `korisnici_clanarine`
--
ALTER TABLE `korisnici_clanarine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `obavestenja`
--
ALTER TABLE `obavestenja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `preuzimanje`
--
ALTER TABLE `preuzimanje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `korisnici_clanarine`
--
ALTER TABLE `korisnici_clanarine`
  ADD CONSTRAINT `korisnici_clanarine_clanarine` FOREIGN KEY (`clanarine_id`) REFERENCES `clanarine` (`id`),
  ADD CONSTRAINT `korisnici_clanarine_korisnici` FOREIGN KEY (`korisnici_id`) REFERENCES `korisnici` (`id`);

--
-- Constraints for table `obavestenja`
--
ALTER TABLE `obavestenja`
  ADD CONSTRAINT `FK_obavestenja_preuzimanje` FOREIGN KEY (`preuzimanje_id`) REFERENCES `preuzimanje` (`id`);

--
-- Constraints for table `preuzimanje`
--
ALTER TABLE `preuzimanje`
  ADD CONSTRAINT `preuzimanje_knjige` FOREIGN KEY (`knjige_id`) REFERENCES `knjige` (`id`),
  ADD CONSTRAINT `preuzimanje_korisnici` FOREIGN KEY (`korisnici_id`) REFERENCES `korisnici` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
