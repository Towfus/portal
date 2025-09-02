-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 04:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `deped_schools`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`, `is_active`, `start_date`, `end_date`) VALUES
(8, 'Nutrition month', 'sample', '2025-08-15 14:28:10', 1, '2025-08-16', '2025-08-23'),
(9, 'ZUMBAYANI', 'ZUMBA w The New Heroes', '2025-08-15 16:00:30', 1, '2025-08-15', '2025-08-23');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `banner_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `filename`, `category`, `filepath`, `filesize`, `upload_date`) VALUES
(27, 'New Government Permit Application.pdf', 'New Government Permit Application', 'shared/forms/gov_permit/New Government Permit Application.pdf', 623292, '2025-08-15 07:20:20'),
(28, 'Renewal or Recognition Application.pdf', 'Renewal/Recognition Application', 'shared/forms/renewal_recognition/Renewal or Recognition Application.pdf', 542250, '2025-08-15 07:21:21'),
(29, 'Renewal_Recognition Application.docx', 'Renewal/Recognition Application', 'shared/forms/renewal_recognition/Renewal_Recognition Application.docx', 2749470, '2025-08-15 07:21:42'),
(30, '005-APRIL-SO-REQUIREMENTS.pdf', 'Special Order Requirements', 'shared/forms/special_order/005-APRIL-SO-REQUIREMENTS.pdf', 562856, '2025-08-15 07:22:40'),
(31, 'SUMMER EVALUATION.pdf', 'Summer Classes Application', 'shared/forms/summer_classes/SUMMER EVALUATION.pdf', 564024, '2025-08-15 07:23:16'),
(32, 'Tuition Fee Increase Application.pdf', 'Tuition Fee Increase Application', 'shared/forms/tuition_increase/Tuition Fee Increase Application.pdf', 255694, '2025-08-15 07:24:05');

-- --------------------------------------------------------

--
-- Table structure for table `memorandums`
--

CREATE TABLE `memorandums` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memorandums`
--

INSERT INTO `memorandums` (`id`, `title`, `description`, `file_path`, `upload_date`) VALUES
(26, 'AMENDMENT TO DEPED MEMORANDUM NO. 090', NULL, 'C:\\xampp\\htdocs\\Github\\portal\\shared\\memorandums\\memo_1755242171_4621.pdf', '2025-08-15 15:16:11'),
(27, '2010 REVISED MANUAL OF REGULATIONS FOR PRIVATE SCHOOLS IN BASIC EDUCATION', NULL, 'C:\\xampp\\htdocs\\Github\\portal\\shared\\memorandums\\memo_1755242326_6335.pdf', '2025-08-15 15:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `process_paths`
--

CREATE TABLE `process_paths` (
  `path_id` int(11) NOT NULL,
  `process_id` varchar(50) NOT NULL,
  `path_type` enum('compliant','nonCompliant') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `process_paths`
--

INSERT INTO `process_paths` (`path_id`, `process_id`, `path_type`, `created_at`) VALUES
(1, 'new_permit', 'compliant', '2025-08-05 06:10:01'),
(2, 'new_permit', 'nonCompliant', '2025-08-05 06:10:01'),
(3, 'gov_recognition', 'compliant', '2025-08-05 06:10:01'),
(4, 'gov_recognition', 'nonCompliant', '2025-08-11 00:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `process_steps`
--

CREATE TABLE `process_steps` (
  `step_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `step_order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `process_steps`
--

INSERT INTO `process_steps` (`step_id`, `path_id`, `step_order`, `title`, `description`, `created_at`, `updated_at`) VALUES
(36, 3, 1, 'Submission of Documentary', 'Submission of documentary requirements to the SDO', '2025-08-19 03:25:31', '2025-08-19 03:34:45'),
(37, 3, 2, 'Evaluation of Documents', 'Evaluation', '2025-08-19 03:25:56', '2025-08-19 03:34:45'),
(38, 3, 3, 'Preparation of Indorsement', 'Prepare Indorsement', '2025-08-19 03:27:01', '2025-08-19 03:34:45'),
(39, 3, 4, 'Submission of Documentary', 'Submission of Documentary requirements to the RO', '2025-08-19 03:27:45', '2025-08-19 03:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `process_types`
--

CREATE TABLE `process_types` (
  `process_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `announcement` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `process_types`
