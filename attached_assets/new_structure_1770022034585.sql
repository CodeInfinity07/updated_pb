-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 02, 2026 at 08:46 AM
-- Server version: 8.0.44-0ubuntu0.22.04.1
-- PHP Version: 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onyxrock`
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
-- Table structure for table `admin_chat_stats`
--

CREATE TABLE `admin_chat_stats` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `total_chats_handled` int NOT NULL DEFAULT '0',
  `chats_closed_today` int NOT NULL DEFAULT '0',
  `average_response_time` int DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `stats_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_role_permission`
--

CREATE TABLE `admin_role_permission` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `announcement_type` varchar(255) NOT NULL DEFAULT 'text',
  `target_audience` varchar(255) NOT NULL DEFAULT 'all',
  `target_user_ids` longtext,
  `priority` int NOT NULL DEFAULT '1',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `show_once` tinyint(1) NOT NULL DEFAULT '1',
  `is_dismissible` tinyint(1) NOT NULL DEFAULT '1',
  `button_text` varchar(255) NOT NULL DEFAULT 'Got it',
  `button_link` varchar(255) DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('open','closed','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `closed_at` timestamp NULL DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `admin_last_read_at` timestamp NULL DEFAULT NULL,
  `user_last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_settings`
--

CREATE TABLE `commission_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `min_investment` decimal(15,2) NOT NULL DEFAULT '0.00',
  `min_direct_referrals` int NOT NULL DEFAULT '0',
  `min_indirect_referrals` int NOT NULL DEFAULT '0',
  `commission_level_1` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_level_2` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_level_3` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(7) DEFAULT NULL,
  `description` text,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `mailable_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` enum('transaction','investment','kyc','referral','support','account','system') NOT NULL DEFAULT 'system',
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `variables` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `question` varchar(255) NOT NULL,
  `answer` longtext NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `tags` longtext,
  `created_by` bigint UNSIGNED NOT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `standard_fields` json DEFAULT NULL,
  `custom_fields` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `submissions_count` int NOT NULL DEFAULT '0',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_submissions`
--

CREATE TABLE `form_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `form_id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED DEFAULT NULL,
  `form_data` json NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','processed','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `impersonation_logs`
--

CREATE TABLE `impersonation_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `impersonated_user_id` bigint UNSIGNED NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_expiry_settings`
--

CREATE TABLE `investment_expiry_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plans`
--

CREATE TABLE `investment_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `minimum_amount` decimal(15,2) NOT NULL,
  `maximum_amount` decimal(15,2) DEFAULT NULL,
  `roi_percentage` decimal(5,2) NOT NULL,
  `duration_days` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'daily',
  `principal_return` tinyint(1) DEFAULT '0',
  `principal_hold` double DEFAULT '0',
  `parent_plan_id` bigint UNSIGNED DEFAULT NULL,
  `referral_percentage` double DEFAULT '0',
  `details` json DEFAULT NULL,
  `diff_in_seconds` int DEFAULT '86400',
  `old_package_id` int DEFAULT NULL,
  `interest_rate` decimal(8,2) DEFAULT '0.00',
  `interest_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'daily',
  `return_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'daily',
  `capital_return` tinyint(1) DEFAULT '1',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `total_investors` int DEFAULT '0',
  `total_invested` decimal(20,2) DEFAULT '0.00',
  `features` json DEFAULT NULL,
  `badge` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_scheme` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_tiered` tinyint(1) DEFAULT '0',
  `max_tier_level` int DEFAULT '1',
  `base_interest_rate` decimal(8,2) DEFAULT '0.00',
  `tier_settings` json DEFAULT NULL,
  `profit_sharing_enabled` tinyint(1) DEFAULT '0',
  `roi_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'fixed',
  `min_interest_rate` decimal(8,4) DEFAULT NULL,
  `max_interest_rate` decimal(8,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plan_profit_sharings`
--

