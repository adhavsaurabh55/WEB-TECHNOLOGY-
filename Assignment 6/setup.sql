-- Run this in phpMyAdmin (http://localhost/phpmyadmin)
-- Connect as root (no password), select 'SQL' tab, paste & execute

CREATE DATABASE IF NOT EXISTS `employee_db`;

USE `employee_db`;

CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` VARCHAR(20) PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `gender` VARCHAR(10),
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `age` INT,
  `pincode` VARCHAR(10),
  `state` VARCHAR(50),
  `country` VARCHAR(50),
  `department` VARCHAR(50),
  `role` VARCHAR(50),
  `salary` DECIMAL(10,2),
  `dob` DATE,
  `address` TEXT
);

-- Optional: Insert sample data
INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `gender`, `email`, `phone`, `age`, `pincode`, `state`, `country`, `department`, `role`, `salary`, `dob`, `address`) VALUES
('EMP001', 'John', 'Doe', 'Male', 'john@example.com', '1234567890', 30, '400001', 'Maharashtra', 'India', 'IT', 'Developer', 50000.00, '1994-05-15', '123 Main St, Mumbai');