--

INSERT INTO `process_types` (`process_id`, `title`, `description`, `announcement`, `created_at`, `updated_at`) VALUES
('gov_recognition', 'Application of Government Recognition', 'Government Recognition Process', '', '2025-08-05 06:10:01', '2025-08-15 05:06:00'),
('new_permit', 'new permit application process', 'New Permit Application Process', '', '2025-08-05 06:10:01', '2025-08-15 05:06:33');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `school_id` varchar(50) DEFAULT NULL,
  `school_head` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `offers_elementary` tinyint(1) DEFAULT 0,
  `offers_jhs` tinyint(1) DEFAULT 0,
  `offers_shs` tinyint(1) DEFAULT 0,
  `offers_sped` tinyint(1) DEFAULT 0,
  `renewal` tinyint(1) DEFAULT 0,
  `recognize` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `telephone_number` varchar(50) DEFAULT NULL,
  `mobile_number` varchar(50) DEFAULT NULL,
  `elementary_grades` varchar(50) DEFAULT NULL,
  `jhs_grades` varchar(50) DEFAULT NULL,
  `shs_grades` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `school_name`, `barangay`, `district`, `school_id`, `school_head`, `address`, `contact_number`, `email`, `offers_elementary`, `offers_jhs`, `offers_shs`, `offers_sped`, `renewal`, `recognize`, `created_at`, `updated_at`, `city`, `province`, `telephone_number`, `mobile_number`, `elementary_grades`, `jhs_grades`, `shs_grades`) VALUES
