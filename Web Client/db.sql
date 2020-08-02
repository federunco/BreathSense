-- phpMyAdmin SQL Dump
-- version 4.9.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 02, 2020 at 08:40 PM
-- Server version: 5.7.26
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `breathsense`
--

-- --------------------------------------------------------

--
-- Table structure for table `pazienti`
--

CREATE TABLE `pazienti` (
  `ID` int(11) NOT NULL,
  `NOME` text NOT NULL,
  `DISPOSITIVO` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pazienti`
--

INSERT INTO `pazienti` (`ID`, `NOME`, `DISPOSITIVO`) VALUES
(5, 'Test Paziente (ONBOARD MAX30100)', '3c71bf423118');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `USERNAME` text NOT NULL,
  `PASSWORD` text NOT NULL,
  `ROLE` text NOT NULL,
  `FULLNAME` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `USERNAME`, `PASSWORD`, `ROLE`, `FULLNAME`) VALUES
(1, 'federunco', '$2y$10$', 'administrator', 'Federico Runco');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pazienti`
--
ALTER TABLE `pazienti`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pazienti`
--
ALTER TABLE `pazienti`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
