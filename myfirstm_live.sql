-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 02:08 PM
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
-- Database: `myfirstm_live`
--

-- --------------------------------------------------------

--
-- Table structure for table `behind_the_scenes`
--

CREATE TABLE `behind_the_scenes` (
  `id` bigint(255) NOT NULL,
  `season` bigint(255) NOT NULL DEFAULT 0,
  `day` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `display_note` varchar(5000) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `short_order` int(255) NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `screenshot_thumb` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `created_by` bigint(255) NOT NULL,
  `last_updated_by` bigint(255) NOT NULL,
  `last_update_ip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `behind_the_scenes_images`
--

CREATE TABLE `behind_the_scenes_images` (
  `id` bigint(255) NOT NULL,
  `bts` bigint(255) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `image_thumb` varchar(255) NOT NULL,
  `alt_text` varchar(5000) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_date` datetime NOT NULL,
  `created_by` bigint(255) NOT NULL,
  `last_update_ip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `behind_the_scenes_images`
--

INSERT INTO `behind_the_scenes_images` (`id`, `bts`, `image`, `image_thumb`, `alt_text`, `status`, `create_date`, `created_by`, `last_update_ip`) VALUES
(73, 39, 'bts_gallery_699fed81c70c68.15902643.jpg', 'thumb_bts_gallery_699fed81c70c68.15902643.jpg', '', 0, '0000-00-00 00:00:00', 624, ''),
(74, 40, 'bts_gallery_69a01f51467699.57124676.jpg', 'thumb_bts_gallery_69a01f51467699.57124676.jpg', '', 0, '0000-00-00 00:00:00', 624, '');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `display_note` varchar(5000) DEFAULT NULL,
  `cat_img` varchar(255) DEFAULT NULL,
  `cat_img_thumb` varchar(255) DEFAULT NULL,
  `short_order` int(255) DEFAULT NULL,
  `status` tinyint(10) DEFAULT NULL,
  `fee` int(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `ccav_resp`
--

CREATE TABLE `ccav_resp` (
  `id` int(255) UNSIGNED NOT NULL,
  `uid` int(255) NOT NULL,
  `order_id` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `billing_name` varchar(255) NOT NULL,
  `billing_address` text NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_state` varchar(255) NOT NULL,
  `billing_zip` varchar(255) NOT NULL,
  `billing_country` varchar(255) NOT NULL,
  `billing_tel` varchar(255) NOT NULL,
  `billing_email` varchar(255) NOT NULL,
  `billing_ip` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `msg` varchar(255) NOT NULL,
  `dt` varchar(255) NOT NULL,
  `act` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ccav_response`
--

CREATE TABLE `ccav_response` (
  `id` int(255) UNSIGNED NOT NULL,
  `uid` int(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `tracking_id` varchar(255) NOT NULL,
  `bank_ref_no` varchar(255) DEFAULT NULL,
  `order_status` varchar(1000) NOT NULL,
  `failure_message` varchar(1000) NOT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `card_name` varchar(255) DEFAULT NULL,
  `status_code` varchar(1000) NOT NULL,
  `status_message` varchar(1000) NOT NULL,
  `page_response` varchar(1000) NOT NULL,
  `currency` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `billing_name` varchar(1000) NOT NULL,
  `billing_address` varchar(1000) NOT NULL,
  `billing_city` varchar(1000) NOT NULL,
  `billing_state` varchar(1000) NOT NULL,
  `billing_zip` varchar(1000) NOT NULL,
  `billing_country` varchar(1000) NOT NULL,
  `billing_tel` varchar(255) NOT NULL,
  `billing_email` varchar(255) NOT NULL,
  `delivery_name` varchar(255) NOT NULL,
  `delivery_address` varchar(1000) NOT NULL,
  `delivery_city` varchar(1000) NOT NULL,
  `delivery_state` varchar(1000) NOT NULL,
  `delivery_zip` varchar(1000) NOT NULL,
  `delivery_country` varchar(1000) NOT NULL,
  `delivery_tel` varchar(1000) NOT NULL,
  `merchant_param1` varchar(1000) NOT NULL,
  `merchant_param2` varchar(1000) NOT NULL,
  `merchant_param3` varchar(1000) NOT NULL,
  `merchant_param4` varchar(1000) NOT NULL,
  `merchant_param5` varchar(1000) NOT NULL,
  `vault` varchar(255) NOT NULL,
  `offer_type` varchar(1000) DEFAULT NULL,
  `offer_code` varchar(1000) DEFAULT NULL,
  `discount_value` varchar(255) NOT NULL,
  `mer_amount` varchar(1000) NOT NULL,
  `eci_value` varchar(1000) NOT NULL,
  `retry` varchar(1000) DEFAULT NULL,
  `response_code` varchar(1000) NOT NULL,
  `billing_notes` varchar(1000) NOT NULL,
  `trans_date` varchar(1000) DEFAULT NULL,
  `bin_country` varchar(1000) NOT NULL,
  `dt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `CityID` int(20) NOT NULL,
  `CityName` varchar(255) NOT NULL,
  `StateID` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`CityID`, `CityName`, `StateID`) VALUES
