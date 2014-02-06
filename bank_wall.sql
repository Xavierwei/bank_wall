-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 06, 2014 at 05:06 AM
-- Server version: 5.5.33
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bank_wall`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `content` text,
  `datetime` int(11) DEFAULT NULL,
  `nid` int(11) DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`cid`, `uid`, `content`, `datetime`, `nid`) VALUES
(2, 12, 'I am at #Starbar and work for #Shanghai Company', NULL, 1),
(3, 12, 'I am at #Starbar and work for #Shanghai Company', NULL, 1),
(4, 12, 'I am at #Starbar and work for #Shanghai Company', 1388271018, 1),
(5, 12, 'I am at #Starbar and work for #Shanghai Company', 1388271027, 1),
(6, 12, 'test', 1389423952, 21),
(7, 12, 'fsdf', 1389425642, 21),
(8, 12, 'fsdf', 1389425696, 21),
(9, 12, 'OK!', 1389425860, 21),
(10, 12, 'fds', 1389425941, 21),
(11, 12, 'fsdf', 1389426073, 21),
(12, 12, 'fsdf', 1389426074, 21),
(13, 12, 'fsdf', 1389426108, 21),
(14, 12, 'fsdf', 1389426108, 21),
(15, 12, 'fsd', 1389426150, 21),
(16, 12, 'ccc', 1389450693, 25),
(17, 12, 'fjkdsjfkd', 1391144305, 193),
(18, 12, 'fdsfdfdf', 1391144311, 204),
(19, 12, 'fdeeeff', 1391144319, 204),
(20, 12, 'fsdfdsf', 1391144656, 204),
(21, 12, 'fsdfdsf', 1391144662, 204),
(22, 12, 'ffffff\n', 1391147822, 204),
(23, 12, '33333', 1391148177, 204),
(24, 12, '3323', 1391148186, 204),
(25, 12, 'fdsfdsf', 1391148226, 204),
(26, 12, 'dsfdfdsf', 1391148239, 204),
(27, 12, 'fsdfdf', 1391148267, 204),
(28, 37, 'fsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdffsdf', 1391170897, 204),
(29, 12, 'fdsf', 1391171028, 203),
(30, 12, 'sdfdsfdsfd', 1391171035, 203),
(31, 12, 'sdfdsfdsfdfsd', 1391171039, 203),
(32, 12, 'sdfdsfdsfdfsd', 1391171043, 203),
(33, 12, 'ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff', 1391171235, 203),
(34, 12, 'ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffdffafdsfdsf', 1391171243, 203),
(35, 12, 'fdsf', 1391171611, 201),
(36, 12, 'fdsf', 1391171615, 201),
(37, 12, 'fdsf', 1391171618, 201),
(38, 12, 'fdsf', 1391171621, 201),
(46, 12, 'ok!', 1391305381, 214),
(49, 12, 'dsfdsfdsf', 1391305444, 214),
(50, 12, 'fsdfdsf', 1391357309, 204),
(51, 12, 'fsdfdsf', 1391357309, 204),
(52, 12, 'dsfsdfdf', 1391357403, 217);

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(45) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `flag_icon` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=78 ;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`country_id`, `country_name`, `code`, `flag_icon`) VALUES
(1, 'South Africa', 'south africa', 'south africa'),
(2, 'Albania', 'albania', 'albania'),
(3, 'Algeria', 'algeria', 'algeria'),
(4, 'Germany', 'germany', 'germany'),
(5, 'Saudi', 'saudi', 'saudi'),
(6, 'Arabia', 'arabia', 'arabia'),
(7, 'Argentina', 'argentina', 'argentina'),
(8, 'Australia', 'australia', 'australia'),
(9, 'Austria', 'austria', 'austria'),
(10, 'Bahamas', 'bahamas', 'bahamas'),
(11, 'Belgium', 'belgium', 'belgium'),
(12, 'Benin', 'benin', 'benin'),
(13, 'Brazil', 'brazil', 'brazil'),
(14, 'Bulgaria', 'bulgaria', 'bulgaria'),
(15, 'Burkina Faso', 'burkina faso', 'burkina faso'),
(16, 'Canada', 'canada', 'canada'),
(17, 'Chile', 'chile', 'chile'),
(18, 'China', 'china', 'china'),
(19, 'Cyprus', 'cyprus', 'cyprus'),
(20, 'Korea', 'korea', 'korea'),
(21, 'Republic of Ivory Coast', 'republic of ivory coast', 'republic of ivory coast'),
(22, 'Croatia', 'croatia', 'croatia'),
(23, 'Denmark', 'denmark', 'denmark'),
(24, 'Egypt', 'egypt', 'egypt'),
(25, 'United Arab Emirates', 'united arab emirates', 'united arab emirates'),
(26, 'Spain', 'spain', 'spain'),
(27, 'Estonia', 'estonia', 'estonia'),
(28, 'USA', 'usa', 'usa'),
(29, 'Finland', 'finland', 'finland'),
(30, 'France', 'france', 'france'),
(31, 'Georgia', 'georgia', 'georgia'),
(32, 'Ghana', 'ghana', 'ghana'),
(33, 'Greece', 'greece', 'greece'),
(34, 'Guinea', 'guinea', 'guinea'),
(35, 'Equatorial Guinea', 'equatorial guinea', 'equatorial guinea'),
(36, 'Hungary', 'hungary', 'hungary'),
(37, 'India', 'india', 'india'),
(38, 'Ireland', 'ireland', 'ireland'),
(39, 'Italy', 'italy', 'italy'),
(40, 'Japan', 'japan', 'japan'),
(41, 'Jordan', 'jordan', 'jordan'),
(42, 'Latvia', 'latvia', 'latvia'),
(43, 'Lebanon', 'lebanon', 'lebanon'),
(44, 'Lithuania', 'lithuania', 'lithuania'),
(45, 'Luxembourg', 'luxembourg', 'luxembourg'),
(46, 'Macedonia', 'macedonia', 'macedonia'),
(47, 'Madagascar', 'madagascar', 'madagascar'),
(48, 'Morocco', 'morocco', 'morocco'),
(49, 'Mauritania', 'mauritania', 'mauritania'),
(50, 'Mexico', 'mexico', 'mexico'),
(51, 'Moldova', 'moldova', 'moldova'),
(52, 'Republic of Montenegro', 'republic of montenegro', 'republic of montenegro'),
(53, 'Norway', 'norway', 'norway'),
(54, 'New Caledonia', 'new caledonia', 'new caledonia'),
(55, 'Panama', 'panama', 'panama'),
(56, 'Netherlands', 'netherlands', 'netherlands'),
(57, 'Peru', 'peru', 'peru'),
(58, 'Poland', 'poland', 'poland'),
(59, 'Portugal', 'portugal', 'portugal'),
(60, 'Reunion', 'reunion', 'reunion'),
(61, 'Romania', 'romania', 'romania'),
(62, 'UK', 'uk', 'uk'),
(63, 'Russian Federation', 'russian federation', 'russian federation'),
(64, 'Senegal', 'senegal', 'senegal'),
(65, 'Serbia', 'serbia', 'serbia'),
(66, 'Singapore', 'singapore', 'singapore'),
(67, 'Slovakia', 'slovakia', 'slovakia'),
(68, 'Slovenia', 'slovenia', 'slovenia'),
(69, 'Sweden', 'sweden', 'sweden'),
(70, 'Switzerland', 'switzerland', 'switzerland'),
(71, 'Chad', 'chad', 'chad'),
(72, 'Czech Republic', 'czech republic', 'czech republic'),
(73, 'Tunisia', 'tunisia', 'tunisia'),
(74, 'Turkey', 'turkey', 'turkey'),
(75, 'Ukraine', 'ukraine', 'ukraine'),
(76, 'Uruguay', 'uruguay', 'uruguay'),
(77, 'Vietnam', 'vietnam', 'vietnam');

-- --------------------------------------------------------

--
-- Table structure for table `flag`
--

CREATE TABLE `flag` (
  `flag_id` int(15) NOT NULL AUTO_INCREMENT,
  `nid` int(11) DEFAULT '0',
  `uid` int(15) DEFAULT NULL,
  `datetime` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT '0',
  `comment_nid` int(11) DEFAULT '0',
  PRIMARY KEY (`flag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `flag`
