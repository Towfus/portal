-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 08:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `telephone_number` varchar(50) DEFAULT NULL,
  `mobile_number` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `offers_elementary` tinyint(1) DEFAULT 0,
  `offers_jhs` tinyint(1) DEFAULT 0,
  `offers_shs` tinyint(1) DEFAULT 0,
  `offers_sped` tinyint(1) DEFAULT 0,
  `elementary_grades` varchar(50) DEFAULT NULL,
  `jhs_grades` varchar(50) DEFAULT NULL,
  `shs_grades` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `school_id`, `name`, `address`, `barangay`, `city`, `province`, `telephone_number`, `mobile_number`, `email`, `offers_elementary`, `offers_jhs`, `offers_shs`, `offers_sped`, `elementary_grades`, `jhs_grades`, `shs_grades`) VALUES
(1, 424462, 'A1.W.M. Learning Academy Inc.', 'Brgy.Mangahan, General Trias City, Cavite', 'Manggahan', 'General Trias City', 'Cavite', '(046) 413-1172', NULL, 'depedcavite.a1wmacademy@gmail.com', 0, 1, 1, 0, '', 'Grade 7, Grade 8, Grade 9, Grade 10', 'Grade 11, Grade 12'),
(2, 424009, 'Academy of Saint John - La Salle Green Hills Super...', 'Sta. Clara, Gen. Trias, Cavite', 'Sta. Clara', 'General Trias City', 'Cavite', NULL, NULL, 'asj_lsgh@yahoo.com', 0, 0, 0, 0, NULL, NULL, NULL),
(3, 424463, 'Ace Bloomers Academy, Inc.', 'South Square Village, Brgy. Pasong Kawayan 2, Gene...', 'Pasong Kawayan 2', 'General Trias City', 'Cavite', NULL, '09190939752', 'rowenayulopez@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1 - 6', '', ''),
(4, 424872, 'Airulas Integrated School, Inc.', 'Belvedere Towne III, Pasong Kawayan II, Genearal T...', 'Pasong Kawayan II', 'General Trias City', 'Cavite', NULL, '09193171994', 'rsc13_flusa05@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(5, 410398, 'AMA COLLEGE-CAVITE', 'Biclatan, Genearal Trias City, Cavite', 'Biclatan', 'General Trias City', 'Cavite', NULL, NULL, 'jdbarit@amaes.edu.ph', 0, 0, 1, 0, NULL, NULL, 'Grade 11–12'),
(6, 424340, 'Amazing Learners and Molders Learning Center Inc.', 'Grand Riverside Subd., Pasong Camachile I, Geneara...', 'Pasong Camachile I', 'General Trias City', 'Cavite', NULL, '09178620190', 'amazinglearners.molders@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(7, 402068, 'Angelicum Immanuel Montessori of Cavite, Inc.', 'Phase 1, Tierra Nevada Subd., San Francisco, Genea...', 'San Francisco', 'General Trias City', 'Cavite', '(046) 5382509', NULL, 'angelicumgentri@yahoo.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(8, 408568, 'Bernice Learning Center Cavite, Inc.', 'Biclatan, Genearal Trias City, Cavite', 'Biclatan', 'General Trias City', 'Cavite', NULL, '09281513588', 'bernicelearningcenter@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–3', NULL, NULL),
(9, 402069, 'Bethel Academy of Gen. Trias Cavite', '9053 Pagasa St., Navarro, Genearal Trias City, Cav...', 'Navarro', 'General Trias City', 'Cavite', NULL, '09066812692', 'rizzapearltrias.bethelacademy@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(10, 410408, 'Bethel Academy of Gen. Trias Cavite Inc.', '237 Sta. Clara, General Trias City, Cavite', 'Sta. Clara', 'General Trias City', 'Cavite', NULL, '09066812692', 'rizzapearltrias.bethelacademy@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(11, 424191, 'Blessed Maria Cristina Brando School', '419 Arnaldo Highway, Brgy. Santiago, Genearal Tria...', 'Santiago', 'General Trias City', 'Cavite', '(046) 412-56-41', '09989', 'bmcbs_m@yahoo.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(12, 408557, 'Brilliant Heights Scholastic Montessori Inc.', 'Bella Vista Subdivision, Brgy. Santiago, Genearal ...', 'Santiago', 'General Trias City', 'Cavite', '(043) 443 0570', '09357', 'brilliantheightsinc@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(13, 424257, 'Castle of Young Mind Learning School', 'Parklane Country Homes, Genearal Trias City, Cavit...', 'San Francisco', 'General Trias City', 'Cavite', NULL, NULL, 'jovelle.nazareno@yahoo.com', 1, 0, 0, 0, 'Kinder only', NULL, NULL),
(14, 424499, 'Centre D\'Etude De Mahershalalhashbaz Emmanuel Inc.', 'Grand Riverside Subd., Pasong Camachile I, Geneara...', 'Pasong Camachile I', 'General Trias City', 'Cavite', NULL, '09284713048', 'ectanada@yahoo.com', 1, 0, 0, 0, 'Kinder only', NULL, NULL),
(15, 424771, 'Christian Yra Learning Center, Inc.', 'Pasong Camachile II, General Trias City', 'Pasong Camachile II', 'General Trias City', 'Cavite', NULL, '09228694740', 'strongerbelle@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(16, 402070, 'Claremont School of Gen. Trias Cavite, Inc.', 'Grand Riveside Subd., Pasong Camachile I, Genearal...', 'Pasong Camachile I', 'General Trias City', 'Cavite', '(046) 887-2956', '09175', 'claremont_school@yahoo.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(17, 402072, 'Colegio De Francesca, Inc.', 'Sec. 1 Block 37 Lot 41-B Sunnybrooke 2 San Francis...', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09498805801', 'depedcavite.cdf@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(18, 424011, 'Colegio de San Francisco', 'Sampalucan, Gen. Trias, Cavite', 'Sampalucan', 'General Trias City', 'Cavite', NULL, NULL, 'colegiodesanfrancisco2003@yahoo.com.ph', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(19, 402071, 'Corinthian Academy of Cavite in Gen. Trias Inc.', 'Gov. Ferrer Ave. cor. D. Mojica St. Gen. Trias Cit...', 'Poblacion', 'General Trias City', 'Cavite', NULL, NULL, 'corinthian_academy@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(20, 410407, 'Cosmopolitan School of the Philippines Inc.', 'Brgy. Buenavista I, Gen. Trias City, Cavite', 'Buenavista I', 'General Trias City', 'Cavite', NULL, NULL, 'cosmopolitanschoolofthephil@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–3', NULL, NULL),
(21, 409151, 'Del Rosario Christian Institute of Gen. Trias Inc....', 'Bella Vista Subd., Brgy. Santiago, Gen. Trias City', 'Santiago', 'General Trias City', 'Cavite', NULL, NULL, 'marjesmylene18@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(22, 408038, 'Del Rosario Christian Institute of Gen.Trias', 'Pasong Kawayan II, General Trias City', 'Pasong Kawayan II', 'General Trias City', 'Cavite', NULL, NULL, 'marjesmylene18@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(23, 409281, 'Dream Best Academy and Learning Institution Corp.', 'Blk. 1 Cavite Economic Zone II, Gen. Trias City', 'Buenavista II', 'General Trias City', 'Cavite', NULL, NULL, 'hrdgroup-envi@hrd-s.com', 1, 0, 0, 0, 'Kinder only', NULL, NULL),
(24, 402073, 'Evangel Christian Educational Center, Inc.', '#074 General Trias-Amadeo Rd., Buenavista III, Cit...', 'Buenavista III', 'General Trias City', 'Cavite', NULL, NULL, 'ecec.edu@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(25, 402075, 'Fiat Lux Academe of Gen. Trias, Cavite', 'Block 28 Lots 1&9 Phase 1 Grand Riverside Subd., P...', 'Pasong Camachile 1', 'General Trias City', 'Cavite', '(046) 887-2646', NULL, 'fla.gentri@gmail.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(26, 424342, 'Franclouie Learning Center Inc.', 'Block 23 Lot 1 KPNP Santiago General Trias City Ca...', 'Santiago', 'General Trias City', 'Cavite', NULL, NULL, 'franclouielearningcenter@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(27, 424343, 'Gateway Integrated School of Science & Technology ...', 'Sitio Tinungan, Bgy. Manggahan, General Trias, Cav...', 'Manggahan', 'General Trias City', 'Cavite', NULL, NULL, 'gatewayintegratedschool2009@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(28, 409011, 'Glorious God\'s Family Christian School, Inc.', 'Bella Vista Subd.,Brgy. Santiago, General Trias, C...', 'Santiago', 'General Trias City', 'Cavite', NULL, NULL, 'arnel.shin@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(29, 424283, 'Governor\'s Hills Science School, Inc.', 'Governor\'s Hills Subdivision, Biclatan, General Tr...', 'Biclatan', 'General Trias City', 'Cavite', '(046) 484-8975', NULL, 'ghss.govhills@gmail.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(30, 424284, 'Haven of Virtue and Excellence Academy, Inc.', 'Camachile Subd., Pasong Camachile 1, Gen. Trias, C...', 'Pasong Camachile 1', 'General Trias City', 'Cavite', NULL, '09752607394', 'hvea2004@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(31, 402077, 'Haven of Wisdom Academy Inc.', 'Mary Cris Complex,Pasong Camachile II General Tria...', 'Pasong Camachile II', 'General Trias City', 'Cavite', '(046) 850-0981', NULL, 'havenofwisdomacademy2021@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(32, 424779, 'Hillcrest Periwinkle School, Inc.', 'San Juan 1, General Trias City, Cavite', 'San Juan 1', 'General Trias City', 'Cavite', '046-887-2368', NULL, 'hillcrestperiwinkle.gentrias@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(33, 424162, 'John Isabel Learning Center, Inc.', 'Camachile Subd. Pasong Camachile 1, General Trias ...', 'Pasong Camachile 1', 'General Trias City', 'Cavite', NULL, NULL, 'johnisabellearningcenter@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(34, 424875, 'Jubilee Christian Faith Academy - Gen. Trias Inc.', 'Bgry. Sitio Alangilan St. Open Canal Road Pasong C...', 'Pasong Camacile 2', 'General Trias City', 'Cavite', NULL, '09235520645', 'depedcavite.jcfagentri@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(35, 424874, 'Kairos Academe Learning Center Inc.', 'Blk. 18 Lot 29 Ph. 1 San Jose Townhomes, Pasong Ca...', 'Pasong Camachille II', 'General Trias City', 'Cavite', NULL, '09561866575', 'pastranamariasconcepcion@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–4', NULL, NULL),
(36, 409277, 'King Arthur Academy of Cavite, Inc.', 'Pamayanang Maliksi Pasong Kawayan II General Trias...', 'Pasong Kawayan II', 'General Trias City', 'Cavite', NULL, '09568008183', 'kingarthuracademyinc.annex@gmail.com', 1, 0, 0, 0, 'Grade 1–6', NULL, NULL),
(37, 424345, 'King Solomon Academy of Cavite Inc.', '108 San Juan I, City of Gen. Trias, Cavite', 'San Juan I', 'General Trias City', 'Cavite', NULL, NULL, 'depalmamaricar@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(38, 424117, 'La Salette Montessori Multiple Intelligences Learn...', 'San Francisco, General Trias City, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, NULL, 'lsmmilc03@yahoo.com', 0, 0, 0, 0, NULL, NULL, NULL),
(39, 402082, 'Living Faith School of Cavite, Inc.', 'Marycris Complex, Camachile II, Gen. Trias City', 'Camachile II', 'General Trias City', 'Cavite', NULL, '09754462340', 'livingfaithschool@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(40, 402083, 'Lois Faith Christian Academy', 'Arnaldo St., Manggahan, General Trias City, Cavite', 'Manggahan', 'General Trias City', 'Cavite', NULL, NULL, 'loisfaith1999@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(41, 408650, 'Lyceum de Abuid Inc.', 'Tahanang Yaman Homes Buenavista 2 General Trias Ci...', 'Buenavista 2', 'General Trias City', 'Cavite', NULL, NULL, 'sheryl.abuid@yahoo.com', 1, 0, 0, 0, 'Kinder, Grade 1–4', NULL, NULL),
(42, 424285, 'Lyceum of the Philippines, Inc.', 'Governor\'s Drive, General Trias City, Cavite', 'Manggahan', 'General Trias City', 'Cavite', '(046) 481-1400', '(046)', 'mhar.bayot@lpu.edu.ph', 0, 1, 1, 0, NULL, 'Grade 7–10', 'Grade 11–12'),
(43, 409747, 'Mindful School Of Berlyn Achievers Inc.', 'San Francisco, General Trias City, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09267384119', 'merlbill061114@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(44, 402088, 'Miriam Carmeli School, Inc.', 'Tierra Nevada, SF, Gen. Trias City', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09214687389', 'miriam.carmeli.school@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(45, 402086, 'Mother Theresa School (Gen. Trias, Cavite), Inc.', 'San Francisco General Trias City, Cavite', 'San Francisco', 'General Trias City', 'Cavite', '(046) 402-2459', '09475', 'mtsgentri@gmail.com', 1, 1, 1, 1, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(46, 410614, 'Nataniel Castle Learning Center Inc.', 'Kanutuhan ll, Brgy. San Francisco, Generial Trias ...', 'San Francisco', 'General Trias City', 'Cavite', NULL, NULL, 'natanielclc@gmail.com', 1, 0, 0, 1, 'Kinder, Grade 1–4', NULL, NULL),
(47, 402087, 'New Buenavista Academy, Inc', 'Manggahan, General Trias City, Cavite', 'Manggahan', 'General Trias City', 'Cavite', '(046) 409 1731', '09063', 'primadeldeseo@yahoo.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(48, 424238, 'Nineveh Learning Center, Inc.', 'Brookeside Lane, San Francisco, General Trias, Cav...', 'San Francisco', 'General Trias City', 'Cavite', '(046) 489--8305', '09', 'nineveh_academy@yahoo.com.ph', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(49, 424118, 'Our Lady of Remedios College of Science & Technolo...', 'Buenavista II, Gen. Trias City Cavite', 'Buenavista II', 'General Trias City', 'Cavite', NULL, NULL, 'olrms05@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(50, 424830, 'Our Lady of the Holy Rosary Educational Foundation...', 'Pasong Kawayan I, Gen. Trias City, Cavite', 'Pasong Kawayan I', 'General Trias City', 'Cavite', '(046) 4245131', NULL, 'imeencarnacion20@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(51, 424192, 'Potters\' Hand Academy of Gen. Trias, Inc.', 'San Francisco, Gen. Trias, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, NULL, 'pottershandacademy@yahoo.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7', NULL),
(52, 424465, 'Potter\'s Heart of Wisdom Academy, INC.', 'Brgy. Navarro City of General Trias, Cavite', 'Navarro', 'General Trias City', 'Cavite', '(046)-4318651', '0967 0', 'pottersheartofwisdom@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(53, 424820, 'Saint Charles Borromeo Integrated School', 'San Francisco, General Trias City, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, NULL, 'scborromeo_ischool@yahoo.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–8', NULL),
(54, 402093, 'Saint Francis School', 'San Juan 1, General Trias City, Cavite', 'San Juan 1', 'General Trias City', 'Cavite', NULL, '09163769166', 'tenerefev@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(55, 408777, 'Saint Gilbert Myra Academe Inc.', 'Bella Vista Homes, Brgy. Santiago, Gen. Trias City', 'Santiago', 'General Trias City', 'Cavite', '(046) 4040467', '0927-3', 'st.gilbert.myra2016@gmail.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(56, 424466, 'Samuel Christian College of Gen. Trias, Inc.', 'Navarro, City of Gen. Trias, Cavite', 'Navarro', 'General Trias City', 'Cavite', '(046) 402-0725', '0916-', 'samuelchristiancollegegti@gmail.com', 0, 1, 1, 0, NULL, 'Grade 7–10', 'Grade 11–12'),
(57, 402094, 'San Francisco de Malabon Parochial School, Inc.', 'Poblacion, City of Gen Trias, Cavite', 'Poblacion', 'General Trias City', 'Cavite', '(046) 437-2534', '09053', 'sdemalabon@yahoo.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12'),
(58, 424163, 'Saving Grace Christian School in General Trias Cav...', 'Bgy. San Francisco Gen. Trias, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09178934205', 'bethciudad.savinggrace@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(59, 424888, 'SFS Aid of Angels Learning Center Inc.', '#37 96th Street Gen. Trias City, Cavite 4107', 'Unknown', 'General Trias City', 'Cavite', '(046) 424-9680', '0917-', 'aidofangels@yahoo.com', 0, 0, 0, 1, NULL, NULL, NULL),
(60, 424821, 'Shepherd\'s Haven Christian Academy of Cavite (SHCA...', 'Mary Cris Complex, PasCam 2, General Trias City, C...', 'PasCam 2', 'General Trias City', 'Cavite', '(046) 431-1960', '0995-', 'shcaci.cavite2013@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(61, 410606, 'Southville Monarchs International School', 'Grand Parklane Blk. 70A Lot 1 Antel Grand Village,...', 'Bacao II', 'General Trias City', 'Cavite', NULL, NULL, 'rahima_ona@southville.edu.ph', 0, 0, 0, 0, NULL, NULL, NULL),
(62, 402092, 'St. Alloysius Gonzaga Integrated School of Cavite,...', 'Sunny Brooke 1, Brgy. San Francisco, General Trias...', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09282155626', 'sagis.bella.vista@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(63, 410160, 'St. Carmen Salles School, Cavite Inc.', 'Gov. Ferrer Drive, Buenavista II, General Trias, C...', 'Buenavista II', 'General Trias City', 'Cavite', NULL, NULL, 'scssc2019@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(64, 408874, 'St. Clare of Assisi Special Education School, Inc.', 'Brgy. Sta. Clara, General Trias City, Cavite', 'Sta. Clara', 'General Trias City', 'Cavite', NULL, '09178638291', 'st.clare.spedschool@gmail.com', 0, 0, 0, 1, NULL, NULL, NULL),
(65, 408626, 'St. Edward Integrated School Foundation-Cavite Inc...', 'Kensington 2, Lancaster New City, Brgy. Navarro, G...', 'Navarro', 'General Trias City', 'Cavite', NULL, NULL, 'jtcabalo@ses.edu.ph', 0, 0, 0, 0, NULL, NULL, NULL),
(66, 402095, 'Star Blossoms Academy', 'Parklane Country Homes, Brgy. Santiago,City of Gen...', 'Santiago', 'General Trias City', 'Cavite', NULL, NULL, 'sba@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(67, 424467, 'Teruah Early Childhood Care Center, Inc.', 'Pasong Kawayan II, General Trias City, Cavite', 'Pasong Kawayan II', 'General Trias City', 'Cavite', NULL, '09158757110', 'depedcavite.teruaheccci@gmail.com', 1, 0, 0, 0, 'Kinder only', NULL, NULL),
(68, 424500, 'The Centennial Academy of the Blessed Trinity-Cavi...', 'Bgy. Sampalucan, City of General Trias, Cavite 410...', 'Sampalucan', 'General Trias City', 'Cavite', '046-450-0805', NULL, 'depedgentri.cabt2012@gmail.com', 0, 0, 0, 0, NULL, NULL, NULL),
(69, 424074, 'The First Uniting Christian School', 'Pasong Camachile II, General Trias City, Cavite', 'Pasong Camachile II', 'General Trias City', 'Cavite', NULL, '09087056389', 'mctfucs2000@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–6', NULL, NULL),
(70, 408275, 'The Palmridge School Inc.', 'Brgy. Santiago, Arnaldo Highway, City of General T...', 'Santiago', 'General Trias City', 'Cavite', '(046) 440 3882', '09190', 'ms.lissa@thepalmridgeschool.com', 1, 1, 0, 0, 'Kinder, Grade 1–6', 'Grade 7–10', NULL),
(71, 409143, 'Whizzkid Christian Academy Inc.', 'Pamayanang Maliksi, Pasong Kawayan 2,General Trias...', 'Pasong Kawayan 2', 'General Trias City', 'Cavite', NULL, '09776250862', 'whizzkidchristianacademy@gmail.com', 1, 0, 0, 0, 'Kinder, Grade 1–5', NULL, NULL),
(72, 424194, 'Young Ji International School, Inc.', 'San Francisco, General Trias City, Cavite', 'San Francisco', 'General Trias City', 'Cavite', NULL, '09171755065', 'young_ji_office@yahoo.com', 1, 1, 1, 0, 'Kinder, Grade 1–6', 'Grade 7–10', 'Grade 11–12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
