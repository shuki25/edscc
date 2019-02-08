-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Feb 07, 2019 at 09:33 PM
-- Server version: 5.7.24-0ubuntu0.16.04.1-log
-- PHP Version: 7.2.13-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edscc-install`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `p_commander_earning_rank` (IN `squadronId` INT)  begin
select user_id, b.squadron_id, total_earned, 1+(select count(*) from v_commander_total_earning a where a.total_earned > b.total_earned and a.squadron_id=squadronId) as rank from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=squadronId order by rank;
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `acl`
--

CREATE TABLE `acl` (
  `id` int(11) NOT NULL,
  `role_string` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `list_order` smallint(6) NOT NULL,
  `admin_flag` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl`
--

INSERT INTO `acl` (`id`, `role_string`, `description`, `list_order`, `admin_flag`) VALUES
(1, 'ROLE_ADMIN', 'User has full administrative rights (overrides everything below)', 1, 1),
(2, 'ROLE_EDITOR', 'User can add, edit, delete announcements', 2, 0),
(3, 'CAN_CHANGE_STATUS', 'User can approve, deny, ban or lock out a squadron member', 3, 0),
(4, 'CAN_EDIT_USER', 'User can edit a squadron member account settings', 4, 0),
(5, 'CAN_EDIT_PERMISSIONS', 'User modify access permissions of a squadron member', 5, 0),
(6, 'CAN_VIEW_HISTORY', 'User can view a history log of a squadron member', 6, 0),
(7, 'CAN_MODIFY_SELF', 'User can modify settings or permissions for himself/herself', 7, 0);

-- --------------------------------------------------------

--
-- Table structure for table `activity_counter`
--

CREATE TABLE `activity_counter` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `squadron_id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `bounties_claimed` int(11) NOT NULL,
  `systems_scanned` int(11) NOT NULL,
  `bodies_found` int(11) NOT NULL,
  `saa_scan_completed` int(11) NOT NULL,
  `efficiency_achieved` int(11) NOT NULL,
  `market_buy` int(11) NOT NULL,
  `market_sell` int(11) NOT NULL,
  `missions_completed` int(11) NOT NULL,
  `mining_refined` int(11) NOT NULL,
  `stolen_goods` int(11) NOT NULL,
  `cg_participated` int(11) NOT NULL,
  `crimes_committed` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `squadron_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` longtext,
  `publish_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `published_flag` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `commander`
--