CREATE TABLE `investment_plan_profit_sharings` (
  `id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `tier_id` bigint UNSIGNED DEFAULT NULL,
  `percentage` decimal(8,2) DEFAULT '0.00',
  `frequency` varchar(50) DEFAULT 'daily',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plan_tiers`
--

CREATE TABLE `investment_plan_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `tier_level` int NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `minimum_amount` decimal(20,2) DEFAULT '0.00',
  `maximum_amount` decimal(20,2) DEFAULT '0.00',
  `interest_rate` decimal(8,2) DEFAULT '0.00',
  `features` json DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_returns`
--

CREATE TABLE `investment_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_investment_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `type` varchar(50) DEFAULT 'daily_return',
  `status` varchar(50) DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `raw_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboards`
--

CREATE TABLE `leaderboards` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` enum('active','inactive','completed') NOT NULL DEFAULT 'inactive',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `show_to_users` tinyint(1) NOT NULL DEFAULT '1',
  `max_positions` int NOT NULL DEFAULT '10',
  `referral_type` varchar(50) NOT NULL DEFAULT 'all',
  `max_referral_level` int DEFAULT NULL,
  `min_investment_amount` decimal(15,2) DEFAULT NULL,
  `type` enum('competitive','target') NOT NULL DEFAULT 'competitive',
  `target_referrals` int DEFAULT NULL,
  `target_prize_amount` decimal(10,2) DEFAULT NULL,
  `target_tiers` json DEFAULT NULL,
  `max_winners` int DEFAULT NULL,
  `prize_structure` longtext,
  `prizes_distributed` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `prizes_distributed_at` timestamp NULL DEFAULT NULL,
  `prizes_distributed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `prize_approved` tinyint(1) NOT NULL DEFAULT '0',
  `prize_approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `prize_approved_by` bigint UNSIGNED DEFAULT NULL,
  `prize_claimed` tinyint(1) NOT NULL DEFAULT '0',
  `prize_claimed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `recipient_groups` longtext,
  `specific_users` longtext,
  `total_recipients` int NOT NULL DEFAULT '0',
  `emails_sent` int NOT NULL DEFAULT '0',
  `emails_failed` int NOT NULL DEFAULT '0',
  `status` enum('pending','scheduled','sending','completed','cancelled','failed') NOT NULL DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `cancelled_by` bigint UNSIGNED DEFAULT NULL,
  `error_message` text,
  `metadata` longtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `channels` json NOT NULL,
  `settings` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_count` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `profit_sharing_transactions`
--

CREATE TABLE `profit_sharing_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `tier_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_notification_logs`
--

CREATE TABLE `push_notification_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `recipients` json DEFAULT NULL,
  `recipients_count` int NOT NULL DEFAULT '0',
  `target_type` varchar(255) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `content_encoding` varchar(255) DEFAULT NULL,
  `keys` longtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `display_order` int NOT NULL DEFAULT '0',
  `min_self_deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `min_direct_members` int NOT NULL DEFAULT '0',
  `min_direct_member_investment` decimal(15,2) NOT NULL DEFAULT '100.00',
  `min_team_members` int NOT NULL DEFAULT '0',
  `min_team_member_investment` decimal(15,2) NOT NULL DEFAULT '100.00',
  `reward_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_commission_levels`
--

CREATE TABLE `referral_commission_levels` (
  `id` bigint UNSIGNED NOT NULL,
  `level` int NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Table structure for table `salary_applications`
--

CREATE TABLE `salary_applications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `salary_stage_id` bigint UNSIGNED NOT NULL,
  `applied_at` timestamp NOT NULL,
  `baseline_team_count` int NOT NULL,
  `baseline_direct_count` int NOT NULL,
  `baseline_self_deposit` decimal(15,2) NOT NULL,
  `current_period_start` date NOT NULL,
  `current_period_end` date NOT NULL,
  `current_target_team` int NOT NULL,
  `current_target_direct_new` int NOT NULL DEFAULT '3',
  `months_completed` int NOT NULL DEFAULT '0',
  `status` enum('active','failed','graduated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `failed_at` timestamp NULL DEFAULT NULL,
  `graduated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_monthly_evaluations`
--

CREATE TABLE `salary_monthly_evaluations` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `salary_application_id` bigint UNSIGNED NOT NULL,
  `salary_stage_id` bigint UNSIGNED NOT NULL,
  `month_number` int NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `target_team` int NOT NULL,
  `starting_team_count` int NOT NULL DEFAULT '0',
  `achieved_team_new` int NOT NULL,
  `target_direct_new` int NOT NULL,
  `achieved_direct_new` int NOT NULL,
  `starting_direct_count` int NOT NULL,
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `salary_amount` decimal(15,2) DEFAULT NULL,
  `salary_paid` tinyint(1) NOT NULL DEFAULT '0',
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_payouts`
--

CREATE TABLE `salary_payouts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `salary_stage_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `payout_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_stages`
--

CREATE TABLE `salary_stages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stage_order` int NOT NULL,
  `direct_members_required` int NOT NULL,
  `self_deposit_required` decimal(15,2) NOT NULL,
  `team_required` int NOT NULL,
  `salary_amount` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
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
  `ticket_number` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','pending_user','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `category` varchar(255) DEFAULT NULL,
  `attachments` longtext,
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `last_reply_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `attachments` longtext,
  `is_internal_note` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tracking_pixels`
--

CREATE TABLE `tracking_pixels` (
  `id` bigint UNSIGNED NOT NULL,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pixel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pixel_code` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crypto_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crypto_txid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `excluded_from_stats` tinyint(1) NOT NULL DEFAULT '0',
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
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_level` int NOT NULL DEFAULT '0',
  `total_invested` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `level_updated_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `google2fa_secret` text COLLATE utf8mb4_unicode_ci,
  `google2fa_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `google2fa_enabled_at` timestamp NULL DEFAULT NULL,
  `push_notifications_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `last_push_subscription_at` timestamp NULL DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `bot_activated_at` timestamp NULL DEFAULT NULL,
  `withdraw_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `roi_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `commission_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `referral_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `admin_role_id` bigint UNSIGNED DEFAULT NULL
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `old_package_id` int DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `investment_plan_id` bigint UNSIGNED NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'investment',
  `amount` decimal(15,2) NOT NULL,
  `roi_percentage` decimal(5,2) DEFAULT NULL,
  `duration_days` int NOT NULL,
  `total_return` decimal(15,2) NOT NULL DEFAULT '0.00',
  `daily_return` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `last_payout_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `paid_return` decimal(20,2) DEFAULT '0.00',
  `earnings_accumulated` decimal(15,2) DEFAULT '0.00',
  `commission_earned` decimal(15,2) DEFAULT '0.00',
  `expiry_multiplier` int DEFAULT '3',
  `bot_fee_applied` tinyint(1) DEFAULT '0',
  `status_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `kyc_documents` json DEFAULT NULL,
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
  `metadata` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `uname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upwd` text COLLATE utf8mb4_unicode_ci,
  `umoney` decimal(15,2) NOT NULL DEFAULT '0.00',
  `game_linked_at` timestamp NULL DEFAULT NULL,
  `game_settings` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_ranks`
--

CREATE TABLE `user_ranks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `achieved_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reward_paid` tinyint(1) NOT NULL DEFAULT '0',
  `reward_paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `user_salary_progress`
--

CREATE TABLE `user_salary_progress` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `current_stage` int NOT NULL DEFAULT '0',
  `last_completed_stage` int NOT NULL DEFAULT '0',
  `used_referrals` json DEFAULT NULL,
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
  `webhook_data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Indexes for table `admin_chat_stats`
--
ALTER TABLE `admin_chat_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_chat_stats_admin_id_unique` (`admin_id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_permissions_slug_unique` (`slug`),
  ADD KEY `admin_permissions_module_index` (`module`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_roles_name_unique` (`name`),
  ADD UNIQUE KEY `admin_roles_slug_unique` (`slug`);

--
-- Indexes for table `admin_role_permission`
--
ALTER TABLE `admin_role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `admin_role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`,`scheduled_at`),
  ADD KEY `target_audience` (`target_audience`,`priority`);

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
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_conversations_user_id_foreign` (`user_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  ADD KEY `chat_messages_sender_type_sender_id_index` (`sender_type`,`sender_id`);

--
-- Indexes for table `commission_settings`
--
ALTER TABLE `commission_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level` (`level`),
  ADD KEY `level_2` (`level`,`is_active`),
  ADD KEY `is_active` (`is_active`,`sort_order`);

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
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_status_created_at_index` (`status`,`created_at`),
  ADD KEY `email_logs_type_status_index` (`type`,`status`),
  ADD KEY `email_logs_user_id_index` (`user_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category` (`category`,`is_active`);

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
  ADD KEY `status` (`status`,`category`),
  ADD KEY `sort_order` (`sort_order`,`status`),
  ADD KEY `is_featured` (`is_featured`);

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
-- Indexes for table `impersonation_logs`
--
ALTER TABLE `impersonation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `impersonation_logs_admin_id_index` (`admin_id`),
  ADD KEY `impersonation_logs_impersonated_user_id_index` (`impersonated_user_id`),
  ADD KEY `impersonation_logs_started_at_index` (`started_at`);

--
-- Indexes for table `investment_expiry_settings`
--
ALTER TABLE `investment_expiry_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `investment_plans`
--
ALTER TABLE `investment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_plans_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `investment_plan_profit_sharings`
--
ALTER TABLE `investment_plan_profit_sharings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_plan_id` (`investment_plan_id`);

--
-- Indexes for table `investment_plan_tiers`
--
ALTER TABLE `investment_plan_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investment_plan_id` (`investment_plan_id`);

--
-- Indexes for table `investment_returns`
--
ALTER TABLE `investment_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_investment_id` (`user_investment_id`),
  ADD KEY `status` (`status`),
  ADD KEY `due_date` (`due_date`);

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
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`,`start_date`,`end_date`),
  ADD KEY `status_2` (`status`,`show_to_users`),
  ADD KEY `type` (`type`,`status`);

--
-- Indexes for table `leaderboard_positions`
--
ALTER TABLE `leaderboard_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leaderboard_id` (`leaderboard_id`,`user_id`),
  ADD KEY `leaderboard_id_2` (`leaderboard_id`,`position`),
  ADD KEY `user_id` (`user_id`,`prize_awarded`),
  ADD KEY `leaderboard_positions_prize_approved_by_foreign` (`prize_approved_by`),
  ADD KEY `leaderboard_positions_user_id_prize_approved_prize_claimed_index` (`user_id`,`prize_approved`,`prize_claimed`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`,`created_at`),
  ADD KEY `created_by` (`created_by`,`created_at`),
  ADD KEY `scheduled_at` (`scheduled_at`,`status`),
  ADD KEY `name` (`name`),
  ADD KEY `status_2` (`status`);

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
-- Indexes for table `profit_sharing_transactions`
--
ALTER TABLE `profit_sharing_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `investment_plan_id` (`investment_plan_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `push_notification_logs`
--
ALTER TABLE `push_notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `type` (`type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `push_subscriptions_user_id_endpoint_unique` (`user_id`,`endpoint`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_commission_levels`
--
ALTER TABLE `referral_commission_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level` (`level`);

--
-- Indexes for table `referral_tree`
--
ALTER TABLE `referral_tree`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_tree_sponsor_id_user_id_level_unique` (`sponsor_id`,`user_id`,`level`),
  ADD KEY `referral_tree_sponsor_id_level_index` (`sponsor_id`,`level`),
  ADD KEY `referral_tree_user_id_index` (`user_id`);

--
-- Indexes for table `salary_applications`
--
ALTER TABLE `salary_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_applications_salary_stage_id_foreign` (`salary_stage_id`),
  ADD KEY `salary_applications_user_id_status_index` (`user_id`,`status`),
  ADD KEY `salary_applications_status_current_period_end_index` (`status`,`current_period_end`);

--
-- Indexes for table `salary_monthly_evaluations`
--
ALTER TABLE `salary_monthly_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sal_eval_app_month_unique` (`salary_application_id`,`month_number`),
  ADD KEY `salary_monthly_evaluations_salary_stage_id_foreign` (`salary_stage_id`),
  ADD KEY `sal_eval_user_period_idx` (`user_id`,`period_start`),
  ADD KEY `sal_eval_app_month_idx` (`salary_application_id`,`month_number`);

--
-- Indexes for table `salary_payouts`
--
ALTER TABLE `salary_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_payouts_salary_stage_id_foreign` (`salary_stage_id`),
  ADD KEY `salary_payouts_admin_id_foreign` (`admin_id`),
  ADD KEY `salary_payouts_user_id_salary_stage_id_index` (`user_id`,`salary_stage_id`);

--
-- Indexes for table `salary_stages`
--
ALTER TABLE `salary_stages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `salary_stages_stage_order_unique` (`stage_order`);

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
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `status` (`status`,`priority`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `last_reply_by` (`last_reply_by`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tracking_pixels`
--
ALTER TABLE `tracking_pixels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_pixels_platform_unique` (`platform`);

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
  ADD KEY `idx_users_status_blocked_at` (`status`,`blocked_at`),
  ADD KEY `users_admin_role_id_foreign` (`admin_role_id`);

--
-- Indexes for table `user_announcement_views`
--
ALTER TABLE `user_announcement_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`announcement_id`),
  ADD KEY `announcement_id` (`announcement_id`);

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
  ADD KEY `user_investments_status_end_date_index` (`status`,`end_date`),
  ADD KEY `user_investments_last_payout_date_index` (`last_payout_date`);

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
  ADD KEY `user_profiles_phone_verified_index` (`phone_verified`);

--
-- Indexes for table `user_ranks`
--
ALTER TABLE `user_ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_ranks_user_id_rank_id_unique` (`user_id`,`rank_id`),
  ADD KEY `user_ranks_rank_id_foreign` (`rank_id`),
  ADD KEY `user_ranks_user_id_achieved_at_index` (`user_id`,`achieved_at`);

--
-- Indexes for table `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_referrals_sponsor_id_user_id_unique` (`sponsor_id`,`user_id`),
  ADD KEY `user_referrals_sponsor_id_status_index` (`sponsor_id`,`status`),
  ADD KEY `user_referrals_user_id_index` (`user_id`),
  ADD KEY `user_referrals_level_index` (`level`);

--
-- Indexes for table `user_salary_progress`
--
ALTER TABLE `user_salary_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_salary_progress_user_id_unique` (`user_id`);

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
-- AUTO_INCREMENT for table `admin_chat_stats`
--
ALTER TABLE `admin_chat_stats`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_role_permission`
--
ALTER TABLE `admin_role_permission`
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
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
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
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
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
-- AUTO_INCREMENT for table `impersonation_logs`
--
ALTER TABLE `impersonation_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_expiry_settings`
--
ALTER TABLE `investment_expiry_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plans`
--
ALTER TABLE `investment_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plan_profit_sharings`
--
ALTER TABLE `investment_plan_profit_sharings`
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
-- AUTO_INCREMENT for table `profit_sharing_transactions`
--
ALTER TABLE `profit_sharing_transactions`
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
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_commission_levels`
--
ALTER TABLE `referral_commission_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_tree`
--
ALTER TABLE `referral_tree`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_applications`
--
ALTER TABLE `salary_applications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_monthly_evaluations`
--
ALTER TABLE `salary_monthly_evaluations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_payouts`
--
ALTER TABLE `salary_payouts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_stages`
--
ALTER TABLE `salary_stages`
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
-- AUTO_INCREMENT for table `tracking_pixels`
--
ALTER TABLE `tracking_pixels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `user_ranks`
--
ALTER TABLE `user_ranks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_referrals`
--
ALTER TABLE `user_referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_salary_progress`
--
ALTER TABLE `user_salary_progress`
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
-- Constraints for table `admin_chat_stats`
--
ALTER TABLE `admin_chat_stats`
  ADD CONSTRAINT `admin_chat_stats_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_role_permission`
--
ALTER TABLE `admin_role_permission`
  ADD CONSTRAINT `admin_role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crypto_wallets`
--
ALTER TABLE `crypto_wallets`
  ADD CONSTRAINT `crypto_wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `impersonation_logs`
--
ALTER TABLE `impersonation_logs`
  ADD CONSTRAINT `impersonation_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `impersonation_logs_impersonated_user_id_foreign` FOREIGN KEY (`impersonated_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD CONSTRAINT `kyc_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboard_positions`
--
ALTER TABLE `leaderboard_positions`
  ADD CONSTRAINT `leaderboard_positions_prize_approved_by_foreign` FOREIGN KEY (`prize_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `referral_tree`
--
ALTER TABLE `referral_tree`
  ADD CONSTRAINT `referral_tree_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_tree_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_applications`
--
ALTER TABLE `salary_applications`
  ADD CONSTRAINT `salary_applications_salary_stage_id_foreign` FOREIGN KEY (`salary_stage_id`) REFERENCES `salary_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_monthly_evaluations`
--
ALTER TABLE `salary_monthly_evaluations`
  ADD CONSTRAINT `salary_monthly_evaluations_salary_application_id_foreign` FOREIGN KEY (`salary_application_id`) REFERENCES `salary_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_monthly_evaluations_salary_stage_id_foreign` FOREIGN KEY (`salary_stage_id`) REFERENCES `salary_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_monthly_evaluations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_payouts`
--
ALTER TABLE `salary_payouts`
  ADD CONSTRAINT `salary_payouts_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `salary_payouts_salary_stage_id_foreign` FOREIGN KEY (`salary_stage_id`) REFERENCES `salary_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_payouts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_admin_role_id_foreign` FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `user_ranks`
--
ALTER TABLE `user_ranks`
  ADD CONSTRAINT `user_ranks_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_ranks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD CONSTRAINT `user_referrals_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_referrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_salary_progress`
--
ALTER TABLE `user_salary_progress`
  ADD CONSTRAINT `user_salary_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