(1, 'A1.W.M. Learning Academy Inc.', 'Manggahan', NULL, '424462', NULL, 'Brgy.Mangahan, General Trias City, Cavite', NULL, 'depedcavite.a1wmacademy@gmail.com', 0, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:08:38', 'General Trias City', 'Cavite', '(046) 413-1172', '', '', 'Grade 7, Grade 8, Grade 9, Grade 10', 'Grade 11, Grade 12'),
(2, 'Academy of Saint John - La Salle Green Hills Super...', 'Sta. Clara', NULL, '424009', NULL, 'Sta. Clara, Gen. Trias, Cavite', NULL, 'asj_lsgh@yahoo.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:25:24', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(3, 'Ace Bloomers Academy, Inc.', 'Pasong Kawayan 2', NULL, '424463', NULL, 'South Square Village, Brgy. Pasong Kawayan 2, Gene...', NULL, 'rowenayulopez@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:25:38', 'General Trias City', 'Cavite', '', '09190939752', 'Kinder, Grade 1 - 6', '', ''),
(4, 'Airulas Integrated School, Inc.', 'Pasong Kawayan II', NULL, '424872', NULL, 'Belvedere Towne III, Pasong Kawayan II, Genearal T...', NULL, 'rsc13_flusa05@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:25:53', 'General Trias City', 'Cavite', '', '09193171994', 'Kinder, Grade 1–6', NULL, NULL),
(5, 'AMA COLLEGE-CAVITE', 'Biclatan', NULL, '410398', NULL, 'Biclatan, Genearal Trias City, Cavite', NULL, 'jdbarit@amaes.edu.ph', 0, 0, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:26:08', 'General Trias City', 'Cavite', '', '', NULL, NULL, 'Grade 11–12'),
(6, 'Amazing Learners and Molders Learning Center Inc.', 'Pasong Camachile I', NULL, '424340', NULL, 'Grand Riverside Subd., Pasong Camachile I, Geneara...', NULL, 'amazinglearners.molders@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:26:26', 'General Trias City', 'Cavite', '', '09178620190', 'Kinder, Grade 1–6', NULL, NULL),
(7, 'Angelicum Immanuel Montessori of Cavite, Inc.', 'San Francisco', NULL, '402068', NULL, 'Phase 1, Tierra Nevada Subd., San Francisco, Genea...', NULL, 'angelicumgentri@yahoo.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:27:19', 'General Trias City', 'Cavite', '(046) 5382509', '', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(8, 'Bernice Learning Center Cavite, Inc.', 'Biclatan', NULL, '408568', NULL, 'Biclatan, Genearal Trias City, Cavite', NULL, 'bernicelearningcenter@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:28:03', 'General Trias City', 'Cavite', '', '09281513588', 'Kinder, Grade 1–3', NULL, NULL),
(9, 'Bethel Academy of Gen. Trias Cavite', 'Navarro', NULL, '402069', NULL, '9053 Pagasa St., Navarro, Genearal Trias City, Cav...', NULL, 'rizzapearltrias.bethelacademy@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:28:10', 'General Trias City', 'Cavite', '', '09066812692', NULL, NULL, NULL),
(10, 'Bethel Academy of Gen. Trias Cavite Inc.', 'Sta. Clara', NULL, '410408', NULL, '237 Sta. Clara, General Trias City, Cavite', NULL, 'rizzapearltrias.bethelacademy@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:28:55', 'General Trias City', 'Cavite', '', '09066812692', NULL, NULL, NULL),
(11, 'Blessed Maria Cristina Brando School', 'Santiago', NULL, '424191', NULL, '419 Arnaldo Highway, Brgy. Santiago, Genearal Tria...', NULL, 'bmcbs_m@yahoo.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:29:38', 'General Trias City', 'Cavite', '(046) 412-56-41', '09989', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(12, 'Brilliant Heights Scholastic Montessori Inc.', 'Santiago', NULL, '408557', NULL, 'Bella Vista Subdivision, Brgy. Santiago, Genearal ...', NULL, 'brilliantheightsinc@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:29:46', 'General Trias City', 'Cavite', '(043) 443 0570', '09357', 'Kinder, Grade 1–6', NULL, NULL),
(13, 'Castle of Young Mind Learning School', 'San Francisco', NULL, '424257', NULL, 'Parklane Country Homes, Genearal Trias City, Cavit...', NULL, 'jovelle.nazareno@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:30:00', 'General Trias City', 'Cavite', '', '', 'Kinder only', NULL, NULL),
(14, 'Centre D&#039;Etude De Mahershalalhashbaz Emmanuel Inc.', 'Pasong Camachile I', NULL, '424499', NULL, 'Grand Riverside Subd., Pasong Camachile I, Geneara...', NULL, 'ectanada@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:30:25', 'General Trias City', 'Cavite', '', '09284713048', 'Kinder only', NULL, NULL),
(15, 'Christian Yra Learning Center, Inc.', 'Pasong Camachile II', NULL, '424771', NULL, 'Pasong Camachile II, General Trias City', NULL, 'strongerbelle@gmail.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:30:32', 'General Trias City', 'Cavite', '', '09228694740', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(16, 'Claremont School of Gen. Trias Cavite, Inc.', 'Pasong Camachile I', NULL, '402070', NULL, 'Grand Riveside Subd., Pasong Camachile I, Genearal...', NULL, 'claremont_school@yahoo.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:30:40', 'General Trias City', 'Cavite', '(046) 887-2956', '09175', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(17, 'Colegio De Francesca, Inc.', 'San Francisco', NULL, '402072', NULL, 'Sec. 1 Block 37 Lot 41-B Sunnybrooke 2 San Francis...', NULL, 'depedcavite.cdf@gmail.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:30:48', 'General Trias City', 'Cavite', '', '09498805801', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(18, 'Colegio de San Francisco', 'Sampalucan', NULL, '424011', NULL, 'Sampalucan, Gen. Trias, Cavite', NULL, 'colegiodesanfrancisco2003@yahoo.com.ph', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:30:56', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(19, 'Corinthian Academy of Cavite in Gen. Trias Inc.', 'Poblacion', NULL, '402071', NULL, 'Gov. Ferrer Ave. cor. D. Mojica St. Gen. Trias Cit...', NULL, 'corinthian_academy@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:32:10', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(20, 'Cosmopolitan School of the Philippines Inc.', 'Buenavista I', NULL, '410407', NULL, 'Brgy. Buenavista I, Gen. Trias City, Cavite', NULL, 'cosmopolitanschoolofthephil@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:32:21', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–3', NULL, NULL),
(21, 'Del Rosario Christian Institute of Gen. Trias Inc....', 'Santiago', NULL, '409151', NULL, 'Bella Vista Subd., Brgy. Santiago, Gen. Trias City', NULL, 'marjesmylene18@gmail.com', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:32:28', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(22, 'Del Rosario Christian Institute of Gen.Trias', 'Pasong Kawayan II', NULL, '408038', NULL, 'Pasong Kawayan II, General Trias City', NULL, 'marjesmylene18@gmail.com', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:32:55', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(23, 'Dream Best Academy and Learning Institution Corp.', 'Buenavista II', NULL, '409281', NULL, 'Blk. 1 Cavite Economic Zone II, Gen. Trias City', NULL, 'hrdgroup-envi@hrd-s.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:33:06', 'General Trias City', 'Cavite', '', '', 'Kinder only', NULL, NULL),
(24, 'Evangel Christian Educational Center, Inc.', 'Buenavista III', NULL, '402073', NULL, '#074 General Trias-Amadeo Rd., Buenavista III, Cit...', NULL, 'ecec.edu@gmail.com', 1, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:33:14', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(25, 'Fiat Lux Academe of Gen. Trias, Cavite', 'Pasong Camachile 1', NULL, '402075', NULL, 'Block 28 Lots 1&amp;9 Phase 1 Grand Riverside Subd., P...', NULL, 'fla.gentri@gmail.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:33:27', 'General Trias City', 'Cavite', '(046) 887-2646', '', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(26, 'Franclouie Learning Center Inc.', 'Santiago', NULL, '424342', NULL, 'Block 23 Lot 1 KPNP Santiago General Trias City Ca...', NULL, 'franclouielearningcenter@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:33:38', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(27, 'Gateway Integrated School of Science &amp; Technology ...', 'Manggahan', NULL, '424343', NULL, 'Sitio Tinungan, Bgy. Manggahan, General Trias, Cav...', NULL, 'gatewayintegratedschool2009@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:33:50', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(28, 'Glorious God&#039;s Family Christian School, Inc.', 'Santiago', NULL, '409011', NULL, 'Bella Vista Subd.,Brgy. Santiago, General Trias, C...', NULL, 'arnel.shin@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:34:09', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(29, 'Governor&#039;s Hills Science School, Inc.', 'Biclatan', NULL, '424283', NULL, 'Governor&#039;s Hills Subdivision, Biclatan, General Tr...', NULL, 'ghss.govhills@gmail.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:34:18', 'General Trias City', 'Cavite', '(046) 484-8975', '', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(30, 'Haven of Virtue and Excellence Academy, Inc.', 'Pasong Camachile 1', NULL, '424284', NULL, 'Camachile Subd., Pasong Camachile 1, Gen. Trias, C...', NULL, 'hvea2004@gmail.com', 1, 1, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:34:26', 'General Trias City', 'Cavite', '', '09752607394', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(31, 'Haven of Wisdom Academy Inc.', 'Pasong Camachile II', NULL, '402077', NULL, 'Mary Cris Complex,Pasong Camachile II General Tria...', NULL, 'havenofwisdomacademy2021@gmail.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:36:27', 'General Trias City', 'Cavite', '(046) 850-0981', '', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(32, 'Hillcrest Periwinkle School, Inc.', 'San Juan 1', NULL, '424779', NULL, 'San Juan 1, General Trias City, Cavite', NULL, 'hillcrestperiwinkle.gentrias@yahoo.com', 1, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:36:36', 'General Trias City', 'Cavite', '046-887-2368', '', 'Kinder, Grade 1–6', NULL, NULL),
(33, 'John Isabel Learning Center, Inc.', 'Pasong Camachile 1', NULL, '424162', NULL, 'Camachile Subd. Pasong Camachile 1, General Trias ...', NULL, 'johnisabellearningcenter@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:36:46', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(34, 'Jubilee Christian Faith Academy - Gen. Trias Inc.', 'Pasong Camacile 2', NULL, '424875', NULL, 'Bgry. Sitio Alangilan St. Open Canal Road Pasong C...', NULL, 'depedcavite.jcfagentri@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:36:57', 'General Trias City', 'Cavite', '', '09235520645', 'Kinder, Grade 1–6', NULL, NULL),
(35, 'Kairos Academe Learning Center Inc.', 'Pasong Camachille II', NULL, '424874', NULL, 'Blk. 18 Lot 29 Ph. 1 San Jose Townhomes, Pasong Ca...', NULL, 'pastranamariasconcepcion@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:37:05', 'General Trias City', 'Cavite', '', '09561866575', 'Kinder, Grade 1–4', NULL, NULL),
(36, 'King Arthur Academy of Cavite, Inc.', 'Pasong Kawayan II', NULL, '409277', NULL, 'Pamayanang Maliksi Pasong Kawayan II General Trias...', NULL, 'kingarthuracademyinc.annex@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:37:21', 'General Trias City', 'Cavite', '', '09568008183', 'Grade 1–6', NULL, NULL),
(37, 'King Solomon Academy of Cavite Inc.', 'San Juan I', NULL, '424345', NULL, '108 San Juan I, City of Gen. Trias, Cavite', NULL, 'depalmamaricar@gmail.com', 1, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:37:32', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(38, 'La Salette Montessori Multiple Intelligences Learn...', 'San Francisco', NULL, '424117', NULL, 'San Francisco, General Trias City, Cavite', NULL, 'lsmmilc03@yahoo.com', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:38:03', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(39, 'Living Faith School of Cavite, Inc.', 'Camachile II', NULL, '402082', NULL, 'Marycris Complex, Camachile II, Gen. Trias City', NULL, 'livingfaithschool@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:38:12', 'General Trias City', 'Cavite', '', '09754462340', 'Kinder, Grade 1–6', NULL, NULL),
(40, 'Lois Faith Christian Academy', 'Manggahan', NULL, '402083', NULL, 'Arnaldo St., Manggahan, General Trias City, Cavite', NULL, 'loisfaith1999@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:38:20', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(41, 'Lyceum de Abuid Inc.', 'Buenavista 2', NULL, '408650', NULL, 'Tahanang Yaman Homes Buenavista 2 General Trias Ci...', NULL, 'sheryl.abuid@yahoo.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:38:30', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–4', NULL, NULL),
(42, 'Lyceum of the Philippines, Inc.', 'Manggahan', NULL, '424285', NULL, 'Governor&#039;s Drive, General Trias City, Cavite', NULL, 'mhar.bayot@lpu.edu.ph', 0, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:39:41', 'General Trias City', 'Cavite', '(046) 481-1400', '(046)', NULL, 'Grade 7–10', 'Grade 11–12'),
(43, 'Mindful School Of Berlyn Achievers Inc.', 'San Francisco', NULL, '409747', NULL, 'San Francisco, General Trias City, Cavite', NULL, 'merlbill061114@gmail.com', 1, 1, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:39:54', 'General Trias City', 'Cavite', '', '09267384119', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(44, 'Miriam Carmeli School, Inc.', 'San Francisco', NULL, '402088', NULL, 'Tierra Nevada, SF, Gen. Trias City', NULL, 'miriam.carmeli.school@gmail.com', 1, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:01', 'General Trias City', 'Cavite', '', '09214687389', 'Kinder, Grade 1–6', NULL, NULL),
(45, 'Mother Theresa School (Gen. Trias, Cavite), Inc.', 'San Francisco', NULL, '402086', NULL, 'San Francisco General Trias City, Cavite', NULL, 'mtsgentri@gmail.com', 1, 1, 1, 1, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:10', 'General Trias City', 'Cavite', '(046) 402-2459', '09475', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(46, 'Nataniel Castle Learning Center Inc.', 'San Francisco', NULL, '410614', NULL, 'Kanutuhan ll, Brgy. San Francisco, Generial Trias ...', NULL, 'natanielclc@gmail.com', 1, 0, 0, 1, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:40:20', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–4', NULL, NULL),
(47, 'New Buenavista Academy, Inc', 'Manggahan', NULL, '402087', NULL, 'Manggahan, General Trias City, Cavite', NULL, 'primadeldeseo@yahoo.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:27', 'General Trias City', 'Cavite', '(046) 409 1731', '09063', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(48, 'Nineveh Learning Center, Inc.', 'San Francisco', NULL, '424238', NULL, 'Brookeside Lane, San Francisco, General Trias, Cav...', NULL, 'nineveh_academy@yahoo.com.ph', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:36', 'General Trias City', 'Cavite', '(046) 489--8305', '09', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(49, 'Our Lady of Remedios College of Science &amp; Technolo...', 'Buenavista II', NULL, '424118', NULL, 'Buenavista II, Gen. Trias City Cavite', NULL, 'olrms05@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:44', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(50, 'Our Lady of the Holy Rosary Educational Foundation...', 'Pasong Kawayan I', NULL, '424830', NULL, 'Pasong Kawayan I, Gen. Trias City, Cavite', NULL, 'imeencarnacion20@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:40:52', 'General Trias City', 'Cavite', '(046) 4245131', '', NULL, NULL, NULL),
(51, 'Potters&#039; Hand Academy of Gen. Trias, Inc.', 'San Francisco', NULL, '424192', NULL, 'San Francisco, Gen. Trias, Cavite', NULL, 'pottershandacademy@yahoo.com', 1, 1, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:41:16', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', 'Grade 7', NULL),
(52, 'Potter&#039;s Heart of Wisdom Academy, INC.', 'Navarro', NULL, '424465', NULL, 'Brgy. Navarro City of General Trias, Cavite', NULL, 'pottersheartofwisdom@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:41:05', 'General Trias City', 'Cavite', '(046)-4318651', '0967 0', 'Kinder, Grade 1–6', NULL, NULL),
(53, 'Saint Charles Borromeo Integrated School', 'San Francisco', NULL, '424820', NULL, 'San Francisco, General Trias City, Cavite', NULL, 'scborromeo_ischool@yahoo.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:41:22', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', 'Grade 7–8', NULL),
(54, 'Saint Francis School', 'San Juan 1', NULL, '402093', NULL, 'San Juan 1, General Trias City, Cavite', NULL, 'tenerefev@gmail.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:41:32', 'General Trias City', 'Cavite', '', '09163769166', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(55, 'Saint Gilbert Myra Academe Inc.', 'Santiago', NULL, '408777', NULL, 'Bella Vista Homes, Brgy. Santiago, Gen. Trias City', NULL, 'st.gilbert.myra2016@gmail.com', 1, 1, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:41:40', 'General Trias City', 'Cavite', '(046) 4040467', '0927-3', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(56, 'Samuel Christian College of Gen. Trias, Inc.', 'Navarro', NULL, '424466', NULL, 'Navarro, City of Gen. Trias, Cavite', NULL, 'samuelchristiancollegegti@gmail.com', 0, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:41:52', 'General Trias City', 'Cavite', '(046) 402-0725', '0916-', NULL, 'Grade 7–10', 'Grade 11–12'),
(57, 'San Francisco de Malabon Parochial School, Inc.', 'Poblacion', NULL, '402094', NULL, 'Poblacion, City of Gen Trias, Cavite', NULL, 'sdemalabon@yahoo.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:42:27', 'General Trias City', 'Cavite', '(046) 437-2534', '09053', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(58, 'Saving Grace Christian School in General Trias Cav...', 'San Francisco', NULL, '424163', NULL, 'Bgy. San Francisco Gen. Trias, Cavite', NULL, 'bethciudad.savinggrace@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:42:36', 'General Trias City', 'Cavite', '', '09178934205', NULL, NULL, NULL),
(59, 'SFS Aid of Angels Learning Center Inc.', 'Unknown', NULL, '424888', NULL, '#37 96th Street Gen. Trias City, Cavite 4107', NULL, 'aidofangels@yahoo.com', 0, 0, 0, 1, 0, 0, '2025-08-15 06:46:41', '2025-08-15 06:46:41', 'General Trias City', 'Cavite', '(046) 424-9680', '0917-', NULL, NULL, NULL),
(60, 'Shepherd&#039;s Haven Christian Academy of Cavite (SHCA...', 'PasCam 2', NULL, '424821', NULL, 'Mary Cris Complex, PasCam 2, General Trias City, C...', NULL, 'shcaci.cavite2013@gmail.com', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:42:49', 'General Trias City', 'Cavite', '(046) 431-1960', '0995-', NULL, NULL, NULL),
(61, 'Southville Monarchs International School', 'Bacao II', NULL, '410606', NULL, 'Grand Parklane Blk. 70A Lot 1 Antel Grand Village,...', NULL, 'rahima_ona@southville.edu.ph', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:42:57', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(62, 'St. Alloysius Gonzaga Integrated School of Cavite,...', 'San Francisco', NULL, '402092', NULL, 'Sunny Brooke 1, Brgy. San Francisco, General Trias...', NULL, 'sagis.bella.vista@gmail.com', 0, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:43:05', 'General Trias City', 'Cavite', '', '09282155626', NULL, NULL, NULL),
(63, 'St. Carmen Salles School, Cavite Inc.', 'Buenavista II', NULL, '410160', NULL, 'Gov. Ferrer Drive, Buenavista II, General Trias, C...', NULL, 'scssc2019@gmail.com', 1, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:43:14', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(64, 'St. Clare of Assisi Special Education School, Inc.', 'Sta. Clara', NULL, '408874', NULL, 'Brgy. Sta. Clara, General Trias City, Cavite', NULL, 'st.clare.spedschool@gmail.com', 0, 0, 0, 1, 0, 0, '2025-08-15 06:46:41', '2025-08-15 06:46:41', 'General Trias City', 'Cavite', NULL, '09178638291', NULL, NULL, NULL),
(65, 'St. Edward Integrated School Foundation-Cavite Inc...', 'Navarro', NULL, '408626', NULL, 'Kensington 2, Lancaster New City, Brgy. Navarro, G...', NULL, 'jtcabalo@ses.edu.ph', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:43:45', 'General Trias City', 'Cavite', '', '', NULL, NULL, NULL),
(66, 'Star Blossoms Academy', 'Santiago', NULL, '402095', NULL, 'Parklane Country Homes, Brgy. Santiago,City of Gen...', NULL, 'sba@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:44:03', 'General Trias City', 'Cavite', '', '', 'Kinder, Grade 1–6', NULL, NULL),
(67, 'Teruah Early Childhood Care Center, Inc.', 'Pasong Kawayan II', NULL, '424467', NULL, 'Pasong Kawayan II, General Trias City, Cavite', NULL, 'depedcavite.teruaheccci@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:44:17', 'General Trias City', 'Cavite', '', '09158757110', 'Kinder only', NULL, NULL),
(68, 'The Centennial Academy of the Blessed Trinity-Cavi...', 'Sampalucan', NULL, '424500', NULL, 'Bgy. Sampalucan, City of General Trias, Cavite 410...', NULL, 'depedgentri.cabt2012@gmail.com', 0, 0, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:44:23', 'General Trias City', 'Cavite', '046-450-0805', '', NULL, NULL, NULL),
(69, 'The First Uniting Christian School', 'Pasong Camachile II', NULL, '424074', NULL, 'Pasong Camachile II, General Trias City, Cavite', NULL, 'mctfucs2000@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:44:31', 'General Trias City', 'Cavite', '', '09087056389', 'Kinder, Grade 1–6', NULL, NULL),
(70, 'The Palmridge School Inc.', 'Santiago', NULL, '408275', NULL, 'Brgy. Santiago, Arnaldo Highway, City of General T...', NULL, 'ms.lissa@thepalmridgeschool.com', 1, 1, 0, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:44:55', 'General Trias City', 'Cavite', '(046) 440 3882', '09190', 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(71, 'Whizzkid Christian Academy Inc.', 'Pasong Kawayan 2', NULL, '409143', NULL, 'Pamayanang Maliksi, Pasong Kawayan 2,General Trias...', NULL, 'whizzkidchristianacademy@gmail.com', 1, 0, 0, 0, 1, 0, '2025-08-15 06:46:41', '2025-08-15 07:45:08', 'General Trias City', 'Cavite', '', '09776250862', 'Kinder, Grade 1–5', NULL, NULL),
(72, 'Young Ji International School, Inc.', 'San Francisco', NULL, '424194', NULL, 'San Francisco, General Trias City, Cavite', NULL, 'young_ji_office@yahoo.com', 1, 1, 1, 0, 0, 1, '2025-08-15 06:46:41', '2025-08-15 07:45:14', 'General Trias City', 'Cavite', '', '09171755065', 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `is_admin`, `reset_token`, `reset_expiry`, `password_reset_token`, `token_expiration`) VALUES
(7, 'admin@deped.gov.ph', '$2y$10$fmkRSkf6KHm6/RHfMTeAreijELPculoTBDKobPNkyN8PjUT4sINTu', '2025-08-05 01:56:04', 1, NULL, NULL, NULL, NULL),
(8, 'test@deped.gov.ph', '$2y$10$iDP3ajXoZSj/C4jxroxgj.GCVQ1JJNkVwRSBZpPKXYoI4sDlZ9p6S', '2025-08-05 02:13:56', 0, NULL, NULL, NULL, NULL),
(9, 'smn.gentri@deped.gov.ph', '$2y$10$8NAHgTbIeYQJFeuEeFTgh.uHzpDPlQCw/KJTPkDISywgj4YRO3ZwS', '2025-08-05 02:21:41', 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `memorandums`
--
ALTER TABLE `memorandums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `process_paths`
--
ALTER TABLE `process_paths`
  ADD PRIMARY KEY (`path_id`),
  ADD KEY `process_id` (`process_id`);

--
-- Indexes for table `process_steps`
--
ALTER TABLE `process_steps`
  ADD PRIMARY KEY (`step_id`),
  ADD KEY `path_id` (`path_id`,`step_order`);

--
-- Indexes for table `process_types`
--
ALTER TABLE `process_types`
  ADD PRIMARY KEY (`process_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay` (`barangay`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `memorandums`
--
ALTER TABLE `memorandums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `process_paths`
--
ALTER TABLE `process_paths`
  MODIFY `path_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `process_steps`
--
ALTER TABLE `process_steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `process_paths`
--
ALTER TABLE `process_paths`
  ADD CONSTRAINT `process_paths_ibfk_1` FOREIGN KEY (`process_id`) REFERENCES `process_types` (`process_id`) ON DELETE CASCADE;

--
-- Constraints for table `process_steps`
--
ALTER TABLE `process_steps`
  ADD CONSTRAINT `process_steps_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `process_paths` (`path_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