CREATE TABLE `commander` (
  `id` int(11) NOT NULL,
  `player_id` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `asset` bigint(20) DEFAULT NULL,
  `credits` bigint(20) DEFAULT NULL,
  `loan` int(11) DEFAULT NULL,
  `combat_id` int(11) DEFAULT NULL,
  `trade_id` int(11) DEFAULT NULL,
  `explore_id` int(11) DEFAULT NULL,
  `federation_id` int(11) DEFAULT NULL,
  `empire_id` int(11) DEFAULT NULL,
  `cqc_id` int(11) DEFAULT NULL,
  `combat_progress` int(11) DEFAULT NULL,
  `trade_progress` int(11) DEFAULT NULL,
  `explore_progress` int(11) DEFAULT NULL,
  `federation_progress` int(11) DEFAULT NULL,
  `empire_progress` int(11) DEFAULT NULL,
  `cqc_progress` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `debug`
--

CREATE TABLE `debug` (
  `id` int(11) NOT NULL,
  `detail` mediumtext NOT NULL,
  `posted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `earning_history`
--

CREATE TABLE `earning_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `squadron_id` int(11) NOT NULL,
  `earning_type_id` int(11) NOT NULL,
  `earned_on` date NOT NULL,
  `reward` int(11) NOT NULL,
  `crew_wage` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `earning_type`
--

CREATE TABLE `earning_type` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `mission_flag` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `earning_type`
--

INSERT INTO `earning_type` (`id`, `name`, `mission_flag`) VALUES
(1, 'Bounty', 0),
(2, 'CapShipBond', 0),
(3, 'FactionKillBond', 0),
(4, 'ExplorationData', 0),
(5, 'MarketBuy', 0),
(6, 'MarketSell', 0),
(7, 'CommunityGoalReward', 0),
(8, 'MissionCompleted', 0),
(9, 'Mission_Altruism', 1),
(10, 'Mission_Assassinate', 1),
(11, 'Mission_AssassinateWing', 1),
(12, 'Mission_Collect', 1),
(13, 'Mission_Courier', 1),
(14, 'Mission_Delivery', 1),
(15, 'Mission_DeliveryWing', 1),
(16, 'Mission_Disable', 1),
(17, 'Mission_DS', 1),
(18, 'Mission_Hack', 1),
(19, 'Mission_Massacre', 1),
(20, 'Mission_PassengerBulk', 1),
(21, 'Mission_Salvage', 1),
(22, 'Mission_Scan', 1),
(23, 'Mission_Sightseeing', 1),
(24, 'Mission_TheDead', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edmc`
--

CREATE TABLE `edmc` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entry` text COMMENT '(DC2Type:json_array)',
  `entered_at` datetime NOT NULL,
  `processed_flag` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `faction`
--

CREATE TABLE `faction` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `faction`
--

INSERT INTO `faction` (`id`, `name`, `logo`, `journal_id`) VALUES
(1, 'Independent', 'independent-power.png', NULL),
(2, 'Alliance', 'Alliance.png', NULL),
(3, 'Empire', 'Empire.png', NULL),
(4, 'Federation', 'Federation.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `import_queue`
--

CREATE TABLE `import_queue` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `upload_filename` varchar(255) NOT NULL,
  `game_datetime` datetime NOT NULL,
  `time_started` datetime DEFAULT NULL,
  `progress_code` varchar(1) DEFAULT NULL,
  `progress_percent` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `migration_versions`
--

CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform`
--

CREATE TABLE `platform` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `platform`
--

INSERT INTO `platform` (`id`, `name`) VALUES
(1, 'PC'),
(2, 'XBox'),
(3, 'PS4');

-- --------------------------------------------------------

--
-- Table structure for table `power`
--

CREATE TABLE `power` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `journal_id` int(11) DEFAULT NULL,
  `color_power` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `power`
--

INSERT INTO `power` (`id`, `name`, `logo`, `journal_id`, `color_power`) VALUES
(1, 'Aisling Duval', 'aisling-duval.png', NULL, NULL),
(2, 'Archon Delaine', 'archon-delaine.png', NULL, NULL),
(3, 'Arissa Lavigny Duval', 'arissa-lavigny-duval.png', NULL, NULL),
(4, 'Denton Patreus', 'denton-patreus.png', NULL, NULL),
(5, 'Edmund Mahon', 'edmund-mahon.png', NULL, NULL),
(6, 'Felicia Winters', 'felicia-winters.png', NULL, NULL),
(7, 'Li Yong-Rui', 'li-yong-rui.png', NULL, NULL),
(8, 'Pranav Antal', 'pranav-antal.png', NULL, NULL),
(9, 'Zachary Hudson', 'zachary-hudson.png', NULL, NULL),
(10, 'Zemina Torval', 'zemina-torval.png', NULL, NULL),
(11, 'Yuri Grom', 'yuri-grom.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rank`
--

CREATE TABLE `rank` (
  `id` int(11) NOT NULL,
  `group_code` varchar(20) DEFAULT NULL,
  `assigned_id` int(11) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  `perm_mask` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rank`
--

INSERT INTO `rank` (`id`, `group_code`, `assigned_id`, `name`, `perm_mask`) VALUES
(1, 'service', 0, 'Rookie', 0),
(2, 'service', 1, 'Agent', 0),
(3, 'service', 2, 'Officer', 0),
(4, 'service', 3, 'Senior Officer', 0),
(5, 'service', 4, 'Leader', 0),
(6, 'combat', 0, 'Harmless', 0),
(7, 'combat', 1, 'Mostly Harmless', 0),
(8, 'combat', 2, 'Novice', 0),
(9, 'combat', 3, 'Competent', 0),
(10, 'combat', 4, 'Expert', 0),
(11, 'combat', 5, 'Master', 0),
(12, 'combat', 6, 'Dangerous', 0),
(13, 'combat', 7, 'Deadly', 0),
(14, 'combat', 8, 'Elite', 0),
(15, 'trade', 0, 'Penniless', 0),
(16, 'trade', 1, 'Mostly Penniless', 0),
(17, 'trade', 2, 'Peddler', 0),
(18, 'trade', 3, 'Dealer', 0),
(19, 'trade', 4, 'Merchant', 0),
(20, 'trade', 5, 'Broker', 0),
(21, 'trade', 6, 'Entrepreneur', 0),
(22, 'trade', 7, 'Tycoon', 0),
(23, 'trade', 8, 'Elite', 0),
(24, 'explore', 0, 'Aimless', 0),
(25, 'explore', 1, 'Mostly Aimless', 0),
(26, 'explore', 2, 'Scout', 0),
(27, 'explore', 3, 'Surveyor', 0),
(28, 'explore', 4, 'Explorer', 0),
(29, 'explore', 5, 'Pathfinder', 0),
(30, 'explore', 6, 'Ranger', 0),
(31, 'explore', 7, 'Pioneer', 0),
(32, 'explore', 8, 'Elite', 0),
(33, 'federation', 0, 'None', 0),
(34, 'federation', 1, 'Recruit', 0),
(35, 'federation', 2, 'Cadet', 0),
(36, 'federation', 3, 'Midshipman', 0),
(37, 'federation', 4, 'Petty Officer', 0),
(38, 'federation', 5, 'Chief Petty Officer', 0),
(39, 'federation', 6, 'Warrant Officer', 0),
(40, 'federation', 7, 'Ensign', 0),
(41, 'federation', 8, 'Lieutenant', 0),
(42, 'federation', 9, 'Lt. Commander', 0),
(43, 'federation', 10, 'Post Commander', 0),
(44, 'federation', 11, 'Post Captain', 0),
(45, 'federation', 12, 'Rear Admiral', 0),
(46, 'federation', 13, 'Vice Admiral', 0),
(47, 'federation', 14, 'Admiral', 0),
(48, 'empire', 0, 'None', 0),
(49, 'empire', 1, 'Outsider', 0),
(50, 'empire', 2, 'Serf', 0),
(51, 'empire', 3, 'Master', 0),
(52, 'empire', 4, 'Squire', 0),
(53, 'empire', 5, 'Knight', 0),
(54, 'empire', 6, 'Lord', 0),
(55, 'empire', 7, 'Baron', 0),
(56, 'empire', 8, 'Viscount', 0),
(57, 'empire', 9, 'Count', 0),
(58, 'empire', 10, 'Earl', 0),
(59, 'empire', 11, 'Marquis', 0),
(60, 'empire', 12, 'Duke', 0),
(61, 'empire', 13, 'Prince', 0),
(62, 'empire', 14, 'King', 0),
(63, 'cqc', 0, 'Helpless', 0),
(64, 'cqc', 1, 'Mostly Helpless', 0),
(65, 'cqc', 2, 'Amateur', 0),
(66, 'cqc', 3, 'Semi Professional', 0),
(67, 'cqc', 4, 'Professional', 0),
(68, 'cqc', 5, 'Champion', 0),
(69, 'cqc', 6, 'Hero', 0),
(70, 'cqc', 7, 'Legend', 0),
(71, 'cqc', 8, 'Elite', 0);

-- --------------------------------------------------------

--
-- Table structure for table `squadron`
--

CREATE TABLE `squadron` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `id_code` varchar(4) DEFAULT NULL,
  `active` varchar(1) DEFAULT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `admin_id` int(11) NOT NULL,
  `faction_id` int(11) DEFAULT NULL,
  `power_id` int(11) DEFAULT NULL,
  `home_base` varchar(255) DEFAULT NULL,
  `description` longtext,
  `welcome_message` longtext,
  `require_approval` varchar(1) NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `squadron`
--

INSERT INTO `squadron` (`id`, `name`, `id_code`, `active`, `platform_id`, `admin_id`, `faction_id`, `power_id`, `home_base`, `description`, `welcome_message`, `require_approval`) VALUES
(1, 'Unassigned', '0000', 'Y', 1, 1, 1, 1, 'None', NULL, NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `squadron_tags`
--

CREATE TABLE `squadron_tags` (
  `id` int(11) NOT NULL,
  `squadron_id` int(11) NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `lock_out_flag` tinyint(1) NOT NULL,
  `banned_flag` tinyint(1) NOT NULL,
  `active_flag` tinyint(1) NOT NULL,
  `denied_flag` tinyint(1) NOT NULL,
  `tag` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `name`, `lock_out_flag`, `banned_flag`, `active_flag`, `denied_flag`, `tag`) VALUES
(1, 'Pending', 0, 0, 0, 0, 'warning'),
(2, 'Approved', 0, 0, 1, 0, 'success'),
(3, 'Lock Out', 1, 0, 1, 1, 'danger'),
(4, 'Banned', 0, 1, 0, 1, 'danger'),
(5, 'Denied', 0, 0, 0, 1, 'danger'),
(6, 'New', 0, 0, 0, 0, 'primary');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_code` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `badge_color` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `group_code`, `name`, `badge_color`) VALUES
(1, 'activities', 'Anti-Xeno Activists', 'bg-blue'),
(2, 'activities', 'Bounty Hunters', 'bg-blue'),
(3, 'activities', 'Explorers', 'bg-blue'),
(4, 'activities', 'Faction Supporters', 'bg-blue'),
(5, 'activities', 'Humanitarian Aid Providers', 'bg-blue'),
(6, 'activities', 'Pirates', 'bg-blue'),
(7, 'activities', 'Power Supporters', 'bg-blue'),
(8, 'activities', 'Traders', 'bg-blue'),
(9, 'activities', 'Miners', 'bg-blue'),
(10, 'activities', 'Fuel Rats', 'bg-blue'),
(11, 'activities', 'Seals', 'bg-blue'),
(12, 'availability', 'Occasional', 'bg-orange'),
(13, 'availability', 'Weekdays', 'bg-orange'),
(14, 'availability', 'Weekends', 'bg-orange'),
(15, 'availability', 'Weeknights', 'bg-orange'),
(16, 'game_mode', 'Relaxed', 'bg-green'),
(17, 'game_mode', 'Family', 'bg-green'),
(18, 'game_mode', 'Devoted', 'bg-green'),
(19, 'play_style', 'PvE', 'bg-olive'),
(20, 'play_style', 'PvP', 'bg-olive'),
(21, 'play_style', 'Roleplay', 'bg-olive'),
(22, 'language', 'English', 'bg-navy'),
(23, 'language', 'Portuguese', 'bg-navy'),
(24, 'language', 'German', 'bg-navy'),
(25, 'language', 'French', 'bg-navy'),
(26, 'language', 'Spanish', 'bg-navy'),
(27, 'language', 'Russian', 'bg-navy'),
(28, 'attitude', 'Solo', 'bg-default'),
(29, 'attitude', 'Open', 'bg-default'),
(30, 'attitude', 'Private Group', 'bg-default'),
(31, 'activities', 'FA off', 'bg-blue');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `commander_name` varchar(255) NOT NULL,
  `squadron_id` int(11) DEFAULT NULL,
  `rank_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `status_comment` varchar(255) DEFAULT NULL,
  `email_verify` varchar(1) NOT NULL DEFAULT 'N',
  `welcome_message_flag` varchar(1) DEFAULT 'N',
  `apikey` varchar(64) DEFAULT NULL,
  `oauth_id` varchar(32) DEFAULT NULL,
  `google_flag` varchar(1) NOT NULL DEFAULT 'N',
  `gravatar_flag` varchar(1) NOT NULL DEFAULT 'N',
  `avatar_url` varchar(255) DEFAULT NULL,
  `date_joined` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `commander_name`, `squadron_id`, `rank_id`, `status_id`, `status_comment`, `email_verify`, `welcome_message_flag`, `apikey`, `oauth_id`, `google_flag`, `gravatar_flag`, `avatar_url`, `date_joined`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'dummyaccount@dummy.com', '[\"ROLE_ADMIN\"]', '$argon2i$v=19$m=1024,t=2,p=2$TXphREttaVM4ZmlDR0EwVw$3zilyDIG2Bizvc7ILBQTTZ+uJSSXqcwo3gxIXkUgp4w', 'dummy.account', 1, 1, 1, NULL, 'Y', 'N', NULL, NULL, 'N', 'N', 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTM0ODU5Nzk2OV5BMl5BanBnXkFtZTcwMzI2ODgyNQ@@._V1_UY256_CR4,0,172,256_AL_.jpg', '2018-11-01 00:00:00', NULL, '2019-01-14 00:00:00', '2019-01-21 22:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `verify_token`
--

CREATE TABLE `verify_token` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_commander_daily_earning`
-- (See below for the actual view)
--
CREATE TABLE `v_commander_daily_earning` (
`user_id` int(11)
,`squadron_id` int(11)
,`total_earned` decimal(32,0)
,`earned_on` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_commander_exploration_total`
-- (See below for the actual view)
--
CREATE TABLE `v_commander_exploration_total` (
`user_id` int(11)
,`squadron_id` int(11)
,`commander_name` varchar(255)
,`systems_scanned` decimal(32,0)
,`bodies_found` decimal(32,0)
,`saa_scan_completed` decimal(32,0)
,`efficiency_achieved` decimal(32,0)
,`efficiency_rate` decimal(4,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_commander_market_net_earning`
-- (See below for the actual view)
--
CREATE TABLE `v_commander_market_net_earning` (
`id` int(11)
,`squadron_id` bigint(11)
,`market_buy` decimal(32,0)
,`market_sell` decimal(32,0)
,`total` decimal(33,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_commander_market_net_units`
-- (See below for the actual view)
--
CREATE TABLE `v_commander_market_net_units` (
`user_id` int(11)
,`squadron_id` int(11)
,`units_bought` decimal(32,0)
,`units_sold` decimal(32,0)
,`net_units` decimal(33,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_commander_total_earning`
-- (See below for the actual view)
--
CREATE TABLE `v_commander_total_earning` (
`user_id` int(11)
,`squadron_id` int(11)
,`total_earned` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_squadron_daily_total`
-- (See below for the actual view)
--
CREATE TABLE `v_squadron_daily_total` (
`squadron_id` int(11)
,`total_earned` decimal(32,0)
,`earned_on` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_squadron_mission_total`
-- (See below for the actual view)
--
CREATE TABLE `v_squadron_mission_total` (
`squadron_id` int(11)
,`total_earned` decimal(32,0)
,`earned_on` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_squadron_type_total`
-- (See below for the actual view)
--
CREATE TABLE `v_squadron_type_total` (
`earning_type_id` int(11)
,`squadron_id` int(11)
,`total_earned` decimal(32,0)
,`earned_on` date
);

-- --------------------------------------------------------

--
-- Table structure for table `x_leaderboard_report`
--

CREATE TABLE `x_leaderboard_report` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `header` varchar(512) DEFAULT NULL,
  `columns` varchar(512) DEFAULT NULL,
  `sql` varchar(1024) DEFAULT NULL,
  `count_sql` varchar(512) DEFAULT NULL,
  `parameters` varchar(512) DEFAULT NULL,
  `parameters_sql` varchar(512) DEFAULT NULL,
  `order_id` tinyint(11) DEFAULT NULL,
  `order_dir` varchar(4) DEFAULT NULL,
  `cast_columns` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_leaderboard_report`
--

INSERT INTO `x_leaderboard_report` (`id`, `title`, `header`, `columns`, `sql`, `count_sql`, `parameters`, `parameters_sql`, `order_id`, `order_dir`, `cast_columns`) VALUES
(1, 'Overall Earning', '[\"Rank\", \"Commander Name\", \"Total Earned\"]', '[\"rank\", \"commander_name\", \"total_earned\"]', 'select user_id, commander_name, b.squadron_id, format(total_earned,0) as total_earned, 1+(select count(*) from v_commander_total_earning a where a.total_earned > b.total_earned and a.squadron_id=?) as rank from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=?', 'select count(user_id) from v_commander_total_earning b left join user u on u.id=b.user_id where b.squadron_id=?', '[[\"squadron_id\",\"squadron_id\"],[\"squadron_id\"]]', 'select * from user where id=?', 0, 'asc', '{\"total_earned\":\"signed\"}'),
(2, 'Overall Exploration', '[\"Commander Name\",\"System Scanned\",\"Bodies Discovered\",\"Complete SAA Scans\",\"Efficient Scans\",\"Efficiency Rate\"]', '[\"commander_name\",\"systems_scanned\",\"bodies_found\",\"saa_scan_completed\",\"efficiency_achieved\",\"efficiency_rate\"]', 'select * from v_commander_exploration_total where squadron_id=?', 'select count(user_id) from v_commander_exploration_total where squadron_id=?', '[[\"squadron_id\"],[\"squadron_id\"]]', 'select * from user where id=?', 0, 'asc', NULL),
(3, 'Overall Trade', '[\"Commander Name\",\"Units Bought\",\"Units Sold\",\"Net Units\",\"Amt Paid\",\"Amt Earned\",\"Net Earned\",\"Cr/Unit\"]', '[\"commander_name\",\"units_bought\",\"units_sold\",\"net_units\",\"market_buy\",\"market_sell\",\"total\",\"cr_per_unit\"]', 'select u.commander_name, v1.*, format(v2.market_buy,0) as market_buy, format(v2.market_sell,0) as market_sell, format(v2.total,0) as total, cast(ifnull(format((v2.total/v1.units_sold),0),0) as decimal(10,2)) as cr_per_unit from v_commander_market_net_units v1 left join v_commander_market_net_earning v2 on v1.user_id = v2.id and v1.squadron_id=v2.squadron_id right outer join user u on v1.user_id=u.id where u.squadron_id=?', 'select count(*) from user where squadron_id=?', '[[\"squadron_id\"],[\"squadron_id\"]]', 'select * from user where id=?', 0, 'asc', '{\"market_buy\":\"signed\",\"market_sell\":\"signed\",\"total\":\"signed\",\"cr_per_unit\":\"signed\"}');

-- --------------------------------------------------------

--
-- Structure for view `v_commander_daily_earning`
--
DROP TABLE IF EXISTS `v_commander_daily_earning`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_commander_daily_earning`  AS  select `earning_history`.`user_id` AS `user_id`,`earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned`,`earning_history`.`earned_on` AS `earned_on` from `earning_history` group by `earning_history`.`user_id`,`earning_history`.`earned_on` ;

-- --------------------------------------------------------

--
-- Structure for view `v_commander_exploration_total`
--
DROP TABLE IF EXISTS `v_commander_exploration_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_commander_exploration_total`  AS  select `t`.`user_id` AS `user_id`,`t`.`squadron_id` AS `squadron_id`,`t`.`commander_name` AS `commander_name`,`t`.`systems_scanned` AS `systems_scanned`,`t`.`bodies_found` AS `bodies_found`,`t`.`saa_scan_completed` AS `saa_scan_completed`,`t`.`efficiency_achieved` AS `efficiency_achieved`,cast(ifnull(format(((`t`.`efficiency_achieved` / `t`.`saa_scan_completed`) * 100),1),0) as decimal(4,1)) AS `efficiency_rate` from (select `a`.`user_id` AS `user_id`,`a`.`squadron_id` AS `squadron_id`,`u`.`commander_name` AS `commander_name`,sum(`a`.`systems_scanned`) AS `systems_scanned`,sum(`a`.`bodies_found`) AS `bodies_found`,sum(`a`.`saa_scan_completed`) AS `saa_scan_completed`,sum(`a`.`efficiency_achieved`) AS `efficiency_achieved` from (`activity_counter` `a` left join `user` `u` on((`a`.`user_id` = `u`.`id`))) group by `a`.`user_id`,`a`.`squadron_id`) `t` ;

-- --------------------------------------------------------

--
-- Structure for view `v_commander_market_net_earning`
--
DROP TABLE IF EXISTS `v_commander_market_net_earning`;

CREATE ALGORITHM=UNDEFINED DEFINER=`josh`@`localhost` SQL SECURITY DEFINER VIEW `v_commander_market_net_earning`  AS  select `u`.`id` AS `id`,ifnull(`t1`.`squadron_id`,ifnull(`t2`.`squadron_id`,`u`.`squadron_id`)) AS `squadron_id`,ifnull(`t1`.`market_buy`,0) AS `market_buy`,ifnull(`t2`.`market_sell`,0) AS `market_sell`,(ifnull(`t1`.`market_buy`,0) + ifnull(`t2`.`market_sell`,0)) AS `total` from (`user` `u` left join (((select `e`.`user_id` AS `user_id`,`e`.`squadron_id` AS `squadron_id`,sum(`e`.`reward`) AS `market_buy` from `earning_history` `e` where (`e`.`earning_type_id` = '5') group by `e`.`user_id`,`e`.`squadron_id`)) `t1` left join (select `e`.`user_id` AS `user_id`,`e`.`squadron_id` AS `squadron_id`,sum(`e`.`reward`) AS `market_sell` from `earning_history` `e` where (`e`.`earning_type_id` = '6') group by `e`.`user_id`,`e`.`squadron_id`) `t2` on(((`t1`.`user_id` = `t2`.`user_id`) and (`t1`.`squadron_id` = `t2`.`squadron_id`)))) on((`t1`.`user_id` = `u`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_commander_market_net_units`
--
DROP TABLE IF EXISTS `v_commander_market_net_units`;

CREATE ALGORITHM=UNDEFINED DEFINER=`josh`@`localhost` SQL SECURITY DEFINER VIEW `v_commander_market_net_units`  AS  select `t`.`user_id` AS `user_id`,`t`.`squadron_id` AS `squadron_id`,`t`.`units_bought` AS `units_bought`,`t`.`units_sold` AS `units_sold`,(`t`.`units_sold` - `t`.`units_bought`) AS `net_units` from (select `a`.`user_id` AS `user_id`,`u`.`squadron_id` AS `squadron_id`,sum(`a`.`market_buy`) AS `units_bought`,sum(`a`.`market_sell`) AS `units_sold` from (`activity_counter` `a` left join `user` `u` on((`a`.`user_id` = `u`.`id`))) group by `a`.`user_id`) `t` ;

-- --------------------------------------------------------

--
-- Structure for view `v_commander_total_earning`
--
DROP TABLE IF EXISTS `v_commander_total_earning`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_commander_total_earning`  AS  select `earning_history`.`user_id` AS `user_id`,`earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned` from `earning_history` group by `earning_history`.`user_id` ;

-- --------------------------------------------------------

--
-- Structure for view `v_squadron_daily_total`
--
DROP TABLE IF EXISTS `v_squadron_daily_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_squadron_daily_total`  AS  select `earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned`,`earning_history`.`earned_on` AS `earned_on` from `earning_history` group by `earning_history`.`squadron_id`,`earning_history`.`earned_on` ;

-- --------------------------------------------------------

--
-- Structure for view `v_squadron_mission_total`
--
DROP TABLE IF EXISTS `v_squadron_mission_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_squadron_mission_total`  AS  select `earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned`,`earning_history`.`earned_on` AS `earned_on` from `earning_history` where (`earning_history`.`earning_type_id` >= '8') group by `earning_history`.`squadron_id`,`earning_history`.`earned_on` ;

-- --------------------------------------------------------

--
-- Structure for view `v_squadron_type_total`
--
DROP TABLE IF EXISTS `v_squadron_type_total`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_squadron_type_total`  AS  select `earning_history`.`earning_type_id` AS `earning_type_id`,`earning_history`.`squadron_id` AS `squadron_id`,sum(`earning_history`.`reward`) AS `total_earned`,`earning_history`.`earned_on` AS `earned_on` from `earning_history` group by `earning_history`.`earning_type_id`,`earning_history`.`squadron_id`,`earning_history`.`earned_on` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_counter`
--
ALTER TABLE `activity_counter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C170E298A76ED395` (`user_id`),
  ADD KEY `IDX_C170E298D331F5B5` (`squadron_id`),
  ADD KEY `activity_date_idx` (`activity_date`);

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4DB9D91CA76ED395` (`user_id`),
  ADD KEY `IDX_4DB9D91CD331F5B5` (`squadron_id`),
  ADD KEY `IDX_4DB9D91CDE12AB56` (`created_by`),
  ADD KEY `IDX_4DB9D91C16FE72E1` (`updated_by`);

--
-- Indexes for table `commander`
--
ALTER TABLE `commander`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_42D318BAA76ED395` (`user_id`),
  ADD KEY `IDX_42D318BAFC7EEDB8` (`combat_id`),
  ADD KEY `IDX_42D318BAC2D9760` (`trade_id`),
  ADD KEY `IDX_42D318BA903164BD` (`explore_id`),
  ADD KEY `IDX_42D318BA6A03EFC5` (`federation_id`),
  ADD KEY `IDX_42D318BA6E6A432A` (`empire_id`),
  ADD KEY `IDX_42D318BA4B8351E7` (`cqc_id`);

--
-- Indexes for table `debug`
--
ALTER TABLE `debug`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `earning_history`
--
ALTER TABLE `earning_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4616DE45A76ED395` (`user_id`),
  ADD KEY `IDX_4616DE45D331F5B5` (`squadron_id`),
  ADD KEY `IDX_4616DE45A4E0229D` (`earning_type_id`),
  ADD KEY `earned_on_idx` (`earned_on`);

--
-- Indexes for table `earning_type`
--
ALTER TABLE `earning_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `edmc`
--
ALTER TABLE `edmc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_668A9ED9A76ED395` (`user_id`);

--
-- Indexes for table `faction`
--
ALTER TABLE `faction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_queue`
--
ALTER TABLE `import_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_92A8D0ADA76ED395` (`user_id`);

--
-- Indexes for table `migration_versions`
--
ALTER TABLE `migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `platform`
--
ALTER TABLE `platform`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `power`
--
ALTER TABLE `power`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rank`
--
ALTER TABLE `rank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `squadron`
--
ALTER TABLE `squadron`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_5901B757642B8210` (`admin_id`),
  ADD KEY `IDX_5901B7574448F8DA` (`faction_id`),
  ADD KEY `IDX_5901B757AB4FC384` (`power_id`),
  ADD KEY `IDX_5901B757FFE6496F` (`platform_id`);

--
-- Indexes for table `squadron_tags`
--
ALTER TABLE `squadron_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_52B40DEFD331F5B5` (`squadron_id`),
  ADD KEY `IDX_52B40DEFBAD26311` (`tag_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD KEY `IDX_8D93D649D331F5B5` (`squadron_id`),
  ADD KEY `apikey_idx` (`apikey`),
  ADD KEY `IDX_8D93D6497616678F` (`rank_id`),
  ADD KEY `IDX_8D93D6496BF700BD` (`status_id`);

--
-- Indexes for table `verify_token`
--
ALTER TABLE `verify_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_98E46A9BA76ED395` (`user_id`);

--
-- Indexes for table `x_leaderboard_report`
--
ALTER TABLE `x_leaderboard_report`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acl`
--
ALTER TABLE `acl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `activity_counter`
--
ALTER TABLE `activity_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commander`
--
ALTER TABLE `commander`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debug`
--
ALTER TABLE `debug`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earning_history`
--
ALTER TABLE `earning_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earning_type`
--
ALTER TABLE `earning_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `edmc`
--
ALTER TABLE `edmc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faction`
--
ALTER TABLE `faction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `import_queue`
--
ALTER TABLE `import_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform`
--
ALTER TABLE `platform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `power`
--
ALTER TABLE `power`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rank`
--
ALTER TABLE `rank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `squadron`
--
ALTER TABLE `squadron`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `squadron_tags`
--
ALTER TABLE `squadron_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `verify_token`
--
ALTER TABLE `verify_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `x_leaderboard_report`
--
ALTER TABLE `x_leaderboard_report`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_counter`
--
ALTER TABLE `activity_counter`
  ADD CONSTRAINT `FK_C170E298A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_C170E298D331F5B5` FOREIGN KEY (`squadron_id`) REFERENCES `squadron` (`id`);

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `FK_4DB9D91C16FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_4DB9D91CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_4DB9D91CD331F5B5` FOREIGN KEY (`squadron_id`) REFERENCES `squadron` (`id`),
  ADD CONSTRAINT `FK_4DB9D91CDE12AB56` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `commander`
--
ALTER TABLE `commander`
  ADD CONSTRAINT `FK_42D318BA4B8351E7` FOREIGN KEY (`cqc_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_42D318BA6A03EFC5` FOREIGN KEY (`federation_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_42D318BA6E6A432A` FOREIGN KEY (`empire_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_42D318BA903164BD` FOREIGN KEY (`explore_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_42D318BAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_42D318BAC2D9760` FOREIGN KEY (`trade_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_42D318BAFC7EEDB8` FOREIGN KEY (`combat_id`) REFERENCES `rank` (`id`);

--
-- Constraints for table `earning_history`
--
ALTER TABLE `earning_history`
  ADD CONSTRAINT `FK_4616DE45A4E0229D` FOREIGN KEY (`earning_type_id`) REFERENCES `earning_type` (`id`),
  ADD CONSTRAINT `FK_4616DE45A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_4616DE45D331F5B5` FOREIGN KEY (`squadron_id`) REFERENCES `squadron` (`id`);

--
-- Constraints for table `edmc`
--
ALTER TABLE `edmc`
  ADD CONSTRAINT `FK_668A9ED9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `import_queue`
--
ALTER TABLE `import_queue`
  ADD CONSTRAINT `FK_92A8D0ADA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `squadron`
--
ALTER TABLE `squadron`
  ADD CONSTRAINT `FK_5901B7574448F8DA` FOREIGN KEY (`faction_id`) REFERENCES `faction` (`id`),
  ADD CONSTRAINT `FK_5901B757642B8210` FOREIGN KEY (`admin_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_5901B757AB4FC384` FOREIGN KEY (`power_id`) REFERENCES `power` (`id`),
  ADD CONSTRAINT `FK_5901B757FFE6496F` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`id`);

--
-- Constraints for table `squadron_tags`
--
ALTER TABLE `squadron_tags`
  ADD CONSTRAINT `FK_52B40DEFBAD26311` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`),
  ADD CONSTRAINT `FK_52B40DEFD331F5B5` FOREIGN KEY (`squadron_id`) REFERENCES `squadron` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D6497616678F` FOREIGN KEY (`rank_id`) REFERENCES `rank` (`id`),
  ADD CONSTRAINT `FK_8D93D649D331F5B5` FOREIGN KEY (`squadron_id`) REFERENCES `squadron` (`id`),
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`);

--
-- Constraints for table `verify_token`
--
ALTER TABLE `verify_token`
  ADD CONSTRAINT `FK_98E46A9BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
