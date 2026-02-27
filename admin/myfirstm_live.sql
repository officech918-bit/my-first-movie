-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 06:53 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `configs`
--

CREATE TABLE `configs` (
  `variable` varchar(255) NOT NULL,
  `value` varchar(500) NOT NULL,
  `auto_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `CountryID` bigint(255) NOT NULL,
  `CountryName` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `dt` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `industry_news`
--

CREATE TABLE `industry_news` (
  `id` int(11) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_admin_news` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_date` datetime NOT NULL
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
  `last_update_ip` varchar(255) NOT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `StateID` int(20) NOT NULL,
  `StateName` varchar(255) NOT NULL,
  `CountryID` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `last_login` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `winners`
--

CREATE TABLE `winners` (
  `id` bigint(255) NOT NULL,
  `user_id` bigint(255) NOT NULL,
  `season_id` bigint(255) NOT NULL,
  `category_id` bigint(255) NOT NULL,
  `rank` int(11) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
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
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `last_update_by` (`last_update_by`);

--
-- Indexes for table `web_users`
--
ALTER TABLE `web_users`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `web_users_bk`
--
ALTER TABLE `web_users_bk`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `behind_the_scenes`
--
ALTER TABLE `behind_the_scenes`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `behind_the_scenes_images`
--
ALTER TABLE `behind_the_scenes_images`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ccav_resp`
--
ALTER TABLE `ccav_resp`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ccav_response`
--
ALTER TABLE `ccav_response`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configs`
--
ALTER TABLE `configs`
  MODIFY `auto_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `season_categories`
--
ALTER TABLE `season_categories`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `uid` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `web_users_bk`
--
ALTER TABLE `web_users_bk`
  MODIFY `uid` bigint(255) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
