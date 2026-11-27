CREATE TABLE IF NOT EXISTS `io_user_details` (
  `user_id` int NOT NULL,
  `role_detail` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `directory_status` varchar(50) DEFAULT NULL,
  `company_status` varchar(50) DEFAULT NULL,
  `business_address` text,
  `business_hours` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `io_id` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `io_user_details_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