(1, 'Adilabad', '59'),
(2, 'Anantapur', '1'),
(3, 'Chittoor', '1'),
(4, 'YSR', '1'),
(5, 'East Godavari', '1'),
(6, 'Hyderabad', '59'),
(7, 'Karimnagar', '59'),
(8, 'Khammam', '59'),
(9, 'Krishna', '1'),
(10, 'Kurnool', '1'),
(11, 'Guntur', '1'),
(12, 'Mahaboob Nagar', '59'),
(13, 'Medak', '59'),
(14, 'Nalgonda', '59'),
(15, 'Nizamabad', '59'),
(16, 'Prakasam', '1'),
(17, 'Ranga Reddy', '59'),
(18, 'Srikakulam', '1'),
(19, 'Vijayanagaram', '1'),
(20, 'Visakhapatnam', '1'),
(21, 'West Godavari', '1'),
(22, 'Warrangal', '59'),
(23, 'Changlang', '2'),
(24, 'Dibang Valley', '2'),
(25, 'East Kameng', '2'),
(26, 'East Siang', '2'),
(27, 'Lohit', '2'),
(28, 'Lower Subansiri', '2'),
(29, 'Papum Pare', '2'),
(30, 'Tawang', '2'),
(31, 'Tirap', '2'),
(32, 'Upper Siang', '2'),
(33, 'Upper Subansiri', '2'),
(34, 'West Kameng', '2'),
(35, 'West Siang', '2'),
(36, 'Barpeta', '3'),
(37, 'Bongaigaon', '3'),
(38, 'Cachar', '3'),
(39, 'Darrang', '3'),
(40, 'Dhemaji', '3'),
(41, 'Dhubri', '3'),
(42, 'Dibrugarh', '3'),
(43, 'Goalpara', '3'),
(44, 'Golaghat', '3'),
(45, 'Hailakandi', '3'),
(46, 'Jorhat', '3'),
(47, 'Kamrup', '3'),
(48, 'Karbi Anglong', '3'),
(49, 'Karimganj', '3'),
(50, 'Kokrajhar', '3'),
(51, 'Lakhimpur', '3'),
(52, 'Marigaon', '3'),
(53, 'Nagaon', '3'),
(54, 'Nalbari', '3'),
(55, 'Dima Hasao', '3'),
(56, 'Sibsagar', '3'),
(57, 'Sonitpur', '3'),
(58, 'Tinsukia', '3'),
(59, 'Araria', '4'),
(60, 'Aurangabad (BIH)', '4'),
(61, 'Banka', '4'),
(62, 'Begusarai', '4'),
(63, 'Bhabhua', '4'),
(64, 'Bhagalpur', '4'),
(65, 'Bhojpur', '4'),
(66, 'Bokaro', '10005'),
(67, 'Buxar', '4'),
(68, 'Chatra', '10005'),
(69, 'Darbhanga', '4'),
(70, 'Deoghar (JHAR)', '10005'),
(71, 'Dhanbad', '10005'),
(72, 'Dumka (Santhal Pargana)', '10005'),
(73, 'Garhwa', '10005'),
(74, 'Gaya', '4'),
(75, 'Giridih', '10005'),
(76, 'Godda', '10005'),
(77, 'Gopalganj', '4'),
(78, 'Gumla', '10005'),
(79, 'Hazaribagh', '10005'),
(80, 'Jehanabad', '4'),
(81, 'Jamui', '4'),
(82, 'Katihar', '4'),
(83, 'Khagaria', '4'),
(84, 'Kishanganj', '4'),
(85, 'Kodarma', '10005'),
(86, 'Lakshisarai', '4'),
(87, 'Lohardaga', '10005'),
(88, 'Madhepura', '4'),
(89, 'Madhubani', '4'),
(90, 'Munger', '4'),
(91, 'Muzaffarpur', '4'),
(92, 'Nalanda', '4'),
(93, 'Nawada', '4'),
(94, 'Pakaur', '10005'),
(95, 'Palamu', '10005'),
(96, 'Paschimi Champaran', '4'),
(97, 'Paschimi Singhbhum', '10005'),
(98, 'Patna', '4'),
(99, 'Purbi Champaran', '4'),
(100, 'Purbi Singhbhum', '10005'),
(101, 'Purnia', '4'),
(102, 'Ranchi', '10005'),
(103, 'Rohtas', '4'),
(104, 'Saharsa', '4'),
(105, 'Sahibganj', '10005'),
(106, 'Samastipur', '4'),
(107, 'Saran', '4'),
(108, 'Shaikhpura', '4'),
(109, 'Sheohar', '4'),
(110, 'Sitamarhi', '4'),
(111, 'Siwan', '4'),
(112, 'Supaul', '4'),
(113, 'Vaishali', '4'),
(114, 'North Goa', '5'),
(115, 'South Goa', '5'),
(116, 'Ahmedabad', '6'),
(117, 'Amreli', '6'),
(118, 'Banas Kantha', '6'),
(119, 'Bharuch', '6'),
(120, 'Bhavnagar', '6'),
(121, 'The Dangs', '6'),
(122, 'Gandhinagar', '6'),
(123, 'Jamnagar', '6'),
(124, 'Junagadh', '6'),
(125, 'Kheda', '6'),
(126, 'Kachchh', '6'),
(127, 'Mahesana', '6'),
(128, 'Panchmahal', '6'),
(129, 'Rajkot', '6'),
(130, 'Sabar Kantha', '6'),
(131, 'Surat', '6'),
(132, 'Surendranagar', '6'),
(133, 'Vadodara', '6'),
(134, 'Valsad', '6'),
(135, 'Ambala', '7'),
(136, 'Bhiwani', '7'),
(137, 'Faridabad', '7'),
(138, 'Fatehabad', '7'),
(139, 'Gurgaon', '7'),
(140, 'Hisar', '7'),
(141, 'Jhajjar', '7'),
(142, 'Jind', '7'),
(143, 'Kaithal', '7'),
(144, 'Karnal', '7'),
(145, 'Kurukshetra', '7'),
(146, 'Mahendragarh', '7'),
(148, 'Panipat', '7'),
(149, 'Rewari', '7'),
(150, 'Rohtak', '7'),
(151, 'Sirsa', '7'),
(152, 'Sonipat', '7'),
(153, 'Yamunanagar', '7'),
(154, 'Bilaspur (HP)', '8'),
(155, 'Chamba', '8'),
(156, 'Hamirpur (HP)', '8'),
(157, 'Kangra', '8'),
(158, 'Kinnaur', '8'),
(159, 'Kullu', '8'),
(160, 'Lahul & Spiti', '8'),
(161, 'Mandi', '8'),
(162, 'Shimla', '8'),
(163, 'Sirmaur', '8'),
(164, 'Solan', '8'),
(165, 'Una', '8'),
(166, 'Anant Nag', '9'),
(167, 'Badgam', '9'),
(168, 'Baramula', '9'),
(169, 'Doda', '9'),
(170, 'Jammu', '9'),
(171, 'Kargil', '9'),
(172, 'Kathua', '9'),
(173, 'Kupwara', '9'),
(174, 'Ladakh', '9'),
(175, 'Punch', '9'),
(176, 'Pulwama', '9'),
(177, 'Rajauri', '9'),
(178, 'Srinagar', '9'),
(179, 'Udhampur', '9'),
(180, 'Bagalkot', '10'),
(181, 'Bengaluru', '10'),
(182, 'Bengaluru Rural', '10'),
(183, 'Belagavi', '10'),
(184, 'Ballari', '10'),
(185, 'Bidar', '10'),
(186, 'Vijayapura', '10'),
(187, 'Chamarajanagar', '10'),
(188, 'Chikkamagaluru', '10'),
(189, 'Chitradurga', '10'),
(190, 'Dakshina Kannada', '10'),
(191, 'Davangere', '10'),
(192, 'Dharwad', '10'),
(193, 'Gadag', '10'),
(194, 'Kalaburagi', '10'),
(195, 'Hassan', '10'),
(196, 'Haveri', '10'),
(197, 'Kodagu', '10'),
(198, 'Kolar', '10'),
(199, 'Koppal', '10'),
(200, 'Mandya', '10'),
(201, 'Mysuru', '10'),
(202, 'Raichur', '10'),
(203, 'Shivamogga', '10'),
(204, 'Tumkuru', '10'),
(205, 'Udupi', '10'),
(206, 'Uttara Kannada', '10'),
(207, 'Alappuzha', '11'),
(208, 'Ernakulam', '11'),
(209, 'Idukki', '11'),
(210, 'Kannur', '11'),
(211, 'Kasargod', '11'),
(212, 'Kollam', '11'),
(213, 'Kottayam', '11'),
(214, 'Kozhikode', '11'),
(215, 'Malappuram', '11'),
(216, 'Palakkad', '11'),
(217, 'Pathanamthitta', '11'),
(218, 'Thiruvananthapuram', '11'),
(219, 'Thrissur', '11'),
(220, 'Wyanad', '11'),
(221, 'Balaghat', '12'),
(222, 'Bastar', '10004'),
(223, 'Betul', '12'),
(224, 'Bhind', '12'),
(225, 'Bhopal', '12'),
(226, 'Bilaspur (CHHAT)', '10004'),
(227, 'Chhatarpur', '12'),
(228, 'Chhindwara', '12'),
(229, 'Damoh', '12'),
(230, 'Datia', '12'),
(231, 'Dewas', '12'),
(232, 'Dhar', '12'),
(233, 'Durg', '10004'),
(234, 'East Nimar', '12'),
(235, 'Guna', '12'),
(236, 'Gwalior', '12'),
(237, 'Hoshangabad', '12'),
(238, 'Indore', '12'),
(239, 'Jabalpur', '12'),
(240, 'Jhabua', '12'),
(241, 'Mandla', '12'),
(242, 'Mandsaur', '12'),
(243, 'Morena', '12'),
(244, 'Narsimhapur', '12'),
(245, 'Panna', '12'),
(246, 'Raigarh (CHHAT)', '10004'),
(247, 'Raipur', '10004'),
(248, 'Raisen', '12'),
(249, 'Rajgarh', '12'),
(250, 'Rajnandgaon', '10004'),
(251, 'Ratlam', '12'),
(252, 'Rewa', '12'),
(253, 'Sagar', '12'),
(254, 'Satna', '12'),
(255, 'Sehore', '12'),
(256, 'Seoni', '12'),
(257, 'Shahdol', '12'),
(258, 'Shajapur', '12'),
(259, 'Shivpuri', '12'),
(260, 'Sidhi', '12'),
(261, 'Surguja', '10004'),
(262, 'Tikamgarh', '12'),
(263, 'Ujjain', '12'),
(264, 'Vidisha', '12'),
(265, 'West Nimar', '12'),
(266, 'Ahmadnagar', '13'),
(267, 'Akola', '13'),
(268, 'Amravati', '13'),
(269, 'Aurangabad (MAH)', '13'),
(270, 'Bhandara', '13'),
(271, 'Bid', '13'),
(272, 'Buldana', '13'),
(273, 'Chandrapur', '13'),
(274, 'Dhule', '13'),
(275, 'Gadchiroli', '13'),
(276, 'Mumbai', '13'),
(277, 'Jalgaon', '13'),
(278, 'Jalna', '13'),
(279, 'Kolhapur', '13'),
(280, 'Latur', '13'),
(281, 'Mumbai Suburban', '13'),
(282, 'Nagpur', '13'),
(283, 'Nanded', '13'),
(284, 'Nashik', '13'),
(285, 'Osmanabad', '13'),
(286, 'Parbhani', '13'),
(287, 'Pune', '13'),
(288, 'Raigarh (MAH)', '13'),
(289, 'Ratnagiri', '13'),
(290, 'Sangli', '13'),
(291, 'Satara', '13'),
(292, 'Sindhudurg', '13'),
(293, 'Solapur', '13'),
(294, 'Thane', '13'),
(295, 'Wardha', '13'),
(296, 'Yavatmal', '13'),
(297, 'Bishnupur', '14'),
(298, 'Chandel', '14'),
(299, 'Churachandpur', '14'),
(300, 'Imphal', '14'),
(301, 'Senapati', '14'),
(302, 'Tamenglong', '14'),
(303, 'Thoubal', '14'),
(304, 'Ukhrul', '14'),
(305, 'East Garo Hills', '15'),
(306, 'East Khasi Hills', '15'),
(307, 'Jaintia Hills', '15'),
(308, 'Ri-Bhoi', '15'),
(309, 'South Garo Hills', '15'),
(310, 'West Garo Hills', '15'),
(311, 'West Khasi Hills', '15'),
(312, 'Aizawl', '16'),
(313, 'Chhimtuipui', '16'),
(314, 'Lunglei', '16'),
(315, 'Kohima', '17'),
(316, 'Mokokchung', '17'),
(317, 'Mon', '17'),
(318, 'Phek', '17'),
(319, 'Tuensang', '17'),
(320, 'Wokha', '17'),
(321, 'Zunheboto', '17'),
(322, 'Angul', '18'),
(323, 'Balangir', '18'),
(324, 'Baleshwar', '18'),
(325, 'Barugarh', '18'),
(326, 'Bhadrak', '18'),
(327, 'Boudh', '18'),
(328, 'Cuttack', '18'),
(329, 'Deogarh (ORI)', '18'),
(330, 'Dhenkanal', '18'),
(331, 'Gajapati', '18'),
(332, 'Ganjam', '18'),
(333, 'Jagatsingpur', '18'),
(334, 'Jajpur', '18'),
(335, 'Jharsuguda', '18'),
(336, 'Kalahandi', '18'),
(337, 'Kandhamal (Phulbani)', '18'),
(338, 'Kendrapada', '18'),
(339, 'Keonjhar', '18'),
(340, 'Khurda', '18'),
(341, 'Koraput', '18'),
(342, 'Malkanagiri', '18'),
(343, 'Mayurbhanj', '18'),
(344, 'Nowrangpur', '18'),
(345, 'Nayagarh', '18'),
(346, 'Nowpara', '18'),
(347, 'Puri', '18'),
(348, 'Rayagada', '18'),
(349, 'Sambalpur', '18'),
(350, 'Sonpur', '18'),
(351, 'Sundargarh', '18'),
(352, 'Amritsar', '19'),
(353, 'Bhatinda', '19'),
(354, 'Faridkot', '19'),
(355, 'Fatehgarh Sahib', '19'),
(356, 'Ferozepur', '19'),
(357, 'Gurdaspur', '19'),
(358, 'Hoshiarpur', '19'),
(359, 'Jullandhar', '19'),
(360, 'Kapurthala', '19'),
(361, 'Ludhiana', '19'),
(362, 'Mansa', '19'),
(363, 'Moga', '19'),
(364, 'Muktsar', '19'),
(365, 'Nawanshahr', '19'),
(366, 'Patiala', '19'),
(367, 'Rupnagar (Ropar)', '19'),
(368, 'Sangrur', '19'),
(369, 'Ajmer', '20'),
(370, 'Alwar', '20'),
(371, 'Banswara', '20'),
(372, 'Baran', '20'),
(373, 'Barmer', '20'),
(374, 'Bharatpur', '20'),
(375, 'Bhilwara', '20'),
(376, 'Bikaner', '20'),
(377, 'Bundi', '20'),
(378, 'Chittaurgarh', '20'),
(379, 'Churu', '20'),
(380, 'Dausa', '20'),
(381, 'Dholpur', '20'),
(382, 'Dungarpur', '20'),
(383, 'Ganganagar', '20'),
(384, 'Hanumangarh', '20'),
(385, 'Jaipur', '20'),
(386, 'Jaisalmer', '20'),
(387, 'Jalor', '20'),
(388, 'Jhalawar', '20'),
(389, 'Jhunjhunun', '20'),
(390, 'Jodhpur', '20'),
(392, 'Kota', '20'),
(393, 'Nagaur', '20'),
(394, 'Pali', '20'),
(395, 'Rajsamand', '20'),
(396, 'Sawai Madhopur', '20'),
(397, 'Sikar', '20'),
(398, 'Sirohi', '20'),
(399, 'Tonk', '20'),
(400, 'Udaipur', '20'),
(401, 'East Sikkim', '21'),
(402, 'North Sikkim', '21'),
(403, 'South Sikkim', '21'),
(404, 'West Sikkim', '21'),
(405, 'Chennai', '22'),
(406, 'Coimbatore', '22'),
(407, 'Cuddalore (South Arcot)', '22'),
(408, 'Dharmapuri', '22'),
(409, 'Dindigul', '22'),
(410, 'Erode (Periyar)', '22'),
(411, 'Kancheepuram', '22'),
(412, 'Kanniyakumari', '22'),
(413, 'Karur', '22'),
(414, 'Madurai', '22'),
(415, 'Nagappattinam', '22'),
(416, 'Namakkal', '22'),
(417, 'Nilgiris', '22'),
(418, 'Perambalur', '22'),
(419, 'Pudukkottai', '22'),
(420, 'Ramanathapuram', '22'),
(421, 'Salem', '22'),
(422, 'Sivaganga', '22'),
(423, 'Thanjavur', '22'),
(424, 'Teni', '22'),
(425, 'Thiruvannamalai', '22'),
(426, 'Thiruvarur', '22'),
(427, 'Tirunelveli', '22'),
(428, 'Tiruvallur', '22'),
(429, 'Tiruchchirappalli', '22'),
(430, 'Tuticorin', '22'),
(431, 'Vellore (North Arcot)', '22'),
(432, 'Viluppuram', '22'),
(433, 'Virudhunagar', '22'),
(434, 'North Tripura', '23'),
(435, 'South Tripura', '23'),
(436, 'West Tripura', '23'),
(437, 'Agra', '24'),
(438, 'Aligarh', '24'),
(439, 'Allahabad', '24'),
(440, 'Almora', '10006'),
(441, 'Ambedkar Nagar', '24'),
(442, 'Azamgarh', '24'),
(443, 'Bageshwar', '10006'),
(444, 'Baghpat', '24'),
(445, 'Bahraich', '24'),
(446, 'Ballia', '24'),
(447, 'Balrampur', '24'),
(448, 'Banda', '24'),
(449, 'Barabanki', '24'),
(450, 'Bareilly', '24'),
(451, 'Basti', '24'),
(452, 'Bijnor', '24'),
(453, 'Budaun', '24'),
(454, 'Bulandshahr', '24'),
(455, 'Chamoli', '10006'),
(456, 'Champawat', '10006'),
(457, 'Chandauli', '24'),
(458, 'Chitrakut', '24'),
(459, 'Dehra Dun', '10006'),
(460, 'Deoria', '24'),
(461, 'Etah', '24'),
(462, 'Etawah', '24'),
(463, 'Faizabad', '24'),
(464, 'Farrukhabad', '24'),
(465, 'Fatehpur', '24'),
(466, 'Firozabad', '24'),
(467, 'Pauri Garhwal', '10006'),
(468, 'Gautam Budh Nagar', '24'),
(469, 'Ghaziabad', '24'),
(470, 'Ghazipur', '24'),
(471, 'Gonda', '24'),
(472, 'Gorakhpur', '24'),
(473, 'Hamirpur (UP)', '24'),
(474, 'Hardoi', '24'),
(475, 'Hardwar', '10006'),
(476, 'Auraiya', '24'),
(477, 'Jalaun', '24'),
(478, 'Jaunpur', '24'),
(479, 'Jhansi', '24'),
(480, 'Jyotiba Phule Nagar', '24'),
(481, 'Kannauj', '24'),
(482, 'Kanpur (Dehat)', '24'),
(483, 'Kanpur (Nagar)', '24'),
(484, 'Kaushambi', '24'),
(485, 'Bhadoi', '24'),
(486, 'Kheri', '24'),
(487, 'Lalitpur', '24'),
(488, 'Lucknow', '24'),
(489, 'Maharajganj', '24'),
(490, 'Mahoba', '24'),
(491, 'Mainpuri', '24'),
(492, 'Mathura', '24'),
(493, 'Mau', '24'),
(494, 'Meerut', '24'),
(495, 'Mirzapur', '24'),
(496, 'Moradabad', '24'),
(497, 'Muzaffarnagar', '24'),
(498, 'Nainital', '10006'),
(499, 'Mahamaya Nagar', '24'),
(500, 'Padarauna', '24'),
(501, 'Pilibhit', '24'),
(502, 'Pithoragarh', '10006'),
(503, 'Pratapgarh', '24'),
(504, 'Rae Bareilly', '24'),
(505, 'Rampur', '24'),
(506, 'Rudraprayag', '10006'),
(507, 'Saharanpur', '24'),
(508, 'Sant Kabir Nagar', '24'),
(509, 'Santravidas Nagar', '24'),
(510, 'Shahjahanpur', '24'),
(511, 'Shravasti', '24'),
(512, 'Sidharth Nagar', '24'),
(513, 'Sitapur', '24'),
(514, 'Sonbhadra', '24'),
(515, 'Sultanpur', '24'),
(516, 'Tehri Garhwal', '10006'),
(517, 'Udham Singh Nagar', '10006'),
(518, 'Unnao', '24'),
(519, 'Uttarkashi', '10006'),
(520, 'Varanasi', '24'),
(521, 'Bankura', '25'),
(522, 'Bardhaman', '25'),
(523, 'Birbhum', '25'),
(524, 'Kolkata', '25'),
(525, 'Cooch Behar', '25'),
(526, 'South Dinajpur', '25'),
(527, 'Darjeeling', '25'),
(528, 'Hooghly', '25'),
(529, 'Howrah', '25'),
(530, 'Jalpaiguri', '25'),
(531, 'Malda', '25'),
(532, 'Midnapore', '25'),
(533, 'Murshidabad', '25'),
(534, 'Nadia', '25'),
(535, 'North 24 Parganas', '25'),
(536, 'Purulia', '25'),
(537, 'South 24 Parganas', '25'),
(538, 'North Dinajpur', '25'),
(539, 'West Dinajpur', '25'),
(540, 'Anand', '6'),
(541, 'Dahod', '6'),
(542, 'Godhra', '6'),
(543, 'Narmada', '6'),
(544, 'Navsari', '6'),
(545, 'Patan', '6'),
(546, 'Porbandar', '6'),
(547, 'Barwani', '12'),
(548, 'Dantewada', '10004'),
(549, 'Dhamtari', '10004'),
(550, 'Harda', '12'),
(551, 'Janjgir-Champa', '10004'),
(552, 'Jashpur', '10004'),
(553, 'Kanker', '10004'),
(554, 'Katni', '12'),
(555, 'Kabirdham', '10004'),
(556, 'Korba', '10004'),
(557, 'Koriya', '10004'),
(558, 'Mahasamund', '10004'),
(559, 'Neemuch', '12'),
(560, 'Sheopur', '12'),
(561, 'Umaria', '12'),
(562, 'Nandurbar', '13'),
(563, 'Washim', '13'),
(564, 'Dimapur', '17'),
(565, 'Phulbani', '18'),
(566, 'Dhalai', '23'),
(567, 'Shahuji Maharaj Nagar', '24'),
(568, 'Nellore', '1'),
(801, 'Andamans', '52'),
(802, 'Nicobars', '52'),
(803, 'Chandigarh', '53'),
(804, 'Dadra & Nagar Haveli', '54'),
(805, 'Daman', '55'),
(806, 'Diu', '55'),
(807, 'Agatti', '56'),
(808, 'Ameni', '56'),
(809, 'Andrott', '56'),
(810, 'Bitra', '56'),
(811, 'Chetlath', '56'),
(812, 'Kadmat', '56'),
(813, 'Kalpeni', '56'),
(814, 'Kavaratti', '56'),
(815, 'Kiltan', '56'),
(816, 'Minicoy', '56'),
(817, 'Karaikal', '57'),
(818, 'Mahe', '57'),
(819, 'Pondicherry', '57'),
(820, 'Yanam', '57'),
(821, 'Delhi', '51'),
(822, 'Offshore', '10000'),
(10000, 'Unallocated', '10003'),
(10001, 'Hingoli', '13'),
(10002, 'Hathras', '24'),
(10003, 'Dindori', '12'),
(10004, 'Gondia', '13'),
(10005, 'Panchkula', '7'),
(10006, 'Ariyalur', '22'),
(10007, 'Simdega', '10005'),
(10008, 'Latehar', '10005'),
(10009, 'Saraikela Kharsawan', '10005'),
(10010, 'Jamtara', '10005'),
(10011, 'Karauli', '20'),
(10013, 'Ashoknagar', '12'),
(10014, 'Burhanpur', '12'),
(10015, 'Anuppur', '12'),
(10016, 'Krishnagiri', '22'),
(10018, 'Baska', '3'),
(10019, 'Ballabgarh', '7'),
(10020, 'Bhubaneswar', '18'),
(10021, 'Bilaspur', '10004'),
(10022, 'Cochin', '11'),
(10023, 'Guwahati', '3'),
(10024, 'Hubli', '10'),
(10025, 'Jamshedpur', '10005'),
(10026, 'Kalol', '6'),
(10027, 'Kalyan', '13'),
(10028, 'Kanpur', '24'),
(10029, 'Kansabal', '18'),
(10030, 'Madgaon', '5'),
(10031, 'Mohali', '19'),
(10032, 'Navi Mumbai', '13'),
(10033, 'New Delhi', '51'),
(10034, 'Noida', '24'),
(10035, 'Secunderabad', '59'),
(10036, 'Serampur', '25'),
(10037, 'Silvassa', '54'),
(10038, 'Tronica', '24'),
(10039, 'Vapi', '6'),
(10040, 'Vijayawada', '1'),
(10042, 'Kochi', '11'),
(10043, 'Ahmednagar', '13'),
(10044, 'Ankleshwar', '6'),
(10045, 'Aurangabad', '13'),
(10046, 'Bhilai', '10004'),
(10047, 'Calicut', '11'),
(10048, 'Goa', '5'),
(10049, 'Hazira', '6'),
(10050, 'Hosur', '22'),
(10051, 'Jalandhar', '19'),
(10052, 'Kutchch', '6'),
(10053, 'Mangalore', '10'),
(10054, 'Morbi', '6'),
(10055, 'Panaji', '5'),
(10056, 'Raigad', '13'),
(10057, 'Ropar', '19'),
(10058, 'Simla', '8'),
(10059, 'Tirupati', '1'),
(10060, 'Trissur', '22'),
(10061, 'Balasore', '18'),
(10062, 'Gandhidham', '6'),
(10063, 'Madhuranthakam', '22'),
(10064, 'Mormugao', '5'),
(10065, 'Phul', '19'),
(10066, 'Tanjore', '22'),
(10067, 'Dhrangadhra', '6'),
(10068, 'Kashipur', '10006'),
(10069, 'Baramati', '13'),
(10070, 'Bahadurgarh', '7'),
(10071, 'Anjaw', '2'),
(10072, 'Kurung Kumey', '2'),
(10073, 'Lower Dibang Valley', '2'),
(10074, 'Chirang', '3'),
(10075, 'Udalguri', '3'),
(10076, 'Arwal', '4'),
(10077, 'Kaimur', '4'),
(10078, 'Balod', '10004'),
(10079, 'Baloda Bazar ', '10004'),
(10081, 'Bemetara', '10004'),
(10082, 'Bijapur (CHHAT)', '10004'),
(10083, 'Gariaband', '10004'),
(10084, 'Kondagaon', '10004'),
(10085, 'Mungeli', '10004'),
(10086, 'Narayanpur', '10004'),
(10087, 'Sukma', '10004'),
(10088, 'Surajpur', '10004'),
(10089, 'Margao', '5'),
(10090, 'Aravalli', '6'),
(10091, 'Botad', '6'),
(10092, 'Chhota Udaipur', '6'),
(10093, 'Devbhoomi Dwarka', '6'),
(10094, 'Gir Somnath', '6'),
(10095, 'Mahisagar', '6'),
(10096, 'Tapi', '6'),
(10097, 'Mewat', '7'),
(10098, 'Palwal', '7'),
(10099, 'Samba', '9'),
(10100, 'Reasi', '9'),
(10101, 'Ramban', '9'),
(10102, 'Kishtwar', '9'),
(10103, 'Kulgam', '9'),
(10104, 'Shopian', '9'),
(10105, 'Ganderbal', '9'),
(10106, 'Bandipora', '9'),
(10107, 'Leh', '9'),
(10108, 'Ramgarh', '10005'),
(10109, 'Khunti', '10005'),
(10110, 'Chikballapur', '10'),
(10111, 'Ramanagara', '10'),
(10112, 'Yadgir', '10'),
(10113, 'Singrauli', '12'),
(10114, 'Alirajpur', '12'),
(10115, 'Khandwa', '12'),
(10116, 'Khargone', '12'),
(10117, 'Chambal', '12'),
(10118, 'Shadol', '12'),
(10119, 'Agar Malwa', '12'),
(10120, 'Palghar', '13'),
(10121, 'North Garo Hills', '15'),
(10122, 'Kolasib', '16'),
(10123, 'Lawngtlai ', '16'),
(10124, 'Mamit', '16'),
(10125, 'Saiha', '16'),
(10126, 'Serchhip', '16'),
(10127, 'Champhai', '16'),
(10128, 'Kephrie', '17'),
(10129, 'Longleng', '17'),
(10130, 'Peren', '17'),
(10131, 'Barnala', '19'),
(10132, 'Fazilka', '19'),
(10133, 'Tarn Taran', '19'),
(10134, 'Pathankot', '19'),
(10135, 'Tiruppur', '22'),
(10136, 'Devipatan', '24'),
(10137, 'Amroha', '24'),
(10138, 'Kanshi Ram Nagar', '24'),
(10139, 'Kushinagar', '24'),
(10141, 'Shamli', '24'),
(10143, 'East Medinipur', '25'),
(1, 'Adilabad', '59'),
(2, 'Anantapur', '1'),
(3, 'Chittoor', '1'),
(4, 'YSR', '1'),
(5, 'East Godavari', '1'),
(6, 'Hyderabad', '59'),
(7, 'Karimnagar', '59'),
(8, 'Khammam', '59'),
(9, 'Krishna', '1'),
(10, 'Kurnool', '1'),
(11, 'Guntur', '1'),
(12, 'Mahaboob Nagar', '59'),
(13, 'Medak', '59'),
(14, 'Nalgonda', '59'),
(15, 'Nizamabad', '59'),
(16, 'Prakasam', '1'),
(17, 'Ranga Reddy', '59'),
(18, 'Srikakulam', '1'),
(19, 'Vijayanagaram', '1'),
(20, 'Visakhapatnam', '1'),
(21, 'West Godavari', '1'),
(22, 'Warrangal', '59'),
(23, 'Changlang', '2'),
(24, 'Dibang Valley', '2'),
(25, 'East Kameng', '2'),
(26, 'East Siang', '2'),
(27, 'Lohit', '2'),
(28, 'Lower Subansiri', '2'),
(29, 'Papum Pare', '2'),
(30, 'Tawang', '2'),
(31, 'Tirap', '2'),
(32, 'Upper Siang', '2'),
(33, 'Upper Subansiri', '2'),
(34, 'West Kameng', '2'),
(35, 'West Siang', '2'),
(36, 'Barpeta', '3'),
(37, 'Bongaigaon', '3'),
(38, 'Cachar', '3'),
(39, 'Darrang', '3'),
(40, 'Dhemaji', '3'),
(41, 'Dhubri', '3'),
(42, 'Dibrugarh', '3'),
(43, 'Goalpara', '3'),
(44, 'Golaghat', '3'),
(45, 'Hailakandi', '3'),
(46, 'Jorhat', '3'),
(47, 'Kamrup', '3'),
(48, 'Karbi Anglong', '3'),
(49, 'Karimganj', '3'),
(50, 'Kokrajhar', '3'),
(51, 'Lakhimpur', '3'),
(52, 'Marigaon', '3'),
(53, 'Nagaon', '3'),
(54, 'Nalbari', '3'),
(55, 'Dima Hasao', '3'),
(56, 'Sibsagar', '3'),
(57, 'Sonitpur', '3'),
(58, 'Tinsukia', '3'),
(59, 'Araria', '4'),
(60, 'Aurangabad (BIH)', '4'),
(61, 'Banka', '4'),
(62, 'Begusarai', '4'),
(63, 'Bhabhua', '4'),
(64, 'Bhagalpur', '4'),
(65, 'Bhojpur', '4'),
(66, 'Bokaro', '10005'),
(67, 'Buxar', '4'),
(68, 'Chatra', '10005'),
(69, 'Darbhanga', '4'),
(70, 'Deoghar (JHAR)', '10005'),
(71, 'Dhanbad', '10005'),
(72, 'Dumka (Santhal Pargana)', '10005'),
(73, 'Garhwa', '10005'),
(74, 'Gaya', '4'),
(75, 'Giridih', '10005'),
(76, 'Godda', '10005'),
(77, 'Gopalganj', '4'),
(78, 'Gumla', '10005'),
(79, 'Hazaribagh', '10005'),
(80, 'Jehanabad', '4'),
(81, 'Jamui', '4'),
(82, 'Katihar', '4'),
(83, 'Khagaria', '4'),
(84, 'Kishanganj', '4'),
(85, 'Kodarma', '10005'),
(86, 'Lakshisarai', '4'),
(87, 'Lohardaga', '10005'),
(88, 'Madhepura', '4'),
(89, 'Madhubani', '4'),
(90, 'Munger', '4'),
(91, 'Muzaffarpur', '4'),
(92, 'Nalanda', '4'),
(93, 'Nawada', '4'),
(94, 'Pakaur', '10005'),
(95, 'Palamu', '10005'),
(96, 'Paschimi Champaran', '4'),
(97, 'Paschimi Singhbhum', '10005'),
(98, 'Patna', '4'),
(99, 'Purbi Champaran', '4'),
(100, 'Purbi Singhbhum', '10005'),
(101, 'Purnia', '4'),
(102, 'Ranchi', '10005'),
(103, 'Rohtas', '4'),
(104, 'Saharsa', '4'),
(105, 'Sahibganj', '10005'),
(106, 'Samastipur', '4'),
(107, 'Saran', '4'),
(108, 'Shaikhpura', '4'),
(109, 'Sheohar', '4'),
(110, 'Sitamarhi', '4'),
(111, 'Siwan', '4'),
(112, 'Supaul', '4'),
(113, 'Vaishali', '4'),
(114, 'North Goa', '5'),
(115, 'South Goa', '5'),
(116, 'Ahmedabad', '6'),
(117, 'Amreli', '6'),
(118, 'Banas Kantha', '6'),
(119, 'Bharuch', '6'),
(120, 'Bhavnagar', '6'),
(121, 'The Dangs', '6'),
(122, 'Gandhinagar', '6'),
(123, 'Jamnagar', '6'),
(124, 'Junagadh', '6'),
(125, 'Kheda', '6'),
(126, 'Kachchh', '6'),
(127, 'Mahesana', '6'),
(128, 'Panchmahal', '6'),
(129, 'Rajkot', '6'),
(130, 'Sabar Kantha', '6'),
(131, 'Surat', '6'),
(132, 'Surendranagar', '6'),
(133, 'Vadodara', '6'),
(134, 'Valsad', '6'),
(135, 'Ambala', '7'),
(136, 'Bhiwani', '7'),
(137, 'Faridabad', '7'),
(138, 'Fatehabad', '7'),
(139, 'Gurgaon', '7'),
(140, 'Hisar', '7'),
(141, 'Jhajjar', '7'),
(142, 'Jind', '7'),
(143, 'Kaithal', '7'),
(144, 'Karnal', '7'),
(145, 'Kurukshetra', '7'),
(146, 'Mahendragarh', '7'),
(148, 'Panipat', '7'),
(149, 'Rewari', '7'),
(150, 'Rohtak', '7'),
(151, 'Sirsa', '7'),
(152, 'Sonipat', '7'),
(153, 'Yamunanagar', '7'),
(154, 'Bilaspur (HP)', '8'),
(155, 'Chamba', '8'),
(156, 'Hamirpur (HP)', '8'),
(157, 'Kangra', '8'),
(158, 'Kinnaur', '8'),
(159, 'Kullu', '8'),
(160, 'Lahul & Spiti', '8'),
(161, 'Mandi', '8'),
(162, 'Shimla', '8'),
(163, 'Sirmaur', '8'),
(164, 'Solan', '8'),
(165, 'Una', '8'),
(166, 'Anant Nag', '9'),
(167, 'Badgam', '9'),
(168, 'Baramula', '9'),
(169, 'Doda', '9'),
(170, 'Jammu', '9'),
(171, 'Kargil', '9'),
(172, 'Kathua', '9'),
(173, 'Kupwara', '9'),
(174, 'Ladakh', '9'),
(175, 'Punch', '9'),
(176, 'Pulwama', '9'),
(177, 'Rajauri', '9'),
(178, 'Srinagar', '9'),
(179, 'Udhampur', '9'),
(180, 'Bagalkot', '10'),
(181, 'Bengaluru', '10'),
(182, 'Bengaluru Rural', '10'),
(183, 'Belagavi', '10'),
(184, 'Ballari', '10'),
(185, 'Bidar', '10'),
(186, 'Vijayapura', '10'),
(187, 'Chamarajanagar', '10'),
(188, 'Chikkamagaluru', '10'),
(189, 'Chitradurga', '10'),
(190, 'Dakshina Kannada', '10'),
(191, 'Davangere', '10'),
(192, 'Dharwad', '10'),
(193, 'Gadag', '10'),
(194, 'Kalaburagi', '10'),
(195, 'Hassan', '10'),
(196, 'Haveri', '10'),
(197, 'Kodagu', '10'),
(198, 'Kolar', '10'),
(199, 'Koppal', '10'),
(200, 'Mandya', '10'),
(201, 'Mysuru', '10'),
(202, 'Raichur', '10'),
(203, 'Shivamogga', '10'),
(204, 'Tumkuru', '10'),
(205, 'Udupi', '10'),
(206, 'Uttara Kannada', '10'),
(207, 'Alappuzha', '11'),
(208, 'Ernakulam', '11'),
(209, 'Idukki', '11'),
(210, 'Kannur', '11'),
(211, 'Kasargod', '11'),
(212, 'Kollam', '11'),
(213, 'Kottayam', '11'),
(214, 'Kozhikode', '11'),
(215, 'Malappuram', '11'),
(216, 'Palakkad', '11'),
(217, 'Pathanamthitta', '11'),
(218, 'Thiruvananthapuram', '11'),
(219, 'Thrissur', '11'),
(220, 'Wyanad', '11'),
(221, 'Balaghat', '12'),
(222, 'Bastar', '10004'),
(223, 'Betul', '12'),
(224, 'Bhind', '12'),
(225, 'Bhopal', '12'),
(226, 'Bilaspur (CHHAT)', '10004'),
(227, 'Chhatarpur', '12'),
(228, 'Chhindwara', '12'),
(229, 'Damoh', '12'),
(230, 'Datia', '12'),
(231, 'Dewas', '12'),
(232, 'Dhar', '12'),
(233, 'Durg', '10004'),
(234, 'East Nimar', '12'),
(235, 'Guna', '12'),
(236, 'Gwalior', '12'),
(237, 'Hoshangabad', '12'),
(238, 'Indore', '12'),
(239, 'Jabalpur', '12'),
(240, 'Jhabua', '12'),
(241, 'Mandla', '12'),
(242, 'Mandsaur', '12'),
(243, 'Morena', '12'),
(244, 'Narsimhapur', '12'),
(245, 'Panna', '12'),
(246, 'Raigarh (CHHAT)', '10004'),
(247, 'Raipur', '10004'),
(248, 'Raisen', '12'),
(249, 'Rajgarh', '12'),
(250, 'Rajnandgaon', '10004'),
(251, 'Ratlam', '12'),
(252, 'Rewa', '12'),
(253, 'Sagar', '12'),
(254, 'Satna', '12'),
(255, 'Sehore', '12'),
(256, 'Seoni', '12'),
(257, 'Shahdol', '12'),
(258, 'Shajapur', '12'),
(259, 'Shivpuri', '12'),
(260, 'Sidhi', '12'),
(261, 'Surguja', '10004'),
(262, 'Tikamgarh', '12'),
(263, 'Ujjain', '12'),
(264, 'Vidisha', '12'),
(265, 'West Nimar', '12'),
(266, 'Ahmadnagar', '13'),
(267, 'Akola', '13'),
(268, 'Amravati', '13'),
(269, 'Aurangabad (MAH)', '13'),
(270, 'Bhandara', '13'),
(271, 'Bid', '13'),
(272, 'Buldana', '13'),
(273, 'Chandrapur', '13'),
(274, 'Dhule', '13'),
(275, 'Gadchiroli', '13'),
(276, 'Mumbai', '13'),
(277, 'Jalgaon', '13'),
(278, 'Jalna', '13'),
(279, 'Kolhapur', '13'),
(280, 'Latur', '13'),
(281, 'Mumbai Suburban', '13'),
(282, 'Nagpur', '13'),
(283, 'Nanded', '13'),
(284, 'Nashik', '13'),
(285, 'Osmanabad', '13'),
(286, 'Parbhani', '13'),
(287, 'Pune', '13'),
(288, 'Raigarh (MAH)', '13'),
(289, 'Ratnagiri', '13'),
(290, 'Sangli', '13'),
(291, 'Satara', '13'),
(292, 'Sindhudurg', '13'),
(293, 'Solapur', '13'),
(294, 'Thane', '13'),
(295, 'Wardha', '13'),
(296, 'Yavatmal', '13'),
(297, 'Bishnupur', '14'),
(298, 'Chandel', '14'),
(299, 'Churachandpur', '14'),
(300, 'Imphal', '14'),
(301, 'Senapati', '14'),
(302, 'Tamenglong', '14'),
(303, 'Thoubal', '14'),
(304, 'Ukhrul', '14'),
(305, 'East Garo Hills', '15'),
(306, 'East Khasi Hills', '15'),
(307, 'Jaintia Hills', '15'),
(308, 'Ri-Bhoi', '15'),
(309, 'South Garo Hills', '15'),
(310, 'West Garo Hills', '15'),
(311, 'West Khasi Hills', '15'),
(312, 'Aizawl', '16'),
(313, 'Chhimtuipui', '16'),
(314, 'Lunglei', '16'),
(315, 'Kohima', '17'),
(316, 'Mokokchung', '17'),
(317, 'Mon', '17'),
(318, 'Phek', '17'),
(319, 'Tuensang', '17'),
(320, 'Wokha', '17'),
(321, 'Zunheboto', '17'),
(322, 'Angul', '18'),
(323, 'Balangir', '18'),
(324, 'Baleshwar', '18'),
(325, 'Barugarh', '18'),
(326, 'Bhadrak', '18'),
(327, 'Boudh', '18'),
(328, 'Cuttack', '18'),
(329, 'Deogarh (ORI)', '18'),
(330, 'Dhenkanal', '18'),
(331, 'Gajapati', '18'),
(332, 'Ganjam', '18'),
(333, 'Jagatsingpur', '18'),
(334, 'Jajpur', '18'),
(335, 'Jharsuguda', '18'),
(336, 'Kalahandi', '18'),
(337, 'Kandhamal (Phulbani)', '18'),
(338, 'Kendrapada', '18'),
(339, 'Keonjhar', '18'),
(340, 'Khurda', '18'),
(341, 'Koraput', '18'),
(342, 'Malkanagiri', '18'),
(343, 'Mayurbhanj', '18'),
(344, 'Nowrangpur', '18'),
(345, 'Nayagarh', '18'),
(346, 'Nowpara', '18'),
(347, 'Puri', '18'),
(348, 'Rayagada', '18'),
(349, 'Sambalpur', '18'),
(350, 'Sonpur', '18'),
(351, 'Sundargarh', '18'),
(352, 'Amritsar', '19'),
(353, 'Bhatinda', '19'),
(354, 'Faridkot', '19'),
(355, 'Fatehgarh Sahib', '19'),
(356, 'Ferozepur', '19'),
(357, 'Gurdaspur', '19'),
(358, 'Hoshiarpur', '19'),
(359, 'Jullandhar', '19'),
(360, 'Kapurthala', '19'),
(361, 'Ludhiana', '19'),
(362, 'Mansa', '19'),
(363, 'Moga', '19'),
(364, 'Muktsar', '19'),
(365, 'Nawanshahr', '19'),
(366, 'Patiala', '19'),
(367, 'Rupnagar (Ropar)', '19'),
(368, 'Sangrur', '19'),
(369, 'Ajmer', '20'),
(370, 'Alwar', '20'),
(371, 'Banswara', '20'),
(372, 'Baran', '20'),
(373, 'Barmer', '20'),
(374, 'Bharatpur', '20'),
(375, 'Bhilwara', '20'),
(376, 'Bikaner', '20'),
(377, 'Bundi', '20'),
(378, 'Chittaurgarh', '20'),
(379, 'Churu', '20'),
(380, 'Dausa', '20'),
(381, 'Dholpur', '20'),
(382, 'Dungarpur', '20'),
(383, 'Ganganagar', '20'),
(384, 'Hanumangarh', '20'),
(385, 'Jaipur', '20'),
(386, 'Jaisalmer', '20'),
(387, 'Jalor', '20'),
(388, 'Jhalawar', '20'),
(389, 'Jhunjhunun', '20'),
(390, 'Jodhpur', '20'),
(392, 'Kota', '20'),
(393, 'Nagaur', '20'),
(394, 'Pali', '20'),
(395, 'Rajsamand', '20'),
(396, 'Sawai Madhopur', '20'),
(397, 'Sikar', '20'),
(398, 'Sirohi', '20'),
(399, 'Tonk', '20'),
(400, 'Udaipur', '20'),
(401, 'East Sikkim', '21'),
(402, 'North Sikkim', '21'),
(403, 'South Sikkim', '21'),
(404, 'West Sikkim', '21'),
(405, 'Chennai', '22'),
(406, 'Coimbatore', '22'),
(407, 'Cuddalore (South Arcot)', '22'),
(408, 'Dharmapuri', '22'),
(409, 'Dindigul', '22'),
(410, 'Erode (Periyar)', '22'),
(411, 'Kancheepuram', '22'),
(412, 'Kanniyakumari', '22'),
(413, 'Karur', '22'),
(414, 'Madurai', '22'),
(415, 'Nagappattinam', '22'),
(416, 'Namakkal', '22'),
(417, 'Nilgiris', '22'),
(418, 'Perambalur', '22'),
(419, 'Pudukkottai', '22'),
(420, 'Ramanathapuram', '22'),
(421, 'Salem', '22'),
(422, 'Sivaganga', '22'),
(423, 'Thanjavur', '22'),
(424, 'Teni', '22'),
(425, 'Thiruvannamalai', '22'),
(426, 'Thiruvarur', '22'),
(427, 'Tirunelveli', '22'),
(428, 'Tiruvallur', '22'),
(429, 'Tiruchchirappalli', '22'),
(430, 'Tuticorin', '22'),
(431, 'Vellore (North Arcot)', '22'),
(432, 'Viluppuram', '22'),
(433, 'Virudhunagar', '22'),
(434, 'North Tripura', '23'),
(435, 'South Tripura', '23'),
(436, 'West Tripura', '23'),
(437, 'Agra', '24'),
(438, 'Aligarh', '24'),
(439, 'Allahabad', '24'),
(440, 'Almora', '10006'),
(441, 'Ambedkar Nagar', '24'),
(442, 'Azamgarh', '24'),
(443, 'Bageshwar', '10006'),
(444, 'Baghpat', '24'),
(445, 'Bahraich', '24'),
(446, 'Ballia', '24'),
(447, 'Balrampur', '24'),
(448, 'Banda', '24'),
(449, 'Barabanki', '24'),
(450, 'Bareilly', '24'),
(451, 'Basti', '24'),
(452, 'Bijnor', '24'),
(453, 'Budaun', '24'),
(454, 'Bulandshahr', '24'),
(455, 'Chamoli', '10006'),
(456, 'Champawat', '10006'),
(457, 'Chandauli', '24'),
(458, 'Chitrakut', '24'),
(459, 'Dehra Dun', '10006'),
(460, 'Deoria', '24'),
(461, 'Etah', '24'),
(462, 'Etawah', '24'),
(463, 'Faizabad', '24'),
(464, 'Farrukhabad', '24'),
(465, 'Fatehpur', '24'),
(466, 'Firozabad', '24'),
(467, 'Pauri Garhwal', '10006'),
(468, 'Gautam Budh Nagar', '24'),
(469, 'Ghaziabad', '24'),
(470, 'Ghazipur', '24'),
(471, 'Gonda', '24'),
(472, 'Gorakhpur', '24'),
(473, 'Hamirpur (UP)', '24'),
(474, 'Hardoi', '24'),
(475, 'Hardwar', '10006'),
(476, 'Auraiya', '24'),
(477, 'Jalaun', '24'),
(478, 'Jaunpur', '24'),
(479, 'Jhansi', '24'),
(480, 'Jyotiba Phule Nagar', '24'),
(481, 'Kannauj', '24'),
(482, 'Kanpur (Dehat)', '24'),
(483, 'Kanpur (Nagar)', '24'),
(484, 'Kaushambi', '24'),
(485, 'Bhadoi', '24'),
(486, 'Kheri', '24'),
(487, 'Lalitpur', '24'),
(488, 'Lucknow', '24'),
(489, 'Maharajganj', '24'),
(490, 'Mahoba', '24'),
(491, 'Mainpuri', '24'),
(492, 'Mathura', '24'),
(493, 'Mau', '24'),
(494, 'Meerut', '24'),
(495, 'Mirzapur', '24'),
(496, 'Moradabad', '24'),
(497, 'Muzaffarnagar', '24'),
(498, 'Nainital', '10006'),
(499, 'Mahamaya Nagar', '24'),
(500, 'Padarauna', '24'),
(501, 'Pilibhit', '24'),
(502, 'Pithoragarh', '10006'),
(503, 'Pratapgarh', '24'),
(504, 'Rae Bareilly', '24'),
(505, 'Rampur', '24'),
(506, 'Rudraprayag', '10006'),
(507, 'Saharanpur', '24'),
(508, 'Sant Kabir Nagar', '24'),
(509, 'Santravidas Nagar', '24'),
(510, 'Shahjahanpur', '24'),
(511, 'Shravasti', '24'),
(512, 'Sidharth Nagar', '24'),
(513, 'Sitapur', '24'),
(514, 'Sonbhadra', '24'),
(515, 'Sultanpur', '24'),
(516, 'Tehri Garhwal', '10006'),
(517, 'Udham Singh Nagar', '10006'),
(518, 'Unnao', '24'),
(519, 'Uttarkashi', '10006'),
(520, 'Varanasi', '24'),
(521, 'Bankura', '25'),
(522, 'Bardhaman', '25'),
(523, 'Birbhum', '25'),
(524, 'Kolkata', '25'),
(525, 'Cooch Behar', '25'),
(526, 'South Dinajpur', '25'),
(527, 'Darjeeling', '25'),
(528, 'Hooghly', '25'),
(529, 'Howrah', '25'),
(530, 'Jalpaiguri', '25'),
(531, 'Malda', '25'),
(532, 'Midnapore', '25'),
(533, 'Murshidabad', '25'),
(534, 'Nadia', '25'),
(535, 'North 24 Parganas', '25'),
(536, 'Purulia', '25'),
(537, 'South 24 Parganas', '25'),
(538, 'North Dinajpur', '25'),
(539, 'West Dinajpur', '25'),
(540, 'Anand', '6'),
(541, 'Dahod', '6'),
(542, 'Godhra', '6'),
(543, 'Narmada', '6'),
(544, 'Navsari', '6'),
(545, 'Patan', '6'),
(546, 'Porbandar', '6'),
(547, 'Barwani', '12'),
(548, 'Dantewada', '10004'),
(549, 'Dhamtari', '10004'),
(550, 'Harda', '12'),
(551, 'Janjgir-Champa', '10004'),
(552, 'Jashpur', '10004'),
(553, 'Kanker', '10004'),
(554, 'Katni', '12'),
(555, 'Kabirdham', '10004'),
(556, 'Korba', '10004'),
(557, 'Koriya', '10004'),
(558, 'Mahasamund', '10004'),
(559, 'Neemuch', '12'),
(560, 'Sheopur', '12'),
(561, 'Umaria', '12'),
(562, 'Nandurbar', '13'),
(563, 'Washim', '13'),
(564, 'Dimapur', '17'),
(565, 'Phulbani', '18'),
(566, 'Dhalai', '23'),
(567, 'Shahuji Maharaj Nagar', '24'),
(568, 'Nellore', '1'),
(801, 'Andamans', '52'),
(802, 'Nicobars', '52'),
(803, 'Chandigarh', '53'),
(804, 'Dadra & Nagar Haveli', '54'),
(805, 'Daman', '55'),
(806, 'Diu', '55'),
(807, 'Agatti', '56'),
(808, 'Ameni', '56'),
(809, 'Andrott', '56'),
(810, 'Bitra', '56'),
(811, 'Chetlath', '56'),
(812, 'Kadmat', '56'),
(813, 'Kalpeni', '56'),
(814, 'Kavaratti', '56'),
(815, 'Kiltan', '56'),
(816, 'Minicoy', '56'),
(817, 'Karaikal', '57'),
(818, 'Mahe', '57'),
(819, 'Pondicherry', '57'),
(820, 'Yanam', '57'),
(821, 'Delhi', '51'),
(822, 'Offshore', '10000'),
(10000, 'Unallocated', '10003'),
(10001, 'Hingoli', '13'),
(10002, 'Hathras', '24'),
(10003, 'Dindori', '12'),
(10004, 'Gondia', '13'),
(10005, 'Panchkula', '7'),
(10006, 'Ariyalur', '22'),
(10007, 'Simdega', '10005'),
(10008, 'Latehar', '10005'),
(10009, 'Saraikela Kharsawan', '10005'),
(10010, 'Jamtara', '10005'),
(10011, 'Karauli', '20'),
(10013, 'Ashoknagar', '12'),
(10014, 'Burhanpur', '12'),
(10015, 'Anuppur', '12'),
(10016, 'Krishnagiri', '22'),
(10018, 'Baska', '3'),
(10019, 'Ballabgarh', '7'),
(10020, 'Bhubaneswar', '18'),
(10021, 'Bilaspur', '10004'),
(10022, 'Cochin', '11'),
(10023, 'Guwahati', '3'),
(10024, 'Hubli', '10'),
(10025, 'Jamshedpur', '10005'),
(10026, 'Kalol', '6'),
(10027, 'Kalyan', '13'),
(10028, 'Kanpur', '24'),
(10029, 'Kansabal', '18'),
(10030, 'Madgaon', '5'),
(10031, 'Mohali', '19'),
(10032, 'Navi Mumbai', '13'),
(10033, 'New Delhi', '51'),
(10034, 'Noida', '24'),
(10035, 'Secunderabad', '59'),
(10036, 'Serampur', '25'),
(10037, 'Silvassa', '54'),
(10038, 'Tronica', '24'),
(10039, 'Vapi', '6'),
(10040, 'Vijayawada', '1'),
(10042, 'Kochi', '11'),
(10043, 'Ahmednagar', '13'),
(10044, 'Ankleshwar', '6'),
(10045, 'Aurangabad', '13'),
(10046, 'Bhilai', '10004'),
(10047, 'Calicut', '11'),
(10048, 'Goa', '5'),
(10049, 'Hazira', '6'),
(10050, 'Hosur', '22'),
(10051, 'Jalandhar', '19'),
(10052, 'Kutchch', '6'),
(10053, 'Mangalore', '10'),
(10054, 'Morbi', '6'),
(10055, 'Panaji', '5'),
(10056, 'Raigad', '13'),
(10057, 'Ropar', '19'),
(10058, 'Simla', '8'),
(10059, 'Tirupati', '1'),
(10060, 'Trissur', '22'),
(10061, 'Balasore', '18'),
(10062, 'Gandhidham', '6'),
(10063, 'Madhuranthakam', '22'),
(10064, 'Mormugao', '5'),
(10065, 'Phul', '19'),
(10066, 'Tanjore', '22'),
(10067, 'Dhrangadhra', '6'),
(10068, 'Kashipur', '10006'),
(10069, 'Baramati', '13'),
(10070, 'Bahadurgarh', '7'),
(10071, 'Anjaw', '2'),
(10072, 'Kurung Kumey', '2'),
(10073, 'Lower Dibang Valley', '2'),
(10074, 'Chirang', '3'),
(10075, 'Udalguri', '3'),
(10076, 'Arwal', '4'),
(10077, 'Kaimur', '4'),
(10078, 'Balod', '10004'),
(10079, 'Baloda Bazar ', '10004'),
(10081, 'Bemetara', '10004'),
(10082, 'Bijapur (CHHAT)', '10004'),
(10083, 'Gariaband', '10004'),
(10084, 'Kondagaon', '10004'),
(10085, 'Mungeli', '10004'),
(10086, 'Narayanpur', '10004'),
(10087, 'Sukma', '10004'),
(10088, 'Surajpur', '10004'),
(10089, 'Margao', '5'),
(10090, 'Aravalli', '6'),
(10091, 'Botad', '6'),
(10092, 'Chhota Udaipur', '6'),
(10093, 'Devbhoomi Dwarka', '6'),
(10094, 'Gir Somnath', '6'),
(10095, 'Mahisagar', '6'),
(10096, 'Tapi', '6'),
(10097, 'Mewat', '7'),
(10098, 'Palwal', '7'),
(10099, 'Samba', '9'),
(10100, 'Reasi', '9'),
(10101, 'Ramban', '9'),
(10102, 'Kishtwar', '9'),
(10103, 'Kulgam', '9'),
(10104, 'Shopian', '9'),
(10105, 'Ganderbal', '9'),
(10106, 'Bandipora', '9'),
(10107, 'Leh', '9'),
(10108, 'Ramgarh', '10005'),
(10109, 'Khunti', '10005'),
(10110, 'Chikballapur', '10'),
(10111, 'Ramanagara', '10'),
(10112, 'Yadgir', '10'),
(10113, 'Singrauli', '12'),
(10114, 'Alirajpur', '12'),
(10115, 'Khandwa', '12'),
(10116, 'Khargone', '12'),
(10117, 'Chambal', '12'),
(10118, 'Shadol', '12'),
(10119, 'Agar Malwa', '12'),
(10120, 'Palghar', '13'),
(10121, 'North Garo Hills', '15'),
(10122, 'Kolasib', '16'),
(10123, 'Lawngtlai ', '16'),
(10124, 'Mamit', '16'),
(10125, 'Saiha', '16'),
(10126, 'Serchhip', '16'),
(10127, 'Champhai', '16'),
(10128, 'Kephrie', '17'),
(10129, 'Longleng', '17'),
(10130, 'Peren', '17'),
(10131, 'Barnala', '19'),
(10132, 'Fazilka', '19'),
(10133, 'Tarn Taran', '19'),
(10134, 'Pathankot', '19'),
(10135, 'Tiruppur', '22'),
(10136, 'Devipatan', '24'),
(10137, 'Amroha', '24'),
(10138, 'Kanshi Ram Nagar', '24'),
(10139, 'Kushinagar', '24'),
(10141, 'Shamli', '24'),
(10143, 'East Medinipur', '25');