--

INSERT INTO `flag` (`flag_id`, `nid`, `uid`, `datetime`, `cid`, `comment_nid`) VALUES
(18, 204, 12, 1391165802, 0, NULL),
(19, 214, 12, 1391266459, 0, NULL),
(20, 0, 12, 1391269647, 39, NULL),
(21, 212, 12, 1391270483, 0, NULL),
(22, 211, 12, 1391270509, 0, NULL),
(23, 0, 12, 1391270545, 18, 204),
(24, 0, 12, 1391270906, 19, 204),
(25, 0, 12, 1391272003, 21, 204);

-- --------------------------------------------------------

--
-- Table structure for table `like`
--

CREATE TABLE `like` (
  `like_id` int(15) NOT NULL AUTO_INCREMENT,
  `nid` int(15) DEFAULT NULL,
  `uid` int(15) DEFAULT NULL,
  `datetime` int(15) DEFAULT NULL,
  PRIMARY KEY (`like_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=125 ;

--
-- Dumping data for table `like`
--

INSERT INTO `like` (`like_id`, `nid`, `uid`, `datetime`) VALUES
(17, 185, 12, 1389499316),
(18, 122, 12, 1389499469),
(42, 206, 12, 1391303401),
(88, 212, 12, 1391347310),
(89, 203, 12, 1391347392),
(90, 194, 12, 1391347429),
(91, 192, 12, 1391347447),
(94, 191, 12, 1391347579),
(95, 190, 12, 1391347610),
(97, 197, 12, 1391347915),
(98, 189, 12, 1391347935),
(99, 186, 12, 1391347965),
(100, 183, 12, 1391348024),
(101, 184, 12, 1391348120),
(102, 182, 12, 1391348218),
(103, 181, 12, 1391348252),
(104, 177, 12, 1391348337),
(105, 187, 12, 1391348385),
(106, 178, 12, 1391348590),
(107, 176, 12, 1391348621),
(108, 193, 12, 1391348647),
(117, 204, 12, 1391349070),
(118, 170, 12, 1391349246),
(119, 171, 12, 1391349346),
(120, 163, 12, 1391349373),
(123, 211, 12, 1391350129),
(124, 214, 12, 1391350192);

-- --------------------------------------------------------

--
-- Table structure for table `node`
--

CREATE TABLE `node` (
  `nid` int(15) NOT NULL AUTO_INCREMENT,
  `uid` int(15) DEFAULT NULL,
  `country_id` int(15) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `datetime` int(11) DEFAULT NULL,
  `hashtag` varchar(200) DEFAULT NULL,
  `description` text,
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=245 ;

--
-- Dumping data for table `node`
--

INSERT INTO `node` (`nid`, `uid`, `country_id`, `type`, `file`, `datetime`, `hashtag`, `description`, `status`) VALUES
(62, 12, 1, 'photo', '/uploads/p62.jpg', 1389489709, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(63, 12, 1, 'photo', '/uploads/p63.jpg', 1389489716, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(64, 12, 1, 'photo', '/uploads/p64.jpg', 1389489721, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(65, 12, 1, 'photo', '/uploads/p65.jpg', 1389489727, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(66, 12, 1, 'photo', '/uploads/p66.jpg', 1389489731, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(67, 12, 1, 'photo', '/uploads/p67.jpg', 1389489736, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(68, 12, 1, 'photo', '/uploads/p68.jpg', 1389489742, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(69, 12, 1, 'photo', '/uploads/p69.jpg', 1389489747, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(70, 12, 1, 'photo', '/uploads/p70.jpg', 1389489751, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(71, 12, 1, 'photo', '/uploads/p71.jpg', 1389489755, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(72, 12, 1, 'photo', '/uploads/p72.jpg', 1389489760, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(73, 12, 1, 'photo', '/uploads/p73.jpg', 1389489765, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(74, 12, 1, 'photo', '/uploads/p74.jpg', 1389489770, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(75, 12, 1, 'photo', '/uploads/p75.jpg', 1389489775, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(76, 12, 1, 'photo', '/uploads/p76.jpg', 1389489779, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(77, 12, 1, 'photo', '/uploads/p77.jpg', 1389489784, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(78, 12, 1, 'photo', '/uploads/p78.jpg', 1389489789, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(79, 12, 1, 'photo', '/uploads/p79.jpg', 1389489796, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(80, 12, 1, 'photo', '/uploads/p80.jpg', 1389489799, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(81, 12, 1, 'photo', '/uploads/p81.jpg', 1389489803, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(82, 12, 1, 'photo', '/uploads/p82.jpg', 1389489806, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(83, 12, 1, 'photo', '/uploads/p83.jpg', 1389489809, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(84, 12, 1, 'photo', '/uploads/p84.jpg', 1389489813, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(85, 12, 1, 'photo', '/uploads/p85.jpg', 1389489817, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(86, 12, 1, 'photo', '/uploads/p86.jpg', 1389489821, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(87, 12, 1, 'photo', '/uploads/p87.jpg', 1389489824, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(88, 12, 1, 'photo', '/uploads/p88.jpg', 1389489831, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(89, 12, 1, 'photo', '/uploads/p89.jpg', 1389489835, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(90, 12, 1, 'photo', '/uploads/p90.jpg', 1389489838, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(91, 12, 1, 'photo', '/uploads/p91.jpg', 1389489843, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(92, 12, 1, 'photo', '/uploads/p92.jpg', 1389489846, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(93, 12, 1, 'photo', '/uploads/p93.jpg', 1389489849, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(94, 12, 1, 'photo', '/uploads/p94.jpg', 1389489854, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(95, 12, 1, 'photo', '/uploads/p95.jpg', 1389489857, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(96, 12, 1, 'photo', '/uploads/p96.jpg', 1389489862, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(97, 12, 1, 'photo', '/uploads/p97.jpg', 1389489865, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(98, 12, 1, 'photo', '/uploads/p98.jpg', 1389489869, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(99, 12, 1, 'photo', '/uploads/p99.jpg', 1389489876, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(100, 12, 1, 'photo', '/uploads/p100.jpg', 1389489880, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(101, 12, 1, 'photo', '/uploads/p101.jpg', 1389489883, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(102, 12, 1, 'photo', '/uploads/p102.jpg', 1389489887, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(103, 12, 1, 'photo', '/uploads/p103.jpg', 1389489890, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(104, 12, 1, 'photo', '/uploads/p104.jpg', 1389489894, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(105, 12, 1, 'photo', '/uploads/p105.jpg', 1389489897, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(106, 12, 1, 'photo', '/uploads/p106.jpg', 1389489900, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(107, 12, 1, 'photo', '/uploads/p107.jpg', 1389489904, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(108, 12, 1, 'photo', '/uploads/p108.jpg', 1389489908, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(109, 12, 1, 'photo', '/uploads/p109.jpg', 1389489912, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(110, 12, 1, 'photo', '/uploads/p110.jpg', 1389490088, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(111, 12, 1, 'photo', '/uploads/p111.jpg', 1389490093, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(112, 12, 1, 'photo', '/uploads/p112.jpg', 1389490096, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(113, 12, 1, 'photo', '/uploads/p113.jpg', 1389490099, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(114, 12, 1, 'photo', '/uploads/p114.jpg', 1389490103, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(115, 12, 1, 'photo', '/uploads/p115.jpg', 1389490106, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(116, 12, 1, 'photo', '/uploads/p116.jpg', 1389490109, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(117, 12, 1, 'photo', '/uploads/p117.jpg', 1389490113, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(118, 12, 1, 'photo', '/uploads/p118.jpg', 1389490116, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(119, 12, 1, 'photo', '/uploads/p119.jpg', 1389490120, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(120, 12, 1, 'photo', '/uploads/p120.jpg', 1389490124, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(121, 12, 1, 'video', '/uploads/v121.mp4', 1389490986, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(122, 12, 1, 'video', '/uploads/v122.mp4', 1389491072, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(123, 12, 1, 'video', '/uploads/v123.mp4', 1389491201, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(124, 12, 1, 'video', '/uploads/v124.mp4', 1389491249, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(125, 12, 1, 'video', '/uploads/v125.mp4', 1389493079, 'a:0:{}', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.', 1),
(155, 12, 1, 'photo', '/uploads/p155.jpg', 1389496804, 'a:0:{}', NULL, 1),
(156, 12, 1, 'photo', '/uploads/p156.jpg', 1389496808, 'a:0:{}', NULL, 1),
(157, 12, 1, 'photo', '/uploads/p157.jpg', 1389496811, 'a:0:{}', NULL, 1),
(158, 12, 1, 'photo', '/uploads/p158.jpg', 1389496816, 'a:0:{}', NULL, 1),
(159, 12, 1, 'photo', '/uploads/p159.jpg', 1389496820, 'a:0:{}', NULL, 1),
(160, 12, 1, 'photo', '/uploads/p160.jpg', 1389715200, 'a:0:{}', NULL, 1),
(161, 12, 1, 'photo', '/uploads/p161.jpg', 1389496829, 'a:0:{}', NULL, 1),
(162, 12, 1, 'photo', '/uploads/p162.jpg', 1389496835, 'a:0:{}', NULL, 1),
(163, 12, 1, 'photo', '/uploads/p163.jpg', 1389801600, 'a:0:{}', NULL, 1),
(164, 12, 1, 'photo', '/uploads/p164.jpg', 1389801600, 'a:0:{}', NULL, 1),
(165, 12, 1, 'photo', '/uploads/p165.jpg', 1389801600, 'a:0:{}', NULL, 1),
(166, 12, 1, 'photo', '/uploads/p166.jpg', 1389496851, 'a:0:{}', NULL, 1),
(167, 12, 1, 'photo', '/uploads/p167.jpg', 1389496855, 'a:0:{}', NULL, 1),
(168, 12, 1, 'video', '/uploads/v168.mp4', 1389496865, 'a:0:{}', NULL, 1),
(169, 12, 1, 'photo', '/uploads/p169.jpg', 1389496892, 'a:0:{}', NULL, 1),
(170, 12, 1, 'photo', '/uploads/p170.jpg', 1389974400, 'a:0:{}', NULL, 1),
(171, 12, 1, 'photo', '/uploads/p171.jpg', 1389974400, 'a:0:{}', NULL, 1),
(172, 12, 1, 'photo', '/uploads/p172.jpg', 1389496904, 'a:0:{}', NULL, 1),
(173, 12, 1, 'photo', '/uploads/p173.jpg', 1389496907, 'a:0:{}', NULL, 1),
(174, 12, 1, 'photo', '/uploads/p174.jpg', 1389496916, 'a:0:{}', NULL, 1),
(175, 12, 1, 'photo', '/uploads/p175.jpg', 1389496920, 'a:0:{}', NULL, 1),
(176, 12, 1, 'photo', '/uploads/p176.jpg', 1389496931, 'a:0:{}', NULL, 1),
(177, 12, 1, 'photo', '/uploads/p177.jpg', 1389496934, 'a:0:{}', NULL, 1),
(178, 12, 1, 'photo', '/uploads/p178.jpg', 1389496943, 'a:0:{}', NULL, 1),
(179, 12, 1, 'photo', '/uploads/p179.jpg', 1389496948, 'a:0:{}', NULL, 1),
(180, 12, 1, 'photo', '/uploads/p180.jpg', 1389496953, 'a:0:{}', NULL, 1),
(181, 12, 1, 'photo', '/uploads/p181.jpg', 1389496998, 'a:0:{}', NULL, 1),
(182, 12, 1, 'photo', '/uploads/p182.jpg', 1389497012, 'a:0:{}', NULL, 1),
(183, 12, 1, 'photo', '/uploads/p183.jpg', 1389497061, 'a:0:{}', NULL, 1),
(184, 12, 1, 'photo', '/uploads/p184.jpg', 1389497080, 'a:0:{}', NULL, 1),
(185, 12, 1, 'photo', '/uploads/p185.jpg', 1389497114, 'a:0:{}', NULL, 1),
(186, 12, 1, 'photo', '/uploads/p186.jpg', 1389497134, 'a:0:{}', NULL, 1),
(187, 12, 1, 'photo', '/uploads/p187.jpg', 1389497166, 'a:0:{}', NULL, 1),
(188, 12, 1, 'photo', '/uploads/p188.jpg', 1389497240, 'a:0:{}', NULL, 1),
(189, 12, 1, 'photo', '/uploads/p189.jpg', 1389497333, 'a:0:{}', NULL, 1),
(190, 12, 1, 'photo', '/uploads/p190.jpg', 1389497404, 'a:0:{}', NULL, 1),
(191, 12, 1, 'photo', '/uploads/p191.jpg', 1389497413, 'a:0:{}', NULL, 1),
(192, 12, 1, 'photo', '/uploads/p192.jpg', 1389497464, 'a:0:{}', NULL, 1),
(193, 12, 1, 'photo', '/uploads/p193.jpg', 1389497478, 'a:0:{}', NULL, 1),
(194, 12, 1, 'photo', '/uploads/p194.jpg', 1389497500, 'a:0:{}', NULL, 1),
(197, 12, 1, 'photo', '/uploads/p197.jpg', 1390277398, 'a:0:{}', NULL, 1),
(199, 12, 1, 'video', '/uploads/v199.mp4', 1390280795, 'a:0:{}', NULL, 1),
(201, 12, 1, 'photo', '/uploads/p201.jpg', 1390281204, 'a:0:{}', NULL, 1),
(203, 12, 1, 'photo', '/uploads/p203.jpg', 1390281528, 'a:1:{i:0;s:3:"kkk";}', 'kkk', 1),
(204, 12, 1, 'photo', '/uploads/p204.jpg', 1390281606, 'a:1:{i:0;s:3:"fff";}', 'fff', 1),
(205, 12, 1, 'photo', '/uploads/p205.jpg', 1391177014, 'a:0:{}', NULL, 1),
(206, 12, 1, 'photo', '/uploads/p206.jpg', 1391177392, 'a:0:{}', NULL, 1),
(207, 12, 1, 'photo', '/uploads/p207.jpg', 1391177558, 'a:0:{}', NULL, 1),
(208, 12, 1, 'photo', '/uploads/p208.jpg', 1391177675, 'a:0:{}', NULL, 1),
(209, 12, 1, 'photo', '/uploads/p209.jpg', 1391177752, 'a:0:{}', NULL, 1),
(210, 12, 1, 'photo', '/uploads/p210.jpg', 1391177786, 'a:0:{}', NULL, 1),
(211, 12, 1, 'photo', '/uploads/p211.jpg', 1391177864, 'a:0:{}', NULL, 1),
(212, 12, 1, 'photo', '/uploads/p212.jpg', 1391177958, 'a:0:{}', NULL, 1),
(214, 12, 1, 'photo', '/uploads/p214.jpg', 1391250150, 'a:1:{i:0;s:5:"China";}', '#China dkjfkdf', 1),
(215, 12, 1, 'photo', '/uploads/p215.jpg', 1391350742, 'a:1:{i:0;s:6:"winter";}', '#winter', 1),
(216, 12, 1, 'photo', '/uploads/p216.jpg', 1391350853, 'a:1:{i:0;s:5:"green";}', '#green', 1),
(217, 12, 1, 'photo', '/uploads/p217.jpg', 1391351044, 'a:1:{i:0;s:5:"green";}', '#green', 1),
(218, 12, 1, 'photo', '/uploads/p218.jpg', 1391400015, 'a:0:{}', NULL, 1),
(219, 12, 1, 'photo', '/uploads/p219.jpg', 1391422723, 'a:2:{i:0;s:3:"fff";i:1;s:2:"ff";}', '#fff-fff #ff*ff dkdfsdf', 1),
(220, 12, 1, 'photo', '/uploads/p220.jpg', 1391422764, 'a:0:{}', NULL, 1),
(221, 12, 1, 'photo', '/uploads/p221.jpg', 1391423059, 'a:2:{i:0;s:4:"abcd";i:1;s:3:"223";}', '#abcd''ff #223', 1),
(222, 12, 1, 'photo', '/uploads/p222.jpg', 1391423274, 'a:0:{}', '#fdfkd''dfdsf', 1),
(223, 12, 1, 'photo', '/uploads/p223.jpg', 1391423611, 'a:2:{i:0;s:9:"djd''fifif";i:1;s:4:"dkdd";}', '#djd''fifif #dkdd', 1),
(224, 12, 1, 'photo', '/uploads/p224.jpg', 1391431284, 'a:0:{}', NULL, 1),
(225, 12, 1, 'photo', '/uploads/p225.jpg', 1391431575, 'a:0:{}', NULL, 1),
(226, 12, 1, 'photo', '/uploads/p226.jpg', 1391431799, 'a:0:{}', NULL, 1),
(227, 12, 1, 'photo', '/uploads/p227.jpg', 1391431858, 'a:0:{}', NULL, 1),
(228, 12, 1, 'photo', '/uploads/p228.jpg', 1391431946, 'a:0:{}', NULL, 1),
(229, 12, 1, 'photo', '/uploads/p229.jpg', 1391432013, 'a:0:{}', NULL, 1),
(230, 12, 1, 'photo', '/uploads/p230.jpg', 1391432092, 'a:0:{}', NULL, 1),
(231, 12, 1, 'photo', '/uploads/p231.jpg', 1391432126, 'a:0:{}', NULL, 1),
(232, 12, 1, 'photo', '/uploads/p232.jpg', 1391432172, 'a:0:{}', NULL, 1),
(233, 12, 1, 'photo', '/uploads/p233.jpg', 1391432279, 'a:0:{}', NULL, 1),
(234, 12, 1, 'photo', '/uploads/p234.jpg', 1391432384, 'a:0:{}', NULL, 1),
(235, 12, 1, 'photo', '/uploads/p235.jpg', 1391432463, 'a:0:{}', NULL, 1),
(236, 12, 1, 'photo', '/uploads/p236.jpg', 1391432578, 'a:0:{}', NULL, 1),
(237, 12, 1, 'photo', '/uploads/p237.jpg', 1391432637, 'a:0:{}', NULL, 1),
(238, 12, 1, 'photo', '/uploads/p238.jpg', 1391432766, 'a:0:{}', NULL, 1),
(239, 12, 1, 'photo', '/uploads/p239.jpg', 1391432821, 'a:0:{}', 'fdsfdsf', 1),
(240, 12, 1, 'video', '/uploads/v240.mp4', 1391432879, 'a:0:{}', 'fdsf', 1),
(241, 12, 1, 'photo', '/uploads/p241.jpg', 1391433907, 'a:0:{}', NULL, 1),
(242, 12, 1, 'photo', '/uploads/p242.jpg', 1391566699, 'a:0:{}', NULL, 1),
(243, 12, 1, 'photo', '/uploads/p243.jpg', 1391569392, 'a:0:{}', NULL, 1),
(244, 12, 1, 'photo', '/uploads/p244.jpg', 1391571936, 'a:0:{}', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(128) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `count` int(255) NOT NULL DEFAULT '0',
  `date` varchar(16) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag` (`tag`),
  UNIQUE KEY `tag_id` (`tag_id`),
  KEY `tag_id_2` (`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`tag_id`, `tag`, `count`, `date`) VALUES
(1, 'test', 2, ''),
(2, 'teak', 2, ''),
(3, 'test2', 0, ''),
(5, 'test3', 0, ''),
(8, 'test5', 2, ''),
(9, 'bird', 1, ''),
(10, 'lovely', 1, ''),
(11, 'red', 1, ''),
(12, 'cute', 1, ''),
(13, 'animal', 1, ''),
(14, 'china', 2, ''),
(15, 'green', 3, '1391351051'),
(17, 'natural', 0, ''),
(18, 'fdf', 0, ''),
(19, 'dffd', 0, ''),
(20, 'kkk', 0, ''),
(21, 'fff', 1, '1391422741'),
(22, 'winter', 1, ''),
(23, 'ff', 0, ''),
(24, 'abcd', 0, ''),
(25, '223', 0, ''),
(26, 'djd''fifif', 0, ''),
(27, 'dkdd', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `topday`
--

CREATE TABLE `topday` (
  `topday_id` int(15) NOT NULL AUTO_INCREMENT,
  `nid` int(15) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`topday_id`),
  UNIQUE KEY `nid` (`nid`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `topday`
--

INSERT INTO `topday` (`topday_id`, `nid`, `date`) VALUES
(6, 122, 1389481200),
(14, 163, 1389740400),
(13, 170, 1389913200),
(5, 197, 1390258800),
(3, 206, 1391122800),
(2, 214, 1391209200),
(1, 214, 1391219200);

-- --------------------------------------------------------

--
-- Table structure for table `topmonth`
--

CREATE TABLE `topmonth` (
  `topmonth_id` int(15) NOT NULL AUTO_INCREMENT,
  `nid` int(15) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`topmonth_id`),
  UNIQUE KEY `nid` (`nid`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `topmonth`
--

INSERT INTO `topmonth` (`topmonth_id`, `nid`, `date`) VALUES
(2, 122, 1388530800),
(3, 214, 1391209200);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(15) NOT NULL AUTO_INCREMENT,
  `sso_id` varchar(100) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `personal_email` varchar(200) DEFAULT NULL,
  `company_email` varchar(200) DEFAULT NULL,
  `country_id` int(15) DEFAULT NULL,
  `avatar` varchar(200) DEFAULT '',
  `role` int(11) DEFAULT '1',
  `datetime` int(11) DEFAULT NULL,
  `firstname` varchar(45) DEFAULT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `sso_id` (`sso_id`),
  UNIQUE KEY `personal_email_UNIQUE` (`personal_email`),
  UNIQUE KEY `company_email_UNIQUE` (`company_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `sso_id`, `name`, `personal_email`, `company_email`, `country_id`, `avatar`, `role`, `datetime`, `firstname`, `lastname`, `password`, `status`) VALUES
(3, '2ff43693fbce1d16e38e833b33a069f9', 'tonysh518', NULL, 'tonysh518@rnd.feide.no', NULL, '', 1, 1391654204, 'tony', 'zhu', NULL, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
