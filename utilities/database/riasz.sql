-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 06, 2026 at 09:12 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `riasz`
--

-- --------------------------------------------------------

--
-- Table structure for table `accomplishmentlogs`
--

CREATE TABLE `accomplishmentlogs` (
  `acclog_id` int NOT NULL,
  `accomplishment_id` int DEFAULT NULL,
  `acclog_text` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accomplishmentreports`
--

CREATE TABLE `accomplishmentreports` (
  `accreport_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `accrep_month` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accrep_position` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accrep_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accrep_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accrep_date_uploaded` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessmentcontents`
--

CREATE TABLE `assessmentcontents` (
  `assessment_content_id` int NOT NULL,
  `assessment_id` int DEFAULT NULL,
  `ass_content_question` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessmentfeedback`
--

CREATE TABLE `assessmentfeedback` (
  `afeedback_content_id` int NOT NULL,
  `afeedback_question` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessmentgrades`
--

CREATE TABLE `assessmentgrades` (
  `assessment_grade_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `assessment_content_id` int DEFAULT NULL,
  `assessment_grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int NOT NULL,
  `io_id` int DEFAULT NULL,
  `ass_version_number` int DEFAULT NULL,
  `ass_version_comments` text COLLATE utf8mb4_general_ci,
  `ass_date_created` datetime DEFAULT NULL,
  `ass_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendancelogs`
--

CREATE TABLE `attendancelogs` (
  `attlog_id` int NOT NULL,
  `attrep_id` int DEFAULT NULL,
  `attlog_date` date DEFAULT NULL,
  `attlog_time_in` time DEFAULT NULL,
  `attlog_time_out` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendancereports`
--

CREATE TABLE `attendancereports` (
  `attrep_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `attrep_month` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attrep_position` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attrep_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attrep_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attrep_date_uploaded` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int NOT NULL,
  `company_date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_website` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_address` text COLLATE utf8mb4_general_ci,
  `intern_allowance` decimal(15,2) DEFAULT NULL,
  `partnership_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `max_internslots` int DEFAULT NULL,
  `revenue_growth` decimal(15,2) DEFAULT NULL,
  `profit_margins` decimal(15,2) DEFAULT NULL,
  `roi` decimal(15,2) DEFAULT NULL,
  `roa` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courseoutcomes`
--

CREATE TABLE `courseoutcomes` (
  `co_id` int NOT NULL,
  `io_id` int DEFAULT NULL,
  `co_version_number` int DEFAULT NULL,
  `co_version_comments` text COLLATE utf8mb4_general_ci,
  `co_date_created` datetime DEFAULT NULL,
  `co_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courseoutcomesassessments`
--

CREATE TABLE `courseoutcomesassessments` (
  `coassessment_id` int NOT NULL,
  `co_content_id` int DEFAULT NULL,
  `assessment_content_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courseoutcomescontents`
--

CREATE TABLE `courseoutcomescontents` (
  `co_content_id` int NOT NULL,
  `co_id` int DEFAULT NULL,
  `co_content_text` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courseoutcomesstudentoutcomes`
--

CREATE TABLE `courseoutcomesstudentoutcomes` (
  `coso_id` int NOT NULL,
  `so_content_id` int DEFAULT NULL,
  `co_content_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int NOT NULL,
  `department_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventrecipients`
--

CREATE TABLE `eventrecipients` (
  `event_recipient_id` int NOT NULL,
  `event_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int NOT NULL,
  `io_id` int DEFAULT NULL,
  `event_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `event_date` date DEFAULT NULL,
  `event_year` int DEFAULT NULL,
  `event_intern_year` int DEFAULT NULL,
  `event_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `event_description` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executivedirectors`
--

CREATE TABLE `executivedirectors` (
  `exd_id` int NOT NULL,
  `schooluser_id` int DEFAULT NULL,
  `school_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `executivedirectors`
--

INSERT INTO `executivedirectors` (`exd_id`, `schooluser_id`, `school_id`) VALUES
(8, 83, 3);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `afeedback_content_id` int DEFAULT NULL,
  `feedback_answer` text COLLATE utf8mb4_general_ci,
  `feedback_yesno` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finalnarratives`
--

CREATE TABLE `finalnarratives` (
  `finalnarrative_id` int NOT NULL,
  `finalreport_id` int DEFAULT NULL,
  `fp_id` int DEFAULT NULL,
  `finalnarrative_answer` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finalpapercontents`
--

CREATE TABLE `finalpapercontents` (
  `fp_id` int NOT NULL,
  `fp_content_question` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finalpapers`
--

CREATE TABLE `finalpapers` (
  `fp_id` int NOT NULL,
  `io_id` int DEFAULT NULL,
  `fp_version_number` int DEFAULT NULL,
  `fp_version_comments` text COLLATE utf8mb4_general_ci,
  `fp_date_created` datetime DEFAULT NULL,
  `fp_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fp_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finalreports`
--

CREATE TABLE `finalreports` (
  `finalreport_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `finalreport_specialization` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finalreport_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finalreport_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finalreport_date_uploaded` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interns`
--

CREATE TABLE `interns` (
  `intern_id` int NOT NULL,
  `schooluser_id` int DEFAULT NULL,
  `program_id` int DEFAULT NULL,
  `intern_birthdate` date DEFAULT NULL,
  `intern_gender` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `intern_city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `intern_province_or_state` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `intern_postal_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `intern_country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internshipgrades`
--

CREATE TABLE `internshipgrades` (
  `internshipgrade_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `supervisor_grade` decimal(5,2) DEFAULT NULL,
  `supervisor_date_graded` datetime DEFAULT NULL,
  `io_grade` decimal(5,2) DEFAULT NULL,
  `io_date_graded` datetime DEFAULT NULL,
  `total_grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internshipofficers`
--

CREATE TABLE `internshipofficers` (
  `io_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` int NOT NULL,
  `intern_id` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `io_id` int DEFAULT NULL,
  `internship_year` int DEFAULT NULL,
  `internship_date_started` date DEFAULT NULL,
  `internship_date_ended` date DEFAULT NULL,
  `internship_job_role` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mapping`
--

CREATE TABLE `mapping` (
  `mapping_id` int NOT NULL,
  `coassessment_id` int DEFAULT NULL,
  `coso_id` int DEFAULT NULL,
  `map_version_number` int DEFAULT NULL,
  `map_version_comments` text COLLATE utf8mb4_general_ci,
  `map_date_created` datetime DEFAULT NULL,
  `map_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mappingvalidations`
--

CREATE TABLE `mappingvalidations` (
  `mappingvalidations_id` int NOT NULL,
  `so_id` int DEFAULT NULL,
  `pd_id` int DEFAULT NULL,
  `pd_is_validated` tinyint(1) DEFAULT NULL,
  `pd_validated_date` datetime DEFAULT NULL,
  `exd_id` int DEFAULT NULL,
  `exd_is_validated` tinyint(1) DEFAULT NULL,
  `exd_validated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificationrecipients`
--

CREATE TABLE `notificationrecipients` (
  `notifrecip_id` int NOT NULL,
  `notification_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `notifrecip_is_read` tinyint(1) DEFAULT '0',
  `notifrecip_is_resolved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `notif_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notif_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notif_message` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onetimepasswords`
--

CREATE TABLE `onetimepasswords` (
  `otp_id` int NOT NULL,
  `schooluser_id` int DEFAULT NULL,
  `otp_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `otp_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `otp_date_expiry` datetime DEFAULT NULL,
  `otp_attempts` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passwordresets`
--

CREATE TABLE `passwordresets` (
  `passreset_id` int NOT NULL,
  `schooluser_id` int DEFAULT NULL,
  `passreset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `passreset_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `passreset_date_expiry` datetime DEFAULT NULL,
  `passreset_is_used` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passwordresets`
--

INSERT INTO `passwordresets` (`passreset_id`, `schooluser_id`, `passreset_token`, `passreset_date_created`, `passreset_date_expiry`, `passreset_is_used`) VALUES
(18, 1, 'fbde5f5e78c4a922904e73a85285608f1bb688810be034741909dba0c2f0f866', '2026-05-06 09:04:42', '2026-05-06 17:19:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `programdirectors`
--

CREATE TABLE `programdirectors` (
  `pd_id` int NOT NULL,
  `schooluser_id` int DEFAULT NULL,
  `program_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programdirectors`
--

INSERT INTO `programdirectors` (`pd_id`, `schooluser_id`, `program_id`) VALUES
(2, 84, 6),
(3, 85, 7);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int NOT NULL,
  `program_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `school_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `school_id`) VALUES
(6, 'Computer Engineering', 3),
(7, 'Electrical Engineering', 3),
(8, 'Civil Engineering', 3);

-- --------------------------------------------------------

--
-- Table structure for table `reportformats`
--

CREATE TABLE `reportformats` (
  `reportformat_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reportf_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `report_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_description` text COLLATE utf8mb4_general_ci,
  `report_template` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportparameters`
--

CREATE TABLE `reportparameters` (
  `reportparam_id` int NOT NULL,
  `reportformat_id` int DEFAULT NULL,
  `report_parameter` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int NOT NULL,
  `report_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_year` int DEFAULT NULL,
  `report_intern_year` int DEFAULT NULL,
  `report_filename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `report_remarks_text` text COLLATE utf8mb4_general_ci,
  `io_id` int DEFAULT NULL,
  `report_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int NOT NULL,
  `school_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_name`) VALUES
(14, 'Architecture'),
(3, 'Engineering'),
(13, 'Management');

-- --------------------------------------------------------

--
-- Table structure for table `schoolusers`
--

CREATE TABLE `schoolusers` (
  `schooluser_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `schooluser_given_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `schooluser_password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schoolusers`
--

INSERT INTO `schoolusers` (`schooluser_id`, `user_id`, `schooluser_given_id`, `schooluser_password`) VALUES
(1, 1, 'A001', '$2y$10$XLZN8DuRQkaQqjgAYILRneHPKxWVpGRhN4sCMVo4DoJsvcRQw2omG'),
(82, 105, 'inteoffi1', '$2y$10$P9VVfi7ltIG78hlI0VhQBunnH3Cb8TGv/T9B3/eFZfXxiDqOfVnwu'),
(83, 106, 'engiexe1', '$2y$10$VdROczK2HfdTIKjWcdEhC.oOnc7EflU2brOP/BmXSh11WMUW5Z70S'),
(84, 107, 'cpeprog1', '$2y$10$z5j7Skvqf.A3VA79wPDlQenyH1javhq7oMGiWLbssadtLOAwcWnWq'),
(85, 108, 'eceprog1', '$2y$10$gu8sasqfrgyiCwGeDm0cqegmb1j5brFTl5ttf7u0d92Ko00RdkTIe');

-- --------------------------------------------------------

--
-- Table structure for table `sendassessments`
--

CREATE TABLE `sendassessments` (
  `sendassessment_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `sendass_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sendass_date` date DEFAULT NULL,
  `sendass_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `status_id` int NOT NULL,
  `internship_id` int DEFAULT NULL,
  `status_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_text` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_remarks` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stringliterals`
--

CREATE TABLE `stringliterals` (
  `string_literal_id` int NOT NULL,
  `string_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `string_content` text COLLATE utf8mb4_general_ci,
  `string_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `schooluser_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studentoutcomes`
--

CREATE TABLE `studentoutcomes` (
  `so_id` int NOT NULL,
  `program_id` int DEFAULT NULL,
  `so_version_number` int DEFAULT NULL,
  `so_version_comments` text COLLATE utf8mb4_general_ci,
  `so_date_created` datetime DEFAULT NULL,
  `so_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studentoutcomescontents`
--

CREATE TABLE `studentoutcomescontents` (
  `so_content_id` int NOT NULL,
  `so_id` int DEFAULT NULL,
  `so_content_text` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supervisors`
--

CREATE TABLE `supervisors` (
  `supervisor_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `supervisor_jobrole` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userlogs`
--

CREATE TABLE `userlogs` (
  `userlog_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `userlog_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `userlog_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `userlog_ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `userlog_device_info` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `user_first_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_last_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_date_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_is_archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_first_name`, `user_last_name`, `user_email`, `user_date_created`, `user_date_updated`, `user_role`, `user_is_archived`) VALUES
(1, 'Addy', 'Min', 'addymin@apc.edu.ph', '2026-04-12 11:09:09', '2026-04-12 11:11:14', 'admin', 0),
(105, 'Inte', 'Offi', 'inteoffi@apc.edu.ph', '2026-05-06 03:45:53', '2026-05-06 03:45:53', 'officer', 0),
(106, 'Engi', 'Exe', 'engiexe@apc.edu.ph', '2026-05-06 04:00:50', '2026-05-06 04:00:50', 'executive', 0),
(107, 'Comp Engi', 'Prog', 'cpeprog@apc.edu.ph', '2026-05-06 04:14:44', '2026-05-06 04:14:44', 'program', 0),
(108, 'Elec Engi', 'Prog', 'eceprog@apc.edu.ph', '2026-05-06 04:18:02', '2026-05-06 04:18:02', 'program', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accomplishmentlogs`
--
ALTER TABLE `accomplishmentlogs`
  ADD PRIMARY KEY (`acclog_id`),
  ADD KEY `accomplishment_id` (`accomplishment_id`);

--
-- Indexes for table `accomplishmentreports`
--
ALTER TABLE `accomplishmentreports`
  ADD PRIMARY KEY (`accreport_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `assessmentcontents`
--
ALTER TABLE `assessmentcontents`
  ADD PRIMARY KEY (`assessment_content_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `assessmentfeedback`
--
ALTER TABLE `assessmentfeedback`
  ADD PRIMARY KEY (`afeedback_content_id`);

--
-- Indexes for table `assessmentgrades`
--
ALTER TABLE `assessmentgrades`
  ADD PRIMARY KEY (`assessment_grade_id`),
  ADD KEY `internship_id` (`internship_id`),
  ADD KEY `assessment_content_id` (`assessment_content_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  ADD PRIMARY KEY (`attlog_id`),
  ADD KEY `attrep_id` (`attrep_id`);

--
-- Indexes for table `attendancereports`
--
ALTER TABLE `attendancereports`
  ADD PRIMARY KEY (`attrep_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `courseoutcomes`
--
ALTER TABLE `courseoutcomes`
  ADD PRIMARY KEY (`co_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `courseoutcomesassessments`
--
ALTER TABLE `courseoutcomesassessments`
  ADD PRIMARY KEY (`coassessment_id`),
  ADD KEY `co_content_id` (`co_content_id`),
  ADD KEY `assessment_content_id` (`assessment_content_id`);

--
-- Indexes for table `courseoutcomescontents`
--
ALTER TABLE `courseoutcomescontents`
  ADD PRIMARY KEY (`co_content_id`),
  ADD KEY `co_id` (`co_id`);

--
-- Indexes for table `courseoutcomesstudentoutcomes`
--
ALTER TABLE `courseoutcomesstudentoutcomes`
  ADD PRIMARY KEY (`coso_id`),
  ADD KEY `so_content_id` (`so_content_id`),
  ADD KEY `co_content_id` (`co_content_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `eventrecipients`
--
ALTER TABLE `eventrecipients`
  ADD PRIMARY KEY (`event_recipient_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `executivedirectors`
--
ALTER TABLE `executivedirectors`
  ADD PRIMARY KEY (`exd_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `executivedirectors_ibfk_1` (`schooluser_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `fk_fb_intern` (`internship_id`),
  ADD KEY `fk_fb_af_content` (`afeedback_content_id`);

--
-- Indexes for table `finalnarratives`
--
ALTER TABLE `finalnarratives`
  ADD PRIMARY KEY (`finalnarrative_id`),
  ADD KEY `fk_fn_report` (`finalreport_id`),
  ADD KEY `fk_fn_content` (`fp_id`);

--
-- Indexes for table `finalpapercontents`
--
ALTER TABLE `finalpapercontents`
  ADD PRIMARY KEY (`fp_id`);

--
-- Indexes for table `finalpapers`
--
ALTER TABLE `finalpapers`
  ADD PRIMARY KEY (`fp_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `finalreports`
--
ALTER TABLE `finalreports`
  ADD PRIMARY KEY (`finalreport_id`),
  ADD KEY `fk_fr_intern` (`internship_id`);

--
-- Indexes for table `interns`
--
ALTER TABLE `interns`
  ADD PRIMARY KEY (`intern_id`),
  ADD KEY `schooluser_id` (`schooluser_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `internshipgrades`
--
ALTER TABLE `internshipgrades`
  ADD PRIMARY KEY (`internshipgrade_id`),
  ADD KEY `fk_ig_intern` (`internship_id`);

--
-- Indexes for table `internshipofficers`
--
ALTER TABLE `internshipofficers`
  ADD PRIMARY KEY (`io_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD KEY `intern_id` (`intern_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `mapping`
--
ALTER TABLE `mapping`
  ADD PRIMARY KEY (`mapping_id`),
  ADD KEY `coassessment_id` (`coassessment_id`),
  ADD KEY `coso_id` (`coso_id`);

--
-- Indexes for table `mappingvalidations`
--
ALTER TABLE `mappingvalidations`
  ADD PRIMARY KEY (`mappingvalidations_id`),
  ADD KEY `so_id` (`so_id`),
  ADD KEY `pd_id` (`pd_id`),
  ADD KEY `exd_id` (`exd_id`);

--
-- Indexes for table `notificationrecipients`
--
ALTER TABLE `notificationrecipients`
  ADD PRIMARY KEY (`notifrecip_id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `onetimepasswords`
--
ALTER TABLE `onetimepasswords`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `schooluser_id` (`schooluser_id`);

--
-- Indexes for table `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD PRIMARY KEY (`passreset_id`),
  ADD KEY `passwordresets_ibfk_1` (`schooluser_id`);

--
-- Indexes for table `programdirectors`
--
ALTER TABLE `programdirectors`
  ADD PRIMARY KEY (`pd_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `programdirectors_ibfk_1` (`schooluser_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `reportformats`
--
ALTER TABLE `reportformats`
  ADD PRIMARY KEY (`reportformat_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reportparameters`
--
ALTER TABLE `reportparameters`
  ADD PRIMARY KEY (`reportparam_id`),
  ADD KEY `reportformat_id` (`reportformat_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `io_id` (`io_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`),
  ADD UNIQUE KEY `school_name` (`school_name`);

--
-- Indexes for table `schoolusers`
--
ALTER TABLE `schoolusers`
  ADD PRIMARY KEY (`schooluser_id`),
  ADD KEY `schoolusers_ibfk_1` (`user_id`);

--
-- Indexes for table `sendassessments`
--
ALTER TABLE `sendassessments`
  ADD PRIMARY KEY (`sendassessment_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `internship_id` (`internship_id`);

--
-- Indexes for table `stringliterals`
--
ALTER TABLE `stringliterals`
  ADD PRIMARY KEY (`string_literal_id`),
  ADD KEY `schooluser_id` (`schooluser_id`);

--
-- Indexes for table `studentoutcomes`
--
ALTER TABLE `studentoutcomes`
  ADD PRIMARY KEY (`so_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `studentoutcomescontents`
--
ALTER TABLE `studentoutcomescontents`
  ADD PRIMARY KEY (`so_content_id`),
  ADD KEY `so_id` (`so_id`);

--
-- Indexes for table `supervisors`
--
ALTER TABLE `supervisors`
  ADD PRIMARY KEY (`supervisor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `userlogs`
--
ALTER TABLE `userlogs`
  ADD PRIMARY KEY (`userlog_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accomplishmentlogs`
--
ALTER TABLE `accomplishmentlogs`
  MODIFY `acclog_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accomplishmentreports`
--
ALTER TABLE `accomplishmentreports`
  MODIFY `accreport_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessmentcontents`
--
ALTER TABLE `assessmentcontents`
  MODIFY `assessment_content_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessmentfeedback`
--
ALTER TABLE `assessmentfeedback`
  MODIFY `afeedback_content_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessmentgrades`
--
ALTER TABLE `assessmentgrades`
  MODIFY `assessment_grade_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  MODIFY `attlog_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendancereports`
--
ALTER TABLE `attendancereports`
  MODIFY `attrep_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courseoutcomes`
--
ALTER TABLE `courseoutcomes`
  MODIFY `co_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courseoutcomesassessments`
--
ALTER TABLE `courseoutcomesassessments`
  MODIFY `coassessment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courseoutcomescontents`
--
ALTER TABLE `courseoutcomescontents`
  MODIFY `co_content_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courseoutcomesstudentoutcomes`
--
ALTER TABLE `courseoutcomesstudentoutcomes`
  MODIFY `coso_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventrecipients`
--
ALTER TABLE `eventrecipients`
  MODIFY `event_recipient_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `executivedirectors`
--
ALTER TABLE `executivedirectors`
  MODIFY `exd_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finalnarratives`
--
ALTER TABLE `finalnarratives`
  MODIFY `finalnarrative_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finalpapercontents`
--
ALTER TABLE `finalpapercontents`
  MODIFY `fp_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finalpapers`
--
ALTER TABLE `finalpapers`
  MODIFY `fp_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finalreports`
--
ALTER TABLE `finalreports`
  MODIFY `finalreport_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interns`
--
ALTER TABLE `interns`
  MODIFY `intern_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internshipgrades`
--
ALTER TABLE `internshipgrades`
  MODIFY `internshipgrade_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internshipofficers`
--
ALTER TABLE `internshipofficers`
  MODIFY `io_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mapping`
--
ALTER TABLE `mapping`
  MODIFY `mapping_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mappingvalidations`
--
ALTER TABLE `mappingvalidations`
  MODIFY `mappingvalidations_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificationrecipients`
--
ALTER TABLE `notificationrecipients`
  MODIFY `notifrecip_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `onetimepasswords`
--
ALTER TABLE `onetimepasswords`
  MODIFY `otp_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `passwordresets`
--
ALTER TABLE `passwordresets`
  MODIFY `passreset_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `programdirectors`
--
ALTER TABLE `programdirectors`
  MODIFY `pd_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reportformats`
--
ALTER TABLE `reportformats`
  MODIFY `reportformat_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reportparameters`
--
ALTER TABLE `reportparameters`
  MODIFY `reportparam_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `school_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `schoolusers`
--
ALTER TABLE `schoolusers`
  MODIFY `schooluser_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `sendassessments`
--
ALTER TABLE `sendassessments`
  MODIFY `sendassessment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `status_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stringliterals`
--
ALTER TABLE `stringliterals`
  MODIFY `string_literal_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studentoutcomes`
--
ALTER TABLE `studentoutcomes`
  MODIFY `so_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `studentoutcomescontents`
--
ALTER TABLE `studentoutcomescontents`
  MODIFY `so_content_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supervisors`
--
ALTER TABLE `supervisors`
  MODIFY `supervisor_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userlogs`
--
ALTER TABLE `userlogs`
  MODIFY `userlog_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accomplishmentlogs`
--
ALTER TABLE `accomplishmentlogs`
  ADD CONSTRAINT `accomplishmentlogs_ibfk_1` FOREIGN KEY (`accomplishment_id`) REFERENCES `accomplishmentreports` (`accreport_id`);

--
-- Constraints for table `accomplishmentreports`
--
ALTER TABLE `accomplishmentreports`
  ADD CONSTRAINT `accomplishmentreports_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `assessmentcontents`
--
ALTER TABLE `assessmentcontents`
  ADD CONSTRAINT `assessmentcontents_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`);

--
-- Constraints for table `assessmentgrades`
--
ALTER TABLE `assessmentgrades`
  ADD CONSTRAINT `assessmentgrades_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`),
  ADD CONSTRAINT `assessmentgrades_ibfk_2` FOREIGN KEY (`assessment_content_id`) REFERENCES `assessmentcontents` (`assessment_content_id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  ADD CONSTRAINT `attendancelogs_ibfk_1` FOREIGN KEY (`attrep_id`) REFERENCES `attendancereports` (`attrep_id`);

--
-- Constraints for table `attendancereports`
--
ALTER TABLE `attendancereports`
  ADD CONSTRAINT `attendancereports_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `courseoutcomes`
--
ALTER TABLE `courseoutcomes`
  ADD CONSTRAINT `courseoutcomes_ibfk_1` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `courseoutcomesassessments`
--
ALTER TABLE `courseoutcomesassessments`
  ADD CONSTRAINT `courseoutcomesassessments_ibfk_1` FOREIGN KEY (`co_content_id`) REFERENCES `courseoutcomescontents` (`co_content_id`),
  ADD CONSTRAINT `courseoutcomesassessments_ibfk_2` FOREIGN KEY (`assessment_content_id`) REFERENCES `assessmentcontents` (`assessment_content_id`);

--
-- Constraints for table `courseoutcomescontents`
--
ALTER TABLE `courseoutcomescontents`
  ADD CONSTRAINT `courseoutcomescontents_ibfk_1` FOREIGN KEY (`co_id`) REFERENCES `courseoutcomes` (`co_id`);

--
-- Constraints for table `courseoutcomesstudentoutcomes`
--
ALTER TABLE `courseoutcomesstudentoutcomes`
  ADD CONSTRAINT `courseoutcomesstudentoutcomes_ibfk_1` FOREIGN KEY (`so_content_id`) REFERENCES `studentoutcomescontents` (`so_content_id`),
  ADD CONSTRAINT `courseoutcomesstudentoutcomes_ibfk_2` FOREIGN KEY (`co_content_id`) REFERENCES `courseoutcomescontents` (`co_content_id`);

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`);

--
-- Constraints for table `eventrecipients`
--
ALTER TABLE `eventrecipients`
  ADD CONSTRAINT `eventrecipients_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `eventrecipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `executivedirectors`
--
ALTER TABLE `executivedirectors`
  ADD CONSTRAINT `executivedirectors_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `executivedirectors_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_fb_af_content` FOREIGN KEY (`afeedback_content_id`) REFERENCES `assessmentfeedback` (`afeedback_content_id`),
  ADD CONSTRAINT `fk_fb_intern` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `finalnarratives`
--
ALTER TABLE `finalnarratives`
  ADD CONSTRAINT `fk_fn_content` FOREIGN KEY (`fp_id`) REFERENCES `finalpapercontents` (`fp_id`),
  ADD CONSTRAINT `fk_fn_report` FOREIGN KEY (`finalreport_id`) REFERENCES `finalreports` (`finalreport_id`);

--
-- Constraints for table `finalpapers`
--
ALTER TABLE `finalpapers`
  ADD CONSTRAINT `finalpapers_ibfk_1` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `finalreports`
--
ALTER TABLE `finalreports`
  ADD CONSTRAINT `fk_fr_intern` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `interns`
--
ALTER TABLE `interns`
  ADD CONSTRAINT `interns_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`),
  ADD CONSTRAINT `interns_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `internshipgrades`
--
ALTER TABLE `internshipgrades`
  ADD CONSTRAINT `fk_ig_intern` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`intern_id`) REFERENCES `interns` (`intern_id`),
  ADD CONSTRAINT `internships_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`supervisor_id`),
  ADD CONSTRAINT `internships_ibfk_3` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `mapping`
--
ALTER TABLE `mapping`
  ADD CONSTRAINT `mapping_ibfk_1` FOREIGN KEY (`coassessment_id`) REFERENCES `courseoutcomesassessments` (`coassessment_id`),
  ADD CONSTRAINT `mapping_ibfk_2` FOREIGN KEY (`coso_id`) REFERENCES `courseoutcomesstudentoutcomes` (`coso_id`);

--
-- Constraints for table `mappingvalidations`
--
ALTER TABLE `mappingvalidations`
  ADD CONSTRAINT `mappingvalidations_ibfk_1` FOREIGN KEY (`so_id`) REFERENCES `studentoutcomes` (`so_id`),
  ADD CONSTRAINT `mappingvalidations_ibfk_2` FOREIGN KEY (`pd_id`) REFERENCES `programdirectors` (`pd_id`),
  ADD CONSTRAINT `mappingvalidations_ibfk_3` FOREIGN KEY (`exd_id`) REFERENCES `executivedirectors` (`exd_id`);

--
-- Constraints for table `notificationrecipients`
--
ALTER TABLE `notificationrecipients`
  ADD CONSTRAINT `notificationrecipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`notification_id`),
  ADD CONSTRAINT `notificationrecipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `onetimepasswords`
--
ALTER TABLE `onetimepasswords`
  ADD CONSTRAINT `onetimepasswords_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`);

--
-- Constraints for table `passwordresets`
--
ALTER TABLE `passwordresets`
  ADD CONSTRAINT `passwordresets_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `programdirectors`
--
ALTER TABLE `programdirectors`
  ADD CONSTRAINT `programdirectors_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `programdirectors_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`);

--
-- Constraints for table `reportformats`
--
ALTER TABLE `reportformats`
  ADD CONSTRAINT `reportformats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reportparameters`
--
ALTER TABLE `reportparameters`
  ADD CONSTRAINT `reportparameters_ibfk_1` FOREIGN KEY (`reportformat_id`) REFERENCES `reportformats` (`reportformat_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`io_id`) REFERENCES `internshipofficers` (`io_id`);

--
-- Constraints for table `schoolusers`
--
ALTER TABLE `schoolusers`
  ADD CONSTRAINT `schoolusers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `sendassessments`
--
ALTER TABLE `sendassessments`
  ADD CONSTRAINT `sendassessments_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `statuses`
--
ALTER TABLE `statuses`
  ADD CONSTRAINT `statuses_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`);

--
-- Constraints for table `stringliterals`
--
ALTER TABLE `stringliterals`
  ADD CONSTRAINT `stringliterals_ibfk_1` FOREIGN KEY (`schooluser_id`) REFERENCES `schoolusers` (`schooluser_id`);

--
-- Constraints for table `studentoutcomes`
--
ALTER TABLE `studentoutcomes`
  ADD CONSTRAINT `studentoutcomes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `studentoutcomescontents`
--
ALTER TABLE `studentoutcomescontents`
  ADD CONSTRAINT `studentoutcomescontents_ibfk_1` FOREIGN KEY (`so_id`) REFERENCES `studentoutcomes` (`so_id`);

--
-- Constraints for table `supervisors`
--
ALTER TABLE `supervisors`
  ADD CONSTRAINT `supervisors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `supervisors_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `userlogs`
--
ALTER TABLE `userlogs`
  ADD CONSTRAINT `userlogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