-- --------------------------------------------------------

--
-- Table structure for table `configs`
--

CREATE TABLE `configs` (
  `variable` varchar(255) NOT NULL,
  `value` varchar(500) NOT NULL,
  `auto_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `configs`
--

INSERT INTO `configs` (`variable`, `value`, `auto_id`) VALUES
('avatar_original', '', 1),
('first_name', 'Jitendra', 2),
('last_name', 'Raulo', 3),
('email', 'raulo.jitendra@gmail.com', 4),
('password', 'ebb7383b0046870956577873ca4e11fca4eeaabcc9d32be0329da8d0584928fc25ec1b73', 5),
('salt', '80cbcf5b3044', 6),
('avatar', '', 7),
('admin_location', 'admin', 8),
('sitename', 'https://myfirstmovie.in', 9),
('sub_location', '', 10),
('uid', '2cf9ac3cf93a', 11),
('company_name', 'My First Movie', 16),
('from_email', 'info@myfirstmovie.in', 17),
('to_email', 'satyam@evokemediaservices.com', 15);

-- --------------------------------------------------------

--
-- Table structure for table `core_team`
--

CREATE TABLE `core_team` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `intro` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `CountryID` bigint(255) NOT NULL,
  `CountryName` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`CountryID`, `CountryName`) VALUES
(1, 'Afghanistan'),
(2, 'Albania'),
(3, 'Algeria'),
(4, 'Andorra'),
(5, 'Angola'),
(6, 'Antigua and Barbuda'),
(7, 'Argentina'),
(8, 'Armenia'),
(9, 'Australia'),
(10, 'Austria'),
(11, 'Azerbaijan'),
(12, 'Bahamas'),
(13, 'Bahrain'),
(14, 'Bangladesh'),
(15, 'Barbados'),
(16, 'Belarus'),
(17, 'Belgium'),
(18, 'Belize'),
(19, 'Benin'),
(20, 'Bhutan'),
(21, 'Bolivia'),
(22, 'Bosnia-Herzegovina'),
(23, 'Botswana'),
(24, 'Brazil'),
(25, 'Brunei'),
(26, 'Bulgaria'),
(27, 'Burkina Faso'),
(28, 'Burundi'),
(29, 'Cambodia'),
(30, 'Cameroon'),
(31, 'Canada'),
(32, 'Cape Verde'),
(33, 'Central African Republic'),
(34, 'Chad'),
(35, 'Chile'),
(36, 'China'),
(37, 'Colombia'),
(38, 'Comoros'),
(39, 'Congo, Republic Of The'),
(40, 'Congo, Democratic Republic Of The'),
(41, 'Costa Rica'),
(42, 'Cote D\'Ivoire'),
(43, 'Croatia'),
(44, 'Cuba'),
(45, 'Cyprus'),
(46, 'Czech Republic'),
(47, 'Denmark'),
(48, 'Djibouti'),
(49, 'Dominica'),
(50, 'Dominican Republic'),
(51, 'Ecuador'),
(52, 'Egypt'),
(53, 'El Salvador'),
(54, 'Equatorial Guinea'),
(55, 'Eritrea'),
(56, 'Estonia'),
(57, 'Ethiopia'),
(58, 'Fiji'),
(59, 'Finland'),
(60, 'France'),
(61, 'Gabon'),
(62, 'Gambia'),
(63, 'Georgia'),
(64, 'Germany'),
(65, 'Ghana'),
(66, 'Greece'),
(67, 'Grenada'),
(68, 'Guatemala'),
(69, 'Guinea'),
(70, 'Guinea-Bissau'),
(71, 'Guyana'),
(72, 'Haiti'),
(73, 'Honduras'),
(74, 'Hungary'),
(75, 'Iceland'),
(76, 'India'),
(77, 'Indonesia'),
(78, 'Iran'),
(79, 'Iraq'),
(80, 'Ireland, Republic Of'),
(81, 'Israel'),
(82, 'Italy'),
(83, 'Jamaica'),
(84, 'Japan'),
(85, 'Jordan'),
(86, 'Kazakhstan'),
(87, 'Kenya'),
(88, 'Kiribati'),
(89, 'Korea (North)'),
(90, 'Korea (South)'),
(91, 'Kuwait'),
(92, 'Kyrgyzstan'),
(93, 'Laos'),
(94, 'Latvia'),
(95, 'Lebanon'),
(96, 'Lesotho'),
(97, 'Liberia'),
(98, 'Libya'),
(99, 'Liechtenstein'),
(100, 'Lithuania'),
(101, 'Luxembourg'),
(102, 'Macedonia'),
(103, 'Madagascar'),
(104, 'Malawi'),
(105, 'Malaysia'),
(106, 'Maldives'),
(107, 'Mali'),
(108, 'Malta'),
(109, 'Marshall Islands'),
(110, 'Mauritania'),
(111, 'Mauritius'),
(112, 'Mexico'),
(113, 'Micronesia'),
(114, 'Moldova'),
(115, 'Monaco'),
(116, 'Mongolia'),
(117, 'Morocco'),
(118, 'Mozambique'),
(119, 'Myanmar'),
(120, 'Namibia'),
(121, 'Nauru'),
(122, 'Nepal'),
(123, 'Netherlands'),
(124, 'New Zealand'),
(125, 'Nicaragua'),
(126, 'Niger'),
(127, 'Nigeria'),
(128, 'Norway'),
(129, 'Oman'),
(130, 'Pakistan'),
(131, 'Palau'),
(132, 'Palestine'),
(133, 'Panama'),
(134, 'Papua New Guinea'),
(135, 'Paraguay'),
(136, 'Peru'),
(137, 'Philippines'),
(138, 'Poland'),
(139, 'Portugal'),
(140, 'Qatar'),
(141, 'Romania'),
(142, 'Russia'),
(143, 'Rwanda'),
(144, 'Samoa'),
(145, 'San Marino'),
(146, 'Sao Tome & Principe'),
(147, 'Saudi Arabia'),
(148, 'Senegal'),
(149, 'Seychelles'),
(150, 'Sierra Leone'),
(151, 'Singapore'),
(152, 'Slovakia'),
(153, 'Slovenia'),
(154, 'Solomon Islands'),
(155, 'Somalia'),
(156, 'South Africa'),
(157, 'Spain'),
(158, 'Sri Lanka'),
(159, 'St Kitts and Nevis'),
(160, 'St Lucia'),
(161, 'St Vincent and Grenadines'),
(162, 'Sudan'),
(163, 'Suriname'),
(164, 'Swaziland'),
(165, 'Sweden'),
(166, 'Switzerland'),
(167, 'Syria'),
(168, 'Taiwan'),
(169, 'Tajikistan'),
(170, 'Tanzania'),
(171, 'Thailand'),
(172, 'Togo'),
(173, 'Tonga'),
(174, 'Trinidad and Tobago'),
(175, 'Tunisia'),
(176, 'Turkey'),
(177, 'Turkmenistan'),
(178, 'Tuvalu'),
(179, 'Uganda'),
(180, 'Ukraine'),
(181, 'United Arab Emirates'),
(182, 'UK'),
(183, 'USA'),
(184, 'Uruguay'),
(185, 'Uzbekistan'),
(186, 'Vanuatu'),
(187, 'Vatican City'),
(188, 'Venezuela'),
(189, 'Vietnam'),
(190, 'Yemen'),
(191, 'Yugoslavia'),
(192, 'Zambia'),
(193, 'Zimbabwe'),
(194, 'NRI'),
(195, 'US Virgin Islands'),
(196, 'Hong Kong'),
(197, 'American Samoa'),
(198, 'Anguilla'),
(199, 'Antarctica'),
(200, 'Aruba'),
(201, 'Bermuda'),
(202, 'Bouvet Island'),
(203, 'British Indian Ocean Territory'),
(204, 'Cayman Islands'),
(205, 'Hong Kong S.A.R.'),
(206, 'Macau S.A.R.'),
(207, 'Christmas Island'),
(208, 'Cocos (Keeling) Islands'),
(209, 'Cook Islands'),
(210, 'East Timor'),
(211, 'Faroe Islands'),
(212, 'French Guiana'),
(213, 'French Polynesia'),
(214, 'French Southern Territories'),
(215, 'Gibraltar'),
(216, 'Guadeloupe'),
(217, 'Guam'),
(218, 'Heard and McDonald Islands'),
(219, 'Martinique'),
(220, 'Mayotte'),
(221, 'Montserrat'),
(222, 'Netherlands Antilles'),
(223, 'New Caledonia'),
(224, 'Niue'),
(225, 'Norfolk Island'),
(226, 'Northern Mariana Islands'),
(227, 'Pitcairn Island'),
(228, 'Puerto Rico'),
(229, 'Reunion'),
(230, 'Saint Helena'),
(231, 'Saint Kitts And Nevis'),
(232, 'Saint Lucia'),
(233, 'Saint Pierre and Miquelon'),
(234, 'Tokelau'),
(235, 'Turks And Caicos Islands'),
(236, 'Virgin Islands (British)'),
(237, 'Virgin Islands (US)'),
(238, 'Wallis And Futuna Islands'),
(239, 'Western Sahara'),
(240, 'Unknown'),
(241, 'Multination');

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `subcription_id` int(11) UNSIGNED NOT NULL,
  `donwload_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(255) UNSIGNED NOT NULL,
  `uid` int(255) NOT NULL,
  `title` text NOT NULL,
  `explanation` text NOT NULL,
  `no_of_files` int(255) NOT NULL,
  `fee` varchar(255) NOT NULL,
  `dt` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `season_id` bigint(255) DEFAULT NULL,
  `category_id` bigint(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `industry_news`
--

CREATE TABLE `industry_news` (
  `id` bigint(20) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_admin_news` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `order_id` varchar(255) NOT NULL,
  `product_amount` varchar(255) DEFAULT NULL,
  `service_tax` varchar(255) DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `billing_name` varchar(255) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `billing_tel` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `billing_city` varchar(255) DEFAULT NULL,
  `billing_state` varchar(255) DEFAULT NULL,
  `billing_zip` varchar(255) DEFAULT NULL,
  `billing_country` varchar(255) DEFAULT NULL,
  `i_agree` tinyint(1) DEFAULT 0,
  `tracking_id` varchar(255) DEFAULT NULL,
  `bank_ref_no` varchar(255) DEFAULT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `failure_message` varchar(1000) DEFAULT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `card_name` varchar(255) DEFAULT NULL,
  `status_code` varchar(255) DEFAULT NULL,
  `status_message` varchar(1000) DEFAULT NULL,
  `vault` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders_subscription`
--

CREATE TABLE `orders_subscription` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `subscription_id` int(11) UNSIGNED NOT NULL,
  `price_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `pricing_slab` varchar(255) DEFAULT NULL,
  `subscription_from` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panelists`
--

CREATE TABLE `panelists` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricing`
--

CREATE TABLE `pricing` (
  `price_id` int(11) UNSIGNED NOT NULL,
  `subscription_id` int(11) UNSIGNED NOT NULL,
  `duration` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `saving` varchar(255) DEFAULT '0',
  `short_order` int(11) DEFAULT 0,
  `create_date` datetime DEFAULT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `last_updated_by` bigint(255) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` bigint(255) NOT NULL,
  `region_name` varchar(255) DEFAULT NULL,
  `short_order` int(255) DEFAULT NULL,
  `status` tinyint(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` bigint(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `display_note` varchar(5000) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `short_order` int(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `created_by` bigint(255) NOT NULL,
  `last_updated_by` bigint(255) NOT NULL,
  `last_update_ip` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `season_categories`
--

CREATE TABLE `season_categories` (
  `id` bigint(255) NOT NULL,
  `cat_id` bigint(255) NOT NULL,
  `season_id` bigint(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `created_by` bigint(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `season_categories`
--

INSERT INTO `season_categories` (`id`, `cat_id`, `season_id`, `create_date`, `created_by`) VALUES
(64, 11, 39, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `StateID` int(20) NOT NULL,
  `StateName` varchar(255) NOT NULL,
  `CountryID` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`StateID`, `StateName`, `CountryID`) VALUES
(1, 'Andhra Pradesh', '76'),
(2, 'Arunachal Pradesh', '76'),
(3, 'Assam', '76'),
(4, 'Bihar', '76'),
(5, 'Goa', '76'),
(6, 'Gujarat', '76'),
(7, 'Haryana', '76'),
(8, 'Himachal Pradesh', '76'),
(9, 'Jammu & Kashmir', '76'),
(10, 'Karnataka', '76'),
(11, 'Kerala', '76'),
(12, 'Madhya Pradesh', '76'),
(13, 'Maharashtra', '76'),
(14, 'Manipur', '76'),
(15, 'Meghalaya', '76'),
(16, 'Mizoram', '76'),
(17, 'Nagaland', '76'),
(18, 'Odisha', '76'),
(19, 'Punjab', '76'),
(20, 'Rajasthan', '76'),
(21, 'Sikkim', '76'),
(22, 'Tamil Nadu', '76'),
(23, 'Tripura', '76'),
(24, 'Uttar Pradesh', '76'),
(25, 'West Bengal', '76'),
(51, 'Delhi', '76'),
(52, 'Andaman & Nicobar Islands', '76'),
(53, 'Chandigarh', '76'),
(54, 'Dadra & Nagar Haveli', '76'),
(55, 'Daman & Diu', '76'),
(56, 'Lakshadweep', '76'),
(57, 'Puducherry', '76'),
(58, 'Multi States', '76'),
(10000, 'Offshore', '76'),
(10003, 'Unallocated', '76'),
(10004, 'Chhattisgarh', '76'),
(10005, 'Jharkhand', '76'),
(10006, 'Uttarakhand', '76'),
(59, 'Telangana', '76');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subcription_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `megazine_description` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `image_raw` varchar(255) DEFAULT NULL,
  `image_300` varchar(255) DEFAULT NULL,
  `image_100` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `page_title` varchar(255) DEFAULT NULL,
  `page_description` varchar(255) DEFAULT NULL,
  `page_keywords` varchar(255) DEFAULT NULL,
  `search_terms` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `ref_issue_title` varchar(255) DEFAULT NULL,
  `ref_issue_id` int(11) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `last_updated_by` bigint(255) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `last_update_ip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(255) NOT NULL,
  `testimonial` varchar(5000) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logo_thumb` varchar(255) DEFAULT NULL,
  `short_order` int(255) DEFAULT NULL,
  `status` tinyint(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `salt` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_original` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `last_update` varchar(255) NOT NULL,
  `last_update_by` bigint(255) NOT NULL,
  `last_updated_des` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `user_right` int(255) NOT NULL,
  `last_login` datetime NOT NULL,
  `access_control` varchar(255) NOT NULL,
  `access_control_keys` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `create_date`, `salt`, `hash`, `status`, `ip_address`, `full_name`, `first_name`, `last_name`, `email`, `password`, `avatar_original`, `avatar`, `user_type`, `last_update`, `last_update_by`, `last_updated_des`, `designation`, `contact`, `user_right`, `last_login`, `access_control`, `access_control_keys`) VALUES
(2, '2015-05-27 21:43:51', '378cca069c06', '', 1, '182.58.4.55', '', 'Jitendra', 'Raulo', 'support@aaravinfotech.com', 'cc1f00e0fa0353f2294d81339c143d3b00facf4ec7640981a385d23e224a0da89f9b99b8', 'admin/assets/images/users/156e8f85501c6d.jpg', 'admin/assets/images/users/156e8f85501c6d_thumb.jpg', 'admin', '2016-03-16 11:38:21', 0, '', 'Web Developer', '7208798257', 3, '2016-02-09 21:43:45', '1,1,1;1,1,1;1,1,0;0,0,0;1,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0', 'testimonials,categories,seasons,behind_the_scenes,web_users,enrollments,winners,sorting,panelists,news,core_team,admin'),
(10, '2016-04-25 16:43:46', 'a978b83620e7', '', 1, '', '', 'dharati1', 'patel1', 'dharati@aaravinfotech.com', '$2y$10$MYnpaaLrsP5Y8ZjS050hKObBb3guMP5AGqVF1Q/iUWMCC0JKajm1y', '', '', 'user', '2026-02-12 19:13:12', 0, '', 'Web Developer1', '', 0, '0000-00-00 00:00:00', '', ''),
(12, '2016-04-27 13:37:46', '4a2bcf1f4219', '', 1, '', '', 'x', 'x', 'x@gmail.com', 'e72d479f885a81a734327576c33f0e0928556c87469e5d4a7f9eef28f6773df51a795ff1', '', '', 'user', '2016-04-28 15:37:58', 0, '', 'x', '', 0, '0000-00-00 00:00:00', '1,1,0,1,0,1,1,1,0,0,0,0,0,0,0', 'testimonials,categories,behind_the_scenes,seasons,web_users'),
(13, '2016-04-28 16:00:51', 'e99a18c428cb38d5f260853678922e03', '', 1, '', '', 'test', 'test', 'test@gmail.com', '7fd8d258a70ba6dd09a9ebef84c51afc18f28d79857b09a54470047034fbce58c1c7b001', '', '', 'user', '2016-04-28 18:44:39', 0, '', 'test', '', 0, '0000-00-00 00:00:00', '1,0,1,1,1,1,1,1,1,1,0,1,0,0,0', 'testimonials,categories,behind_the_scenes,web_users,seasons'),
(14, '2016-04-28 16:07:04', '99158d18e728', '', 1, '', '', 'test1', 'test1', 'test1@gmail.com', 'de37729ef7627a1a1b29e1900a31a09f316e455d190f2f73d470f1e00f6ee6aa234bb786', '', '', 'user', '', 0, '', 'test1', '', 0, '0000-00-00 00:00:00', '1,0,1,1,1,0,0,1,1,1,1,0,1,1,0', 'testimonials,categories,seasons,behind_the_scenes,web_users'),
(15, '2016-04-29 17:19:50', 'a3c38c99137e', '', 1, '182.58.103.44', '', 'test3', 'test3', 'test3@gmail.com', '26c5f53cb9fb3989dea95d61342834a076f44da5728102accf666de47b9d701277c557fe', '', '', 'user', '', 0, '', 'test3', '', 0, '2016-05-03 02:37:57', '1,0,1,1,0,0,1,1,1,1,0,0,1,0,0', 'testimonials,categories,seasons,behind_the_scenes,web_users'),
(16, '2026-02-12 12:25:13', '', '', 1, '127.0.0.1', 'Admin User', 'Admin', 'User', 'a@a.com', '[$2y$10$hX4bOIvXMON/iOwPgrxMiO8QbmanZpiBUJA8ze2chAdlk3GosOU3.]', '', '', 'admin', '2026-02-12 12:25:13', 0, '', 'Administrator', '1234567890', 0, '0000-00-00 00:00:00', '0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0;0,0,0', 'testimonials,categories,seasons,behind_the_scenes,web_users,enrollments,winners,sorting,panelists,news,core_team,admin');

-- --------------------------------------------------------

--
-- Table structure for table `web_users`
--

CREATE TABLE `web_users` (
  `uid` bigint(255) NOT NULL,
  `region` bigint(255) NOT NULL DEFAULT 0,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `hash_code` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `avatar_thumb` varchar(255) NOT NULL,
  `avatar_path` varchar(255) NOT NULL,
  `last_update_on` datetime NOT NULL,
  `tnc_agreed` tinyint(1) NOT NULL,
  `company` varchar(255) NOT NULL,
  `billing_address` varchar(500) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(55) NOT NULL,
  `country` varchar(255) NOT NULL,
  `about_me` varchar(5000) NOT NULL,
  `newsletter` tinyint(1) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `admin_approved` tinyint(1) NOT NULL,
  `activation_code` varchar(255) NOT NULL,
  `activation_time` datetime NOT NULL,
  `activation_expire_time` datetime NOT NULL,
  `activation_link` varchar(255) NOT NULL,
  `activation_status` tinyint(1) NOT NULL,
  `reset_req_id` varchar(255) NOT NULL,
  `reset_time` datetime NOT NULL,
  `reset_expire_time` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `web_users`
--

INSERT INTO `web_users` (`uid`, `region`, `first_name`, `last_name`, `email`, `contact`, `gender`, `salt`, `hash_code`, `avatar`, `avatar_thumb`, `avatar_path`, `last_update_on`, `tnc_agreed`, `company`, `billing_address`, `address`, `city`, `state`, `zip`, `country`, `about_me`, `newsletter`, `ip`, `create_date`, `status`, `admin_approved`, `activation_code`, `activation_time`, `activation_expire_time`, `activation_link`, `activation_status`, `reset_req_id`, `reset_time`, `reset_expire_time`, `last_login`, `user_type`) VALUES
(4, 4, 'Jitendra ', 'Raulo', 'support@aaravinfotech.com', '7208798257', '', 'fbbd5d67abc6', 'b972b532f1cb51273020b492abc9b08ef8abca930260603e3962b032f05a6d7f72895519', '', '', '', '2016-02-20 17:40:43', 1, 'Aarav Infotech India Private Limited', '', '101, Millenium Plaza', 'Mumbai', 'Maharashtra', '400072', 'India', '', 1, '182.58.4.55', '2015-05-31 17:52:59', 1, 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 1, 'd21bbc998665', '2015-06-29 11:42:48', '2015-06-30 11:42:48', '2016-03-08 11:27:35', 'user'),
(211, 0, 'jitendra', 'raulo', 'raulo.jitendra@gmail.com', '7208798257', '', '5b7ecd75d185', '7b13463680330db9bd07c57dd2ed70fcb05399cf1448b85da233e89ba3d5fc6665dc6e85', '156dfe19c9aecc_raw.jpg', '156dfe19c9aecc_100.jpg', 'members/images/profile/', '2016-03-09 14:11:00', 1, 'Aarav Infotech India Pvt. Ltd.', '', '110 millenium plaza, sakinaka', 'Mumbai', 'Maharashtra', '400072', 'India', 'fdsdsfsd sdf sdfsd fsdf', 1, '27.106.8.86', '2016-03-06 17:09:04', 1, 0, '361097', '2016-03-06 17:19:08', '2016-03-07 17:18:46', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2017-09-29 18:58:56', 'user'),
(214, 0, 'Satyam', 'Raj', 'satyam@yourfilmmaker.in', '', '', '563ed9ee8fcc', 'd9b935c41a0dc5f1b466ad75834ea0f59d5920bbce8cabc30f6ea799b66c623152d323a0', '', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', '', 0, '103.239.169.131', '2016-03-10 15:19:13', 1, 0, '554357', '2017-07-28 19:52:44', '2017-07-29 19:51:41', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2017-07-28 19:46:52', 'user'),
(216, 0, 'Satyam', 'Raj', 'myfirstmovie.in@gmail.com', '9820202506', 'Male', '5efaad6f963a', '$2y$10$F66/9Ute/7VGwVpe2RiucegtV7ojcGGtjJ43nlOrSyRMT6SzPgU9C', 'fb3bc1a3e5a7906beddd8cadb18538dc.png', '138c93f3d23da89ab17879096bf02b68_thumb.png', 'admin/assets/images/users/', '2026-02-13 12:27:23', 1, 'Evoke Media Services', '', '', 'Mumbai', 'Maharashtra', '', 'INDIA', '', 0, '192.168.1.235', '2016-04-25 12:21:08', 1, 1, '587191', '2016-04-25 12:22:30', '2016-04-26 12:21:15', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2026-02-23 16:05:56', 'admin'),
(218, 0, 'Jitendra', 'Raulo', 'domains@aaravinfotech.com', '7208798257', '', '73c3ad22078d', '0ab2ece6e5bbe279a3c22faa924bc0acf947539a096bc6607b644f94933efdc45e1ce9db', '', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', '', 0, '27.106.8.86', '2017-07-28 19:10:12', 1, 0, '987169', '2017-07-28 19:11:03', '2017-07-29 19:10:13', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2017-07-28 19:10:15', 'user'),
(624, 0, 'Aditya ', 'chauhan', 'dharati@aaravinfotech.com', '1234567890', 'Male', '', '$2y$10$CyY39GIZXcrMnfxT.VnAleG2FTapqaS7Pb07.9p6acvs9oBN7x0Ka', '330a7d67e7b2158efd844ad5d4b351ae.png', 'e1fa96cbf5611028bed652f24096445e_thumb.png', 'admin/assets/images/users/', '2026-02-26 12:58:53', 0, 'evoke', 'asdf', 'mumbai', 'Papum Pare', 'Arunachal Pradesh', '400067', 'India', 'asdf', 1, '::1', '2026-02-13 11:47:53', 1, 1, '', '2026-02-13 11:47:53', '2026-02-13 11:47:53', '', 1, '', '2026-02-13 11:47:53', '2026-02-13 11:47:53', '2026-02-26 17:47:32', 'admin'),
(629, 0, 'test', 'user', 'thoor@gmail.com', '1234567890', 'Male', '', '$2y$10$t0W3e..s6JwmeiOcC70y/.roDVbNUIQSP/zoaQvjhL3TQ09lxWYHy', '', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', '', 0, '::1', '2026-02-18 11:57:02', 1, 0, '205200', '2026-02-18 16:32:54', '2026-02-19 12:02:48', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2026-02-18 11:57:04', 'user'),
(642, 0, 'test', 'user', 'asdf@gmail.com', '1234567890', 'Male', '', '$2y$10$eK9pQFuyII1ldv6MCwCNuuPZME3ZcVc2rgDK1ia.wDuD2RDAVooNe', 'kjjhkjh', '', '', '2026-02-26 07:19:56', 1, 'evoke', 'asdf', 'mumbai', '', '', '', 'India', '', 0, '', '2026-02-26 07:19:56', 1, 1, '', '2026-02-26 07:19:56', '2026-02-26 07:19:56', '', 1, '', '2026-02-26 07:19:56', '2026-02-26 07:19:56', '2026-02-26 07:19:56', 'user'),
(643, 0, 'test', 'chauhan', 'Suryachauhan367367@gmail.com', '1234567890', 'Male', '', '$2y$10$vAQ24bqfW17On3XNvMck1.eaiUx/7vTGtvptm39BuUncG5zbfTu5u', '', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', '', 0, '::1', '2026-02-26 08:21:54', 1, 0, '943358', '0000-00-00 00:00:00', '2026-02-27 08:21:54', '', 0, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2026-02-26 08:21:58', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `web_users_bk`
--

CREATE TABLE `web_users_bk` (
  `uid` bigint(255) NOT NULL,
  `region` bigint(255) NOT NULL DEFAULT 0,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `hash_code` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `avatar_thumb` varchar(255) NOT NULL,
  `last_update_on` datetime NOT NULL,
  `tnc_agreed` tinyint(1) NOT NULL,
  `company` varchar(255) NOT NULL,
  `billing_address` varchar(500) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(55) NOT NULL,
  `country` varchar(255) NOT NULL,
  `newsletter` tinyint(1) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `create_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `admin_approved` tinyint(1) NOT NULL,
  `activation_code` varchar(255) NOT NULL,
  `activation_time` datetime NOT NULL,
  `activation_expire_time` datetime NOT NULL,
  `activation_link` varchar(255) NOT NULL,
  `activation_status` tinyint(1) NOT NULL,
  `reset_req_id` varchar(255) NOT NULL,
  `reset_time` datetime NOT NULL,
  `reset_expire_time` datetime NOT NULL,
  `last_login` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `web_users_bk`
--

INSERT INTO `web_users_bk` (`uid`, `region`, `first_name`, `last_name`, `email`, `contact`, `gender`, `salt`, `hash_code`, `avatar`, `avatar_thumb`, `last_update_on`, `tnc_agreed`, `company`, `billing_address`, `address`, `city`, `state`, `zip`, `country`, `newsletter`, `ip`, `create_date`, `status`, `admin_approved`, `activation_code`, `activation_time`, `activation_expire_time`, `activation_link`, `activation_status`, `reset_req_id`, `reset_time`, `reset_expire_time`, `last_login`) VALUES
(4, 4, 'Jitendra ', 'Raulo', 'support@aaravinfotech.com', '7208798257', '', 'fbbd5d67abc6', 'b972b532f1cb51273020b492abc9b08ef8abca930260603e3962b032f05a6d7f72895519', '', '', '2015-12-09 17:43:59', 1, 'Aarav Infotech India Private Limited', '', '101, Millenium Plaza', 'Mumbai', 'Maharashtra', '400072', 'India', 1, '182.58.4.55', '2015-05-31 17:52:59', 1, 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 1, 'd21bbc998665', '2015-06-29 11:42:48', '2015-06-30 11:42:48', '2016-02-09 13:49:02'),
(80, 2, 'jitendra', 'raulo', 'domains@aaravinfotech.com', '', '', 'a61691784a4f', '33d31792ed5b84555281409d53d7c107d8ace3f6f8a0e631f32266642fa978e7185bed22', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', 0, '182.58.4.55', '2015-12-19 10:26:03', 1, 1, '676790', '2015-12-21 14:01:50', '2015-12-22 14:01:18', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2015-12-21 13:58:27'),
(84, 2, 'Jitendra', 'Raulo', 'raulo_jitendra@yahoo.co.in', '', '', '4e445fe6061b', '711b595a40e602679b8fdf0686b26ef5f36f980771dd7fd85991125ccd8f7031c16e1eaf', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', 0, '182.58.4.55', '2015-12-30 19:42:50', 1, 1, '946884', '2015-12-30 19:46:37', '2015-12-31 19:42:50', '', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(208, 2, 'jitendra', 'raulo', 'raulo.jitendra@gmail.com', '', '', '6db71952eca9', '97a9958ed3b114577736b4e716817fb48c9660a9fea1f1e809d9f587dfb36af2d41dd0a0', '', '', '0000-00-00 00:00:00', 1, '', '', '', '', '', '', '', 0, '182.58.4.55', '2016-02-10 10:43:08', 1, 0, '922423', '0000-00-00 00:00:00', '2016-02-11 10:43:08', '', 0, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `winners`
--

CREATE TABLE `winners` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(255) NOT NULL,
  `season_id` bigint(255) NOT NULL,
  `category_id` bigint(255) NOT NULL,
  `rank` int(11) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_thumb` varchar(255) DEFAULT NULL,
  `winner_photo` varchar(255) DEFAULT NULL,
  `description` varchar(5000) DEFAULT NULL,
  `rank_position` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `announcement_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `behind_the_scenes`
--
ALTER TABLE `behind_the_scenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcription_id` (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `last_updated_by` (`last_updated_by`);

--
-- Indexes for table `behind_the_scenes_images`
--
ALTER TABLE `behind_the_scenes_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcription_id` (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `ccav_resp`
--
ALTER TABLE `ccav_resp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ccav_response`
--
ALTER TABLE `ccav_response`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configs`
--
ALTER TABLE `configs`
  ADD PRIMARY KEY (`auto_id`);

--
-- Indexes for table `core_team`
--
ALTER TABLE `core_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_core_team_display_order` (`display_order`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_downloads_user` (`user_id`),
  ADD KEY `idx_downloads_sub` (`subcription_id`),
  ADD KEY `idx_downloads_date` (`donwload_date`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollments_uid` (`uid`),
  ADD KEY `idx_enrollments_season` (`season_id`),
  ADD KEY `idx_enrollments_category` (`category_id`);

--
-- Indexes for table `industry_news`
--
ALTER TABLE `industry_news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_order_id` (`order_id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `orders_subscription`
--
ALTER TABLE `orders_subscription`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_os_order_id` (`order_id`),
  ADD KEY `idx_os_user_id` (`user_id`),
  ADD KEY `idx_os_subscription_id` (`subscription_id`);

--
-- Indexes for table `panelists`
--
ALTER TABLE `panelists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pricing`
--
ALTER TABLE `pricing`
  ADD PRIMARY KEY (`price_id`),
  ADD KEY `idx_pricing_subscription` (`subscription_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcription_id` (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `last_updated_by` (`last_updated_by`);

--
-- Indexes for table `season_categories`
--
ALTER TABLE `season_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcription_id` (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subcription_id`),
  ADD UNIQUE KEY `uniq_slug` (`slug`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `int_user_id` (`user_id`),
  ADD KEY `last_update_by` (`last_update_by`),
  ADD KEY `idx_users_user_type` (`user_type`);

--
-- Indexes for table `web_users`
--
ALTER TABLE `web_users`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `idx_web_users_email` (`email`);

--
-- Indexes for table `web_users_bk`
--
ALTER TABLE `web_users_bk`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `winners`
--
ALTER TABLE `winners`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `behind_the_scenes`
--
ALTER TABLE `behind_the_scenes`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `behind_the_scenes_images`
--
ALTER TABLE `behind_the_scenes_images`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ccav_resp`
--
ALTER TABLE `ccav_resp`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `ccav_response`
--
ALTER TABLE `ccav_response`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `configs`
--
ALTER TABLE `configs`
  MODIFY `auto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `core_team`
--
ALTER TABLE `core_team`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `industry_news`
--
ALTER TABLE `industry_news`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders_subscription`
--
ALTER TABLE `orders_subscription`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panelists`
--
ALTER TABLE `panelists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pricing`
--
ALTER TABLE `pricing`
  MODIFY `price_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `season_categories`
--
ALTER TABLE `season_categories`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subcription_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `uid` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=644;

--
-- AUTO_INCREMENT for table `web_users_bk`
--
ALTER TABLE `web_users_bk`
  MODIFY `uid` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `winners`
--
ALTER TABLE `winners`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
