-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 16, 2025 at 11:14 AM
-- Server version: 8.0.44-0ubuntu0.24.04.1
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mlm_laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_balances`
--

CREATE TABLE `account_balances` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `locked_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `target_audience` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `target_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `priority` int NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `show_once` tinyint(1) NOT NULL DEFAULT '1',
  `is_dismissible` tinyint(1) NOT NULL DEFAULT '1',
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Got it',
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED NOT NULL,
  `status` enum('active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `assigned_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_settings`
--

CREATE TABLE `commission_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_investment` decimal(15,2) NOT NULL DEFAULT '0.00',
  `min_direct_referrals` int NOT NULL DEFAULT '0',
  `min_indirect_referrals` int NOT NULL DEFAULT '0',
  `commission_level_1` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_level_2` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_level_3` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_structures`
--

CREATE TABLE `commission_structures` (
  `id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cryptocurrencies`
--

CREATE TABLE `cryptocurrencies` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `network` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decimal_places` tinyint NOT NULL DEFAULT '8',
  `min_withdrawal` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `max_withdrawal` decimal(20,8) DEFAULT NULL,
  `withdrawal_fee` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crypto_wallets`
--

CREATE TABLE `crypto_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `usd_rate` decimal(15,2) NOT NULL DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('transaction','investment','kyc','referral','support','account','system') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_by` bigint UNSIGNED NOT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

CREATE TABLE `followups` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `followup_date` date NOT NULL,
  `type` enum('call','email','meeting','whatsapp','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'call',
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `submit_button_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Submit Application',
  `success_message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Thank you! We will contact you soon.',
  `standard_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `submissions_count` int NOT NULL DEFAULT '0',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `form_submissions`
--

CREATE TABLE `form_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `form_id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED DEFAULT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','processed','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plans`
--

CREATE TABLE `investment_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `minimum_amount` decimal(15,2) DEFAULT NULL,
  `maximum_amount` decimal(15,2) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `interest_type` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `duration_days` int NOT NULL,
  `return_type` enum('fixed','compound') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `is_tiered` tinyint(1) NOT NULL DEFAULT '0',
  `profit_sharing_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `max_tier_level` int NOT NULL DEFAULT '0',
  `base_interest_rate` decimal(5,2) DEFAULT NULL,
  `tier_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `capital_return` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive','paused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `total_investors` int NOT NULL DEFAULT '0',
  `total_invested` decimal(12,2) NOT NULL DEFAULT '0.00',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_scheme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plan_profit_sharing`
--

CREATE TABLE `investment_plan_profit_sharing` (
  `id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `investment_plan_tier_id` bigint UNSIGNED NOT NULL,
  `level_1_commission` decimal(8,2) NOT NULL DEFAULT '0.00',
  `level_2_commission` decimal(8,2) NOT NULL DEFAULT '0.00',
  `level_3_commission` decimal(8,2) NOT NULL DEFAULT '0.00',
  `max_commission_cap` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plan_tiers`
--

CREATE TABLE `investment_plan_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `tier_level` int NOT NULL DEFAULT '0',
  `tier_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimum_amount` decimal(15,2) NOT NULL,
  `maximum_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `min_user_level` int NOT NULL DEFAULT '0',
  `tier_description` text COLLATE utf8mb4_unicode_ci,
  `tier_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `investment_returns`
--

CREATE TABLE `investment_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `user_investment_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('interest','capital') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'interest',
  `status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` timestamp NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_verifications`
--

CREATE TABLE `kyc_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempt_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `decision` enum('approved','declined','resubmission_requested') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decision_score` decimal(3,2) DEFAULT NULL,
  `verified_first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_date_of_birth` date DEFAULT NULL,
  `verified_gender` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_valid_until` date DEFAULT NULL,
  `document_verified` tinyint(1) DEFAULT NULL,
  `face_verified` tinyint(1) DEFAULT NULL,
  `liveness_check` tinyint(1) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboards`
--

CREATE TABLE `leaderboards` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `show_to_users` tinyint(1) NOT NULL DEFAULT '1',
  `max_positions` int NOT NULL DEFAULT '10',
  `referral_type` enum('all','first_level','verified_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `type` enum('competitive','target') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'competitive',
  `target_referrals` int DEFAULT NULL,
  `target_prize_amount` decimal(10,2) DEFAULT NULL,
  `max_winners` int DEFAULT NULL,
  `prize_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `prizes_distributed` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `prizes_distributed_at` timestamp NULL DEFAULT NULL,
  `prizes_distributed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_positions`
--

CREATE TABLE `leaderboard_positions` (
  `id` bigint UNSIGNED NOT NULL,
  `leaderboard_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `position` int NOT NULL,
  `referral_count` int NOT NULL,
  `prize_amount` decimal(10,2) DEFAULT NULL,
  `prize_awarded` tinyint(1) NOT NULL DEFAULT '0',
  `prize_awarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('hot','warm','cold','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cold',
  `interest` enum('Low','Medium','High') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

CREATE TABLE `lead_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_successful` tinyint(1) NOT NULL DEFAULT '1',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_at` timestamp NOT NULL,
  `logout_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mass_email_campaigns`
--

CREATE TABLE `mass_email_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `specific_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `total_recipients` int NOT NULL DEFAULT '0',
  `emails_sent` int NOT NULL DEFAULT '0',
  `emails_failed` int NOT NULL DEFAULT '0',
  `status` enum('pending','scheduled','sending','completed','cancelled','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `cancelled_by` bigint UNSIGNED DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_tables`
--

CREATE TABLE `mlm_tables` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_count` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plan_profit_sharing`
--

CREATE TABLE `plan_profit_sharing` (
  `id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `investment_plan_tier_id` bigint UNSIGNED NOT NULL,
  `level_1_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `level_2_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `level_3_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `max_commission_cap` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profit_sharing_txns`
--

CREATE TABLE `profit_sharing_txns` (
  `id` bigint UNSIGNED NOT NULL,
  `user_investment_id` bigint UNSIGNED NOT NULL,
  `beneficiary_user_id` bigint UNSIGNED NOT NULL,
  `source_user_id` bigint UNSIGNED NOT NULL,
  `commission_level` int NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `source_investment_amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_notification_logs`
--

CREATE TABLE `push_notification_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `recipients` json DEFAULT NULL,
  `recipients_count` int NOT NULL DEFAULT '0',
  `target_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keys` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `referral_tree`
--

CREATE TABLE `referral_tree` (
  `id` bigint UNSIGNED NOT NULL,
  `sponsor_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_encrypted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','pending_user','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `last_reply_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_internal_note` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('deposit','withdrawal','commission','roi','investment','bonus','fee') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crypto_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crypto_txid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sponsor_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('pending_verification','active','inactive','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_verification',
  `user_level` int NOT NULL DEFAULT '0',
  `total_invested` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `level_updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','support','moderator','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `blocked_by` bigint UNSIGNED DEFAULT NULL,
  `block_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `block_notes` text COLLATE utf8mb4_unicode_ci,
  `block_expires_at` timestamp NULL DEFAULT NULL,
  `unblocked_at` timestamp NULL DEFAULT NULL,
  `unblocked_by` bigint UNSIGNED DEFAULT NULL,
  `unblock_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `google2fa_secret` text COLLATE utf8mb4_unicode_ci,
  `google2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `google2fa_enabled_at` timestamp NULL DEFAULT NULL,
  `push_notifications_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `last_push_subscription_at` timestamp NULL DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_announcement_views`
--

CREATE TABLE `user_announcement_views` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `announcement_id` bigint UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_earnings`
--

CREATE TABLE `user_earnings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `total` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00',
  `today` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00',
  `last_earning_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_investments`
--

CREATE TABLE `user_investments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `tier_level` int NOT NULL DEFAULT '0',
  `tier_interest_rate` decimal(5,2) DEFAULT NULL,
  `user_level_at_investment` int NOT NULL DEFAULT '0',
  `amount` decimal(12,2) NOT NULL,
  `total_return` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_return` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','completed','cancelled','paused') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `started_at` timestamp NOT NULL,
  `ends_at` timestamp NOT NULL,
  `last_return_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `return_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PK',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrallink` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `treferrallink` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TL - 0',
  `total_investments` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_withdraw` decimal(15,2) NOT NULL DEFAULT '0.00',
  `last_deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `last_withdraw` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kyc_status` enum('pending','session_created','submitted','under_review','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `kyc_submitted_at` timestamp NULL DEFAULT NULL,
  `kyc_session_created_at` timestamp NULL DEFAULT NULL,
  `kyc_verified_at` timestamp NULL DEFAULT NULL,
  `kyc_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `kyc_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kyc_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `uname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upwd` text COLLATE utf8mb4_unicode_ci,
  `umoney` decimal(15,2) NOT NULL DEFAULT '0.00',
  `game_linked_at` timestamp NULL DEFAULT NULL,
  `game_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `referral_count` int NOT NULL DEFAULT '0',
  `total_commission_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pending_commission` decimal(15,2) NOT NULL DEFAULT '0.00',
  `max_referral_level` int NOT NULL DEFAULT '0',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `sms_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_language` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `phone_verified` tinyint(1) NOT NULL DEFAULT '1',
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_address` text COLLATE utf8mb4_unicode_ci,
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `user_referrals`
--

CREATE TABLE `user_referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `sponsor_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `commission_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_messages`
--

CREATE TABLE `whatsapp_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci,
  `message_type` enum('incoming','outgoing') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  `webhook_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_balances`
--
ALTER TABLE `account_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_balances_user_id_unique` (`user_id`),
  ADD KEY `account_balances_balance_index` (`balance`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_created_by_foreign` (`created_by`),
  ADD KEY `announcements_status_scheduled_at_index` (`status`,`scheduled_at`),
  ADD KEY `announcements_target_audience_priority_index` (`target_audience`,`priority`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_assigned_to_status_index` (`assigned_to`,`status`),
  ADD KEY `assignments_assigned_by_status_index` (`assigned_by`,`status`),
  ADD KEY `assignments_lead_id_index` (`lead_id`),
  ADD KEY `assignments_assigned_at_index` (`assigned_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `commission_settings`
--
ALTER TABLE `commission_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `commission_settings_level_unique` (`level`),
  ADD KEY `commission_settings_level_is_active_index` (`level`,`is_active`),
  ADD KEY `commission_settings_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `commission_settings_level_index` (`level`);

--
-- Indexes for table `commission_structures`
--
ALTER TABLE `commission_structures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `commission_structures_level_unique` (`level`),
  ADD KEY `commission_structures_is_active_index` (`is_active`);

--
-- Indexes for table `cryptocurrencies`
--
ALTER TABLE `cryptocurrencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cryptocurrencies_symbol_unique` (`symbol`),
  ADD KEY `cryptocurrencies_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `crypto_wallets`
--
ALTER TABLE `crypto_wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `crypto_wallets_user_id_currency_unique` (`user_id`,`currency`),
  ADD KEY `crypto_wallets_user_id_is_active_index` (`user_id`,`is_active`),
  ADD KEY `crypto_wallets_currency_index` (`currency`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_templates_slug_unique` (`slug`),
  ADD KEY `email_templates_created_by_foreign` (`created_by`),
  ADD KEY `email_templates_updated_by_foreign` (`updated_by`),
  ADD KEY `email_templates_category_is_active_index` (`category`,`is_active`),
  ADD KEY `email_templates_slug_index` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_created_by_foreign` (`created_by`),
  ADD KEY `faqs_updated_by_foreign` (`updated_by`),
  ADD KEY `faqs_status_category_index` (`status`,`category`),
  ADD KEY `faqs_sort_order_status_index` (`sort_order`,`status`),
  ADD KEY `faqs_is_featured_index` (`is_featured`);

--
-- Indexes for table `followups`
--
ALTER TABLE `followups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `followups_lead_id_followup_date_index` (`lead_id`,`followup_date`),
  ADD KEY `followups_followup_date_completed_index` (`followup_date`,`completed`),
  ADD KEY `followups_created_by_index` (`created_by`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `forms_slug_unique` (`slug`),
  ADD KEY `forms_is_active_created_at_index` (`is_active`,`created_at`),
  ADD KEY `forms_slug_index` (`slug`),
  ADD KEY `forms_created_by_index` (`created_by`);

--
-- Indexes for table `form_submissions`
--
ALTER TABLE `form_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_submissions_form_id_created_at_index` (`form_id`,`created_at`),
  ADD KEY `form_submissions_status_created_at_index` (`status`,`created_at`),
  ADD KEY `form_submissions_lead_id_index` (`lead_id`);

--
-- Indexes for table `investment_plans`
--
ALTER TABLE `investment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_plans_is_active_sort_order_index` (`sort_order`),
  ADD KEY `investment_plans_status_sort_order_index` (`status`,`sort_order`),
  ADD KEY `investment_plans_status_index` (`status`);

--
-- Indexes for table `investment_plan_profit_sharing`
--
ALTER TABLE `investment_plan_profit_sharing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_tier_unique` (`investment_plan_id`,`investment_plan_tier_id`),
  ADD KEY `investment_plan_profit_sharing_investment_plan_id_index` (`investment_plan_id`),
  ADD KEY `investment_plan_profit_sharing_investment_plan_tier_id_index` (`investment_plan_tier_id`),
  ADD KEY `investment_plan_profit_sharing_is_active_index` (`is_active`);

--
-- Indexes for table `investment_plan_tiers`
--
ALTER TABLE `investment_plan_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `investment_plan_tiers_investment_plan_id_tier_level_unique` (`investment_plan_id`,`tier_level`),
  ADD KEY `investment_plan_tiers_investment_plan_id_tier_level_index` (`investment_plan_id`,`tier_level`),
  ADD KEY `investment_plan_tiers_investment_plan_id_is_active_index` (`investment_plan_id`,`is_active`);

--
-- Indexes for table `investment_returns`
--
ALTER TABLE `investment_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_returns_user_investment_id_foreign` (`user_investment_id`),
  ADD KEY `investment_returns_user_id_status_index` (`user_id`,`status`),
  ADD KEY `investment_returns_status_due_date_index` (`status`,`due_date`),
  ADD KEY `investment_returns_transaction_id_index` (`transaction_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kyc_verifications_session_id_unique` (`session_id`),
  ADD KEY `kyc_verifications_user_id_status_index` (`user_id`,`status`),
  ADD KEY `kyc_verifications_session_id_index` (`session_id`);

--
-- Indexes for table `leaderboards`
--
ALTER TABLE `leaderboards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leaderboards_created_by_foreign` (`created_by`),
  ADD KEY `leaderboards_prizes_distributed_by_foreign` (`prizes_distributed_by`),
  ADD KEY `leaderboards_status_start_date_end_date_index` (`status`,`start_date`,`end_date`),
  ADD KEY `leaderboards_status_show_to_users_index` (`status`,`show_to_users`),
  ADD KEY `leaderboards_type_status_index` (`type`,`status`);

--
-- Indexes for table `leaderboard_positions`
--
ALTER TABLE `leaderboard_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leaderboard_positions_leaderboard_id_user_id_unique` (`leaderboard_id`,`user_id`),
  ADD KEY `leaderboard_positions_leaderboard_id_position_index` (`leaderboard_id`,`position`),
  ADD KEY `leaderboard_positions_user_id_prize_awarded_index` (`user_id`,`prize_awarded`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_status_created_at_index` (`status`,`created_at`),
  ADD KEY `leads_source_created_at_index` (`source`,`created_at`),
  ADD KEY `leads_created_by_index` (`created_by`);

--
-- Indexes for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_activities_lead_id_created_at_index` (`lead_id`,`created_at`),
  ADD KEY `lead_activities_activity_type_created_at_index` (`activity_type`,`created_at`),
  ADD KEY `lead_activities_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_logs_user_id_login_at_index` (`user_id`,`login_at`),
  ADD KEY `login_logs_ip_address_index` (`ip_address`),
  ADD KEY `login_logs_is_successful_index` (`is_successful`);

--
-- Indexes for table `mass_email_campaigns`
--
ALTER TABLE `mass_email_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mass_email_campaigns_user_id_foreign` (`user_id`),
  ADD KEY `mass_email_campaigns_cancelled_by_foreign` (`cancelled_by`),
  ADD KEY `mass_email_campaigns_status_created_at_index` (`status`,`created_at`),
  ADD KEY `mass_email_campaigns_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `mass_email_campaigns_scheduled_at_status_index` (`scheduled_at`,`status`),
  ADD KEY `mass_email_campaigns_name_index` (`name`),
  ADD KEY `mass_email_campaigns_status_index` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mlm_tables`
--
ALTER TABLE `mlm_tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_settings_class_name_unique` (`class_name`),
  ADD KEY `notification_settings_class_name_is_active_index` (`class_name`,`is_active`),
  ADD KEY `notification_settings_last_used_at_index` (`last_used_at`),
  ADD KEY `notification_settings_usage_count_index` (`usage_count`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `plan_profit_sharing`
--
ALTER TABLE `plan_profit_sharing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_profit_idx` (`investment_plan_id`,`investment_plan_tier_id`),
  ADD KEY `plan_profit_plan_idx` (`investment_plan_id`),
  ADD KEY `plan_profit_tier_idx` (`investment_plan_tier_id`);

--
-- Indexes for table `profit_sharing_txns`
--
ALTER TABLE `profit_sharing_txns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profit_txn_beneficiary_idx` (`beneficiary_user_id`,`status`),
  ADD KEY `profit_txn_source_idx` (`source_user_id`,`commission_level`),
  ADD KEY `profit_txn_investment_idx` (`user_investment_id`),
  ADD KEY `profit_txn_status_idx` (`status`);

--
-- Indexes for table `push_notification_logs`
--
ALTER TABLE `push_notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `push_notification_logs_admin_id_index` (`admin_id`),
  ADD KEY `push_notification_logs_type_index` (`type`),
  ADD KEY `push_notification_logs_created_at_index` (`created_at`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `push_subscriptions_user_id_endpoint_unique` (`user_id`,`endpoint`);

--
-- Indexes for table `referral_tree`
--
ALTER TABLE `referral_tree`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_tree_sponsor_id_user_id_level_unique` (`sponsor_id`,`user_id`,`level`),
  ADD KEY `referral_tree_sponsor_id_level_index` (`sponsor_id`,`level`),
  ADD KEY `referral_tree_user_id_index` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_category_index` (`category`),
  ADD KEY `settings_key_category_index` (`key`,`category`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  ADD KEY `support_tickets_last_reply_by_foreign` (`last_reply_by`),
  ADD KEY `support_tickets_status_priority_index` (`status`,`priority`),
  ADD KEY `support_tickets_user_id_index` (`user_id`),
  ADD KEY `support_tickets_assigned_to_index` (`assigned_to`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_replies_user_id_foreign` (`user_id`),
  ADD KEY `support_ticket_replies_ticket_id_index` (`ticket_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_transaction_id_unique` (`transaction_id`),
  ADD KEY `transactions_user_id_type_status_index` (`user_id`,`type`,`status`),
  ADD KEY `transactions_transaction_id_index` (`transaction_id`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_type_index` (`type`),
  ADD KEY `transactions_created_at_index` (`created_at`),
  ADD KEY `idx_transactions_processed_by` (`processed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_sponsor_id_index` (`sponsor_id`),
  ADD KEY `users_referral_code_index` (`referral_code`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_username_index` (`username`),
  ADD KEY `users_phone_index` (`phone`),
  ADD KEY `idx_users_blocked_at` (`blocked_at`),
  ADD KEY `idx_users_block_reason` (`block_reason`),
  ADD KEY `idx_users_block_expires_at` (`block_expires_at`),
  ADD KEY `idx_users_status_blocked_at` (`status`,`blocked_at`);

--
-- Indexes for table `user_announcement_views`
--
ALTER TABLE `user_announcement_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_announcement_views_user_id_announcement_id_unique` (`user_id`,`announcement_id`),
  ADD KEY `user_announcement_views_announcement_id_foreign` (`announcement_id`);

--
-- Indexes for table `user_earnings`
--
ALTER TABLE `user_earnings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_earnings_user_id_unique` (`user_id`),
  ADD KEY `user_earnings_last_earning_date_index` (`last_earning_date`);

--
-- Indexes for table `user_investments`
--
ALTER TABLE `user_investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_investments_investment_plan_id_foreign` (`investment_plan_id`),
  ADD KEY `user_investments_user_id_status_index` (`user_id`,`status`),
  ADD KEY `user_investments_status_end_date_index` (`status`),
  ADD KEY `user_investments_investment_plan_id_status_index` (`investment_plan_id`,`status`),
  ADD KEY `user_investments_status_ends_at_index` (`status`,`ends_at`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  ADD KEY `user_profiles_country_index` (`country`),
  ADD KEY `user_profiles_kyc_status_index` (`kyc_status`),
  ADD KEY `user_profiles_level_index` (`level`),
  ADD KEY `user_profiles_kyc_verified_at_index` (`kyc_verified_at`),
  ADD KEY `user_profiles_phone_verified_index` (`phone_verified`),
  ADD KEY `user_profiles_uname_index` (`uname`);

--
-- Indexes for table `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_referrals_sponsor_id_user_id_unique` (`sponsor_id`,`user_id`),
  ADD KEY `user_referrals_sponsor_id_status_index` (`sponsor_id`,`status`),
  ADD KEY `user_referrals_user_id_index` (`user_id`),
  ADD KEY `user_referrals_level_index` (`level`),
  ADD KEY `user_referrals_created_at_index` (`created_at`),
  ADD KEY `user_referrals_updated_at_index` (`updated_at`),
  ADD KEY `user_referrals_commission_earned_index` (`commission_earned`),
  ADD KEY `user_referrals_sponsor_status_created_index` (`sponsor_id`,`status`,`created_at`),
  ADD KEY `user_referrals_status_created_index` (`status`,`created_at`);

--
-- Indexes for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_messages_user_phone_index` (`user_phone`),
  ADD KEY `whatsapp_messages_message_type_index` (`message_type`),
  ADD KEY `whatsapp_messages_created_at_index` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_balances`
--
ALTER TABLE `account_balances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_settings`
--
ALTER TABLE `commission_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_structures`
--
ALTER TABLE `commission_structures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cryptocurrencies`
--
ALTER TABLE `cryptocurrencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crypto_wallets`
--
ALTER TABLE `crypto_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_submissions`
--
ALTER TABLE `form_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plans`
--
ALTER TABLE `investment_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plan_profit_sharing`
--
ALTER TABLE `investment_plan_profit_sharing`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plan_tiers`
--
ALTER TABLE `investment_plan_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_returns`
--
ALTER TABLE `investment_returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaderboards`
--
ALTER TABLE `leaderboards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaderboard_positions`
--
ALTER TABLE `leaderboard_positions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_activities`
--
ALTER TABLE `lead_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mass_email_campaigns`
--
ALTER TABLE `mass_email_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_tables`
--
ALTER TABLE `mlm_tables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plan_profit_sharing`
--
ALTER TABLE `plan_profit_sharing`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profit_sharing_txns`
--
ALTER TABLE `profit_sharing_txns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_notification_logs`
--
ALTER TABLE `push_notification_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_tree`
--
ALTER TABLE `referral_tree`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_announcement_views`
--
ALTER TABLE `user_announcement_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_earnings`
--
ALTER TABLE `user_earnings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_investments`
--
ALTER TABLE `user_investments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_referrals`
--
ALTER TABLE `user_referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_balances`
--
ALTER TABLE `account_balances`
  ADD CONSTRAINT `account_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crypto_wallets`
--
ALTER TABLE `crypto_wallets`
  ADD CONSTRAINT `crypto_wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD CONSTRAINT `email_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faqs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `followups`
--
ALTER TABLE `followups`
  ADD CONSTRAINT `followups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `followups_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forms`
--
ALTER TABLE `forms`
  ADD CONSTRAINT `forms_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `form_submissions`
--
ALTER TABLE `form_submissions`
  ADD CONSTRAINT `form_submissions_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `form_submissions_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `investment_plan_profit_sharing`
--
ALTER TABLE `investment_plan_profit_sharing`
  ADD CONSTRAINT `investment_plan_profit_sharing_investment_plan_id_foreign` FOREIGN KEY (`investment_plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `investment_plan_profit_sharing_investment_plan_tier_id_foreign` FOREIGN KEY (`investment_plan_tier_id`) REFERENCES `investment_plan_tiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investment_plan_tiers`
--
ALTER TABLE `investment_plan_tiers`
  ADD CONSTRAINT `investment_plan_tiers_investment_plan_id_foreign` FOREIGN KEY (`investment_plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investment_returns`
--
ALTER TABLE `investment_returns`
  ADD CONSTRAINT `investment_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `investment_returns_user_investment_id_foreign` FOREIGN KEY (`user_investment_id`) REFERENCES `user_investments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD CONSTRAINT `kyc_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboards`
--
ALTER TABLE `leaderboards`
  ADD CONSTRAINT `leaderboards_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaderboards_prizes_distributed_by_foreign` FOREIGN KEY (`prizes_distributed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leaderboard_positions`
--
ALTER TABLE `leaderboard_positions`
  ADD CONSTRAINT `leaderboard_positions_leaderboard_id_foreign` FOREIGN KEY (`leaderboard_id`) REFERENCES `leaderboards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaderboard_positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD CONSTRAINT `lead_activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mass_email_campaigns`
--
ALTER TABLE `mass_email_campaigns`
  ADD CONSTRAINT `mass_email_campaigns_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mass_email_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mass_email_campaigns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plan_profit_sharing`
--
ALTER TABLE `plan_profit_sharing`
  ADD CONSTRAINT `plan_profit_sharing_investment_plan_id_foreign` FOREIGN KEY (`investment_plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plan_profit_sharing_investment_plan_tier_id_foreign` FOREIGN KEY (`investment_plan_tier_id`) REFERENCES `investment_plan_tiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profit_sharing_txns`
--
ALTER TABLE `profit_sharing_txns`
  ADD CONSTRAINT `profit_sharing_txns_beneficiary_user_id_foreign` FOREIGN KEY (`beneficiary_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profit_sharing_txns_source_user_id_foreign` FOREIGN KEY (`source_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profit_sharing_txns_user_investment_id_foreign` FOREIGN KEY (`user_investment_id`) REFERENCES `user_investments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `push_notification_logs`
--
ALTER TABLE `push_notification_logs`
  ADD CONSTRAINT `push_notification_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_tree`
--
ALTER TABLE `referral_tree`
  ADD CONSTRAINT `referral_tree_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_tree_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_last_reply_by_foreign` FOREIGN KEY (`last_reply_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD CONSTRAINT `support_ticket_replies_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_announcement_views`
--
ALTER TABLE `user_announcement_views`
  ADD CONSTRAINT `user_announcement_views_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_announcement_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_earnings`
--
ALTER TABLE `user_earnings`
  ADD CONSTRAINT `user_earnings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_investments`
--
ALTER TABLE `user_investments`
  ADD CONSTRAINT `user_investments_investment_plan_id_foreign` FOREIGN KEY (`investment_plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_investments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD CONSTRAINT `user_referrals_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_referrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
