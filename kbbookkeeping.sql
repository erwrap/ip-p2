-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 08:58 PM
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
-- Database: `kbbookkeeping`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `doc_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `category` enum('bank_statement','invoice','tax_form','other') DEFAULT 'other',
  `file_size` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`doc_id`, `user_id`, `filename`, `filepath`, `category`, `file_size`, `admin_notes`, `upload_date`) VALUES
(1, 2, 'BankStatement_Feb2026.pdf', '/uploads/2/BankStatement_Feb2026.pdf', 'bank_statement', 1200000, 'Reviewed and matched.', '2026-04-05 21:19:52'),
(2, 2, 'Invoice_March.xlsx', '/uploads/2/Invoice_March.xlsx', 'invoice', 400000, NULL, '2026-04-05 21:19:52'),
(3, 3, 'TaxForm_2025.pdf', '/uploads/3/TaxForm_2025.pdf', 'tax_form', 980000, 'Pending review.', '2026-04-05 21:19:52');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `user_id`, `description`, `amount`, `due_date`, `status`, `created_at`) VALUES
(1, 2, 'Monthly Bookkeeping – January 2026', 175.00, '2026-02-01', 'paid', '2026-04-05 21:20:00'),
(2, 2, 'Monthly Bookkeeping – February 2026', 175.00, '2026-03-01', 'paid', '2026-04-05 21:20:00'),
(3, 2, 'Monthly Bookkeeping – March 2026', 175.00, '2026-04-01', 'unpaid', '2026-04-05 21:20:00'),
(4, 2, 'Clean Up Service – February 2026', 350.00, '2026-02-15', 'paid', '2026-04-05 21:20:00'),
(5, 3, 'Payroll Setup – March 2026', 200.00, '2026-04-01', 'unpaid', '2026-04-05 21:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `content`, `read_status`, `sent_at`) VALUES
(1, 1, 2, 'Hi Jane! Your March bookkeeping is now in progress. Please upload your latest bank statement.', 1, '2026-04-05 21:20:06'),
(2, 2, 1, 'Thanks! I just uploaded it to the Documents section.', 1, '2026-04-05 21:20:06'),
(3, 1, 2, 'Got it, thank you! We will have your report ready by the 20th.', 0, '2026-04-05 21:20:06'),
(4, 1, 3, 'Hi Bob! We received your payroll request. Can you confirm the number of employees?', 1, '2026-04-05 21:20:06'),
(5, 3, 1, 'Hi! We have 4 full-time employees. Let me know what else you need.', 0, '2026-04-05 21:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `price`) VALUES
(1, 'Monthly Bookkeeping', 'Monthly income/expense tracking, bank reconciliation, and financial reports.', 175.00),
(2, 'Payroll', 'Management of payroll processing and payroll tax reporting.', 200.00),
(3, 'Accounts Receivable', 'Invoice clients, receive payments, and report overdue accounts.', 150.00),
(4, 'Accounts Payable', 'Pay bills directly through your bank account from scanned invoices.', 150.00),
(5, 'Clean Up', 'Catch up on past bookkeeping entries for clean financials.', 350.00),
(6, 'Quarterly Review', 'Review your own bookkeeping quarterly to ensure accuracy.', 125.00);

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('normal','urgent') DEFAULT 'normal',
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`request_id`, `user_id`, `service_id`, `notes`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Please start with March 2026 transactions.', 'normal', 'in_progress', '2026-04-05 21:19:45', '2026-04-05 21:19:45'),
(2, 2, 5, 'I am behind about 3 months on bookkeeping entries.', 'urgent', 'completed', '2026-04-05 21:19:45', '2026-04-05 21:19:45'),
(3, 3, 2, 'Need payroll set up for 4 employees.', 'normal', 'pending', '2026-04-05 21:19:45', '2026-04-05 21:19:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(1, 'Kari Brown', 'admin@kbbookkeeping.com', '$2y$10$examplehashedpassword1', '(770) 815-1820', 'admin', '2026-04-05 21:19:22'),
(2, 'Jane Smith', 'jane@example.com', '$2y$10$examplehashedpassword2', '(770) 555-1234', 'client', '2026-04-05 21:19:22'),
(3, 'Bob Turner', 'bob@acme.com', '$2y$10$examplehashedpassword3', '(404) 555-9876', 'client', '2026-04-05 21:19:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
