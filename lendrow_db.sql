-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql200.infinityfree.com
-- Generation Time: Dec 16, 2024 at 10:11 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_37691197_lendrow`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `borrowername` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `funded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime NOT NULL DEFAULT current_timestamp(),
  `lending_terms_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_loading`
--

CREATE TABLE `cash_loading` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `payment_account_name` varchar(255) NOT NULL,
  `receipt` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deducted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `wallet_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_details`
--

CREATE TABLE `financial_details` (
  `id` int(11) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `lendername` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `borrowername` varchar(255) NOT NULL,
  `interest` varchar(255) NOT NULL,
  `term` varchar(255) NOT NULL,
  `monthly` varchar(255) NOT NULL,
  `month_1` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_2` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_3` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_4` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_5` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_6` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_7` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_8` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_9` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_10` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_11` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `month_12` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `status` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `applications_id` int(11) DEFAULT NULL,
  `lending_terms_id` int(11) DEFAULT NULL,
  `lending_agreements_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lending_agreement`
--

CREATE TABLE `lending_agreement` (
  `id` int(11) NOT NULL,
  `borrowername` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `lendername` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `interest` varchar(255) NOT NULL,
  `term` varchar(255) NOT NULL,
  `monthly` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `applications_id` int(11) DEFAULT NULL,
  `lending_terms_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lending_terms`
--

CREATE TABLE `lending_terms` (
  `id` int(11) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `lendername` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `interest` varchar(255) NOT NULL,
  `term` varchar(255) NOT NULL,
  `monthly` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `users_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '@gmail.com',
  `pass` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `usertype` varchar(255) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `picture`, `firstname`, `middlename`, `lastname`, `username`, `mobile`, `email`, `pass`, `created_at`, `updated_at`, `usertype`) VALUES
(1, '../pictures/shattered.jpg', 'Kent Ryan', 'Gonzales', 'Entice', 'adminkent', '09100119667', '@gmail.com', '$2y$10$5vUCg//DQeeowrHg/mJssebprJKgIqRiW/6RgBgorWem.2.FmW0pW', '2024-11-12 19:10:13', '2024-11-21 21:29:47', 'admin');


-- --------------------------------------------------------

--
-- Table structure for table `virtual_wallet`
--

CREATE TABLE `virtual_wallet` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `balance` varchar(255) NOT NULL,
  `system_balance` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `virtual_wallet_history`
--

CREATE TABLE `virtual_wallet_history` (
  `id` int(11) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `virtual_wallet_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `balance` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `users_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_history`
--

CREATE TABLE `wallet_history` (
  `id` int(11) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `receiver` varchar(255) NOT NULL,
  `transfer_method` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `wallet_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lending_terms_id` (`lending_terms_id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `cash_loading`
--
ALTER TABLE `cash_loading`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_id` (`wallet_id`);

--
-- Indexes for table `financial_details`
--
ALTER TABLE `financial_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_id` (`applications_id`),
  ADD KEY `lending_terms_id` (`lending_terms_id`),
  ADD KEY `lending_agreements_id` (`lending_agreements_id`);

--
-- Indexes for table `lending_agreement`
--
ALTER TABLE `lending_agreement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_id` (`applications_id`),
  ADD KEY `lending_terms_id` (`lending_terms_id`);

--
-- Indexes for table `lending_terms`
--
ALTER TABLE `lending_terms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `virtual_wallet`
--
ALTER TABLE `virtual_wallet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `virtual_wallet_history`
--
ALTER TABLE `virtual_wallet_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `virtual_wallet_id` (`virtual_wallet_id`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`);

--
-- Indexes for table `wallet_history`
--
ALTER TABLE `wallet_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_id` (`wallet_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_loading`
--
ALTER TABLE `cash_loading`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_details`
--
ALTER TABLE `financial_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lending_agreement`
--
ALTER TABLE `lending_agreement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lending_terms`
--
ALTER TABLE `lending_terms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `virtual_wallet`
--
ALTER TABLE `virtual_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `virtual_wallet_history`
--
ALTER TABLE `virtual_wallet_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_history`
--
ALTER TABLE `wallet_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`lending_terms_id`) REFERENCES `lending_terms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cash_loading`
--
ALTER TABLE `cash_loading`
  ADD CONSTRAINT `cash_loading_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_details`
--
ALTER TABLE `financial_details`
  ADD CONSTRAINT `financial_details_ibfk_1` FOREIGN KEY (`applications_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_details_ibfk_2` FOREIGN KEY (`lending_terms_id`) REFERENCES `lending_terms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_details_ibfk_3` FOREIGN KEY (`lending_agreements_id`) REFERENCES `lending_agreement` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lending_agreement`
--
ALTER TABLE `lending_agreement`
  ADD CONSTRAINT `lending_agreement_ibfk_1` FOREIGN KEY (`applications_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lending_agreement_ibfk_2` FOREIGN KEY (`lending_terms_id`) REFERENCES `lending_terms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lending_terms`
--
ALTER TABLE `lending_terms`
  ADD CONSTRAINT `lending_terms_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `virtual_wallet_history`
--
ALTER TABLE `virtual_wallet_history`
  ADD CONSTRAINT `virtual_wallet_history_ibfk_1` FOREIGN KEY (`virtual_wallet_id`) REFERENCES `virtual_wallet` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wallet_history`
--
ALTER TABLE `wallet_history`
  ADD CONSTRAINT `wallet_history_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_history_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `wallet` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
