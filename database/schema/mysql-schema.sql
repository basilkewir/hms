/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `accountant_report_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accountant_report_overrides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aro_unique` (`user_id`,`report_type`,`metric_key`),
  CONSTRAINT `accountant_report_overrides_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day','on_leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'absent',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_user_id_date_unique` (`user_id`,`date`),
  CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bed_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bed_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `width_inches` decimal(5,2) DEFAULT NULL,
  `length_inches` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bed_types_name_unique` (`name`),
  UNIQUE KEY `bed_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_name_unique` (`name`),
  UNIQUE KEY `brands_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `breakfast_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `breakfast_menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `items` json DEFAULT NULL,
  `serving_time_start` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serving_time_end` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `available_online` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `budget_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `budget_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` date NOT NULL,
  `vendor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_expenses_budget_id_foreign` (`budget_id`),
  KEY `budget_expenses_created_by_foreign` (`created_by`),
  KEY `budget_expenses_approved_by_foreign` (`approved_by`),
  CONSTRAINT `budget_expenses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `budget_expenses_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(15,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','expired','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budgets_department_id_foreign` (`department_id`),
  KEY `budgets_approved_by_foreign` (`approved_by`),
  KEY `budgets_status_start_date_end_date_index` (`status`,`start_date`,`end_date`),
  KEY `budgets_category_id_department_id_index` (`category_id`,`department_id`),
  KEY `budgets_created_by_index` (`created_by`),
  CONSTRAINT `budgets_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `budgets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budgets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budgets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `building_wings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `building_wings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `building_wings_name_unique` (`name`),
  UNIQUE KEY `building_wings_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_drawer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_drawer_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL,
  `closing_balance` decimal(10,2) DEFAULT NULL,
  `expected_balance` decimal(10,2) DEFAULT NULL,
  `difference` decimal(10,2) DEFAULT NULL,
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_drawer_sessions_user_id_foreign` (`user_id`),
  CONSTRAINT `cash_drawer_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `concierge_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concierge_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NULL DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `concierge_requests_request_number_unique` (`request_number`),
  KEY `concierge_requests_created_by_foreign` (`created_by`),
  CONSTRAINT `concierge_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount percentage for this group',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_group_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hall_booking_preferences` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  KEY `customers_created_by_foreign` (`created_by`),
  KEY `customers_updated_by_foreign` (`updated_by`),
  KEY `customers_email_phone_index` (`email`,`phone`),
  KEY `customers_customer_group_id_foreign` (`customer_group_id`),
  CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_customer_group_id_foreign` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_documents_user_id_foreign` (`user_id`),
  KEY `delivery_documents_purchase_order_id_document_type_index` (`purchase_order_id`,`document_type`),
  CONSTRAINT `delivery_documents_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `applicable_to` enum('room','service','folio','pos','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'room',
  `auto_apply` tinyint(1) NOT NULL DEFAULT '0',
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `max_uses` int DEFAULT NULL,
  `per_guest_limit` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `requires_code` tinyint(1) NOT NULL DEFAULT '0',
  `is_stackable` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discounts_code_unique` (`code`),
  KEY `discounts_hotel_id_applicable_to_is_active_index` (`hotel_id`,`applicable_to`,`is_active`),
  KEY `discounts_code_index` (`code`),
  CONSTRAINT `discounts_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_payroll` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_period_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `regular_hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `holiday_hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `sick_hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `vacation_hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `regular_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `holiday_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gross_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `federal_tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `state_tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  `social_security` decimal(8,2) NOT NULL DEFAULT '0.00',
  `medicare` decimal(8,2) NOT NULL DEFAULT '0.00',
  `health_insurance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_payroll_payroll_period_id_user_id_unique` (`payroll_period_id`,`user_id`),
  KEY `employee_payroll_user_id_foreign` (`user_id`),
  CONSTRAINT `employee_payroll_payroll_period_id_foreign` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payroll_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `employee_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hire_date` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `position_id` bigint unsigned DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','casual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full_time',
  `base_salary` decimal(12,2) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `eligible_for_overtime` tinyint(1) NOT NULL DEFAULT '1',
  `pay_frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `employee_profiles_employee_code_unique` (`employee_code`),
  KEY `employee_profiles_hotel_id_foreign` (`hotel_id`),
  KEY `employee_profiles_department_id_foreign` (`department_id`),
  KEY `employee_profiles_position_id_foreign` (`position_id`),
  CONSTRAINT `employee_profiles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_profiles_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_profiles_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `work_shift_id` bigint unsigned NOT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `days_of_week` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_shifts_work_shift_id_foreign` (`work_shift_id`),
  KEY `employee_shifts_user_id_effective_date_index` (`user_id`,`effective_date`),
  CONSTRAINT `employee_shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_shifts_work_shift_id_foreign` FOREIGN KEY (`work_shift_id`) REFERENCES `work_shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3b82f6',
  `parent_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expense_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` bigint unsigned NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_documents_user_id_foreign` (`user_id`),
  KEY `expense_documents_expense_id_document_type_index` (`expense_id`,`document_type`),
  CONSTRAINT `expense_documents_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `pos_expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` bigint unsigned NOT NULL,
  `vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `payment_method` enum('cash','check','credit_card','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submitted_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by` bigint unsigned DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  KEY `expenses_submitted_by_foreign` (`submitted_by`),
  KEY `expenses_approved_by_foreign` (`approved_by`),
  KEY `expenses_paid_by_foreign` (`paid_by`),
  KEY `expenses_status_expense_date_index` (`status`,`expense_date`),
  KEY `expenses_expense_category_id_expense_date_index` (`expense_category_id`,`expense_date`),
  CONSTRAINT `expenses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `expenses_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  CONSTRAINT `expenses_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `floors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `floors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `floor_number` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `floors_floor_number_unique` (`floor_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `folio_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folio_charges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guest_folio_id` bigint unsigned NOT NULL,
  `charge_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `charge_date` date NOT NULL,
  `charge_time` time DEFAULT NULL,
  `quantity` decimal(8,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_by` bigint unsigned NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_voided` tinyint(1) NOT NULL DEFAULT '0',
  `voided_by` bigint unsigned DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `folio_charges_posted_by_foreign` (`posted_by`),
  KEY `folio_charges_voided_by_foreign` (`voided_by`),
  KEY `folio_charges_guest_folio_id_charge_date_index` (`guest_folio_id`,`charge_date`),
  KEY `folio_charges_charge_code_charge_date_index` (`charge_code`,`charge_date`),
  KEY `folio_charges_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `folio_charges_guest_folio_id_foreign` FOREIGN KEY (`guest_folio_id`) REFERENCES `guest_folios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folio_charges_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `folio_charges_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_booking_hall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_booking_hall` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_booking_id` bigint unsigned NOT NULL,
  `hall_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_booking_hall_group_booking_id_foreign` (`group_booking_id`),
  KEY `group_booking_hall_hall_id_foreign` (`hall_id`),
  CONSTRAINT `group_booking_hall_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_booking_hall_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_booking_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_booking_package` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_booking_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `optional_features` json DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_booking_package_group_booking_id_foreign` (`group_booking_id`),
  KEY `group_booking_package_package_id_foreign` (`package_id`),
  CONSTRAINT `group_booking_package_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_booking_package_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_guest_id` bigint unsigned NOT NULL,
  `contact_person_id` bigint unsigned DEFAULT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_rooms` int NOT NULL,
  `total_guests` int NOT NULL,
  `total_adults` int NOT NULL,
  `total_children` int NOT NULL DEFAULT '0',
  `group_discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `group_discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_group_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_amount` decimal(12,2) NOT NULL,
  `billing_type` enum('consolidated','individual','split') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'consolidated',
  `billing_instructions` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `group_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `availability_rules` json DEFAULT NULL,
  `booking_blackouts` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_bookings_group_number_unique` (`group_number`),
  KEY `group_bookings_primary_guest_id_foreign` (`primary_guest_id`),
  KEY `group_bookings_contact_person_id_foreign` (`contact_person_id`),
  KEY `group_bookings_created_by_foreign` (`created_by`),
  KEY `group_bookings_updated_by_foreign` (`updated_by`),
  KEY `group_bookings_check_in_date_check_out_date_index` (`check_in_date`,`check_out_date`),
  KEY `group_bookings_status_index` (`status`),
  CONSTRAINT `group_bookings_contact_person_id_foreign` FOREIGN KEY (`contact_person_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `group_bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `group_bookings_primary_guest_id_foreign` FOREIGN KEY (`primary_guest_id`) REFERENCES `guests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `group_bookings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `guest_folios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_folios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `folio_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `status` enum('open','closed','transferred','voided') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `folio_date` date NOT NULL,
  `room_charges` decimal(12,2) NOT NULL DEFAULT '0.00',
  `service_charges` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_folios_folio_number_unique` (`folio_number`),
  KEY `guest_folios_guest_id_foreign` (`guest_id`),
  KEY `guest_folios_room_id_foreign` (`room_id`),
  KEY `guest_folios_closed_by_foreign` (`closed_by`),
  KEY `guest_folios_status_folio_date_index` (`status`,`folio_date`),
  KEY `guest_folios_reservation_id_index` (`reservation_id`),
  KEY `guest_folios_hotel_id_foreign` (`hotel_id`),
  CONSTRAINT `guest_folios_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `guest_folios_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `guest_folios_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guest_folios_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `guest_folios_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `guest_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_types_name_unique` (`name`),
  UNIQUE KEY `guest_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `guests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `guest_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternate_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_address` text COLLATE utf8mb4_unicode_ci,
  `id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_issuing_authority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_issue_date` date DEFAULT NULL,
  `id_expiry_date` date DEFAULT NULL,
  `id_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_issuing_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_issue_date` date DEFAULT NULL,
  `passport_expiry_date` date DEFAULT NULL,
  `passport_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_issue_date` date DEFAULT NULL,
  `visa_expiry_date` date DEFAULT NULL,
  `visa_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `police_verification_status` enum('pending','verified','flagged','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `police_verification_notes` text COLLATE utf8mb4_unicode_ci,
  `police_verification_date` timestamp NULL DEFAULT NULL,
  `police_verification_officer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `police_case_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departure_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purpose_of_visit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_duration_days` int DEFAULT NULL,
  `total_companions` int DEFAULT NULL,
  `companion_details` json DEFAULT NULL,
  `vehicle_registration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_make_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `medical_conditions` text COLLATE utf8mb4_unicode_ci,
  `dietary_restrictions` text COLLATE utf8mb4_unicode_ci,
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT '0',
  `blacklist_reason` text COLLATE utf8mb4_unicode_ci,
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `guest_type_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guests_guest_id_unique` (`guest_id`),
  KEY `guests_created_by_foreign` (`created_by`),
  KEY `guests_updated_by_foreign` (`updated_by`),
  KEY `guests_id_type_id_number_index` (`id_type`,`id_number`),
  KEY `guests_passport_number_index` (`passport_number`),
  KEY `guests_police_verification_status_index` (`police_verification_status`),
  KEY `guests_first_name_last_name_index` (`first_name`,`last_name`),
  KEY `guests_phone_index` (`phone`),
  KEY `guests_nationality_index` (`nationality`),
  KEY `guests_guest_type_id_foreign` (`guest_type_id`),
  KEY `guests_hotel_id_foreign` (`hotel_id`),
  CONSTRAINT `guests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `guests_guest_type_id_foreign` FOREIGN KEY (`guest_type_id`) REFERENCES `guest_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `guests_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guests_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `analytics_date` date NOT NULL,
  `total_bookings` int NOT NULL,
  `total_guests` int NOT NULL,
  `total_revenue` decimal(10,2) NOT NULL,
  `average_booking_value` decimal(10,2) NOT NULL,
  `occupancy_rate` decimal(5,2) NOT NULL,
  `booking_sources` json DEFAULT NULL,
  `package_usage` json DEFAULT NULL,
  `cancellation_stats` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_analytics_hall_id_foreign` (`hall_id`),
  CONSTRAINT `hall_booking_analytics_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_availability_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_availability_calendar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `availability_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `available_slots` int NOT NULL DEFAULT '0',
  `booked_slots` int NOT NULL DEFAULT '0',
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `special_conditions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_availability_calendar_hall_id_foreign` (`hall_id`),
  CONSTRAINT `hall_booking_availability_calendar_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_cancellations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_cancellations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `cancellation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `cancellation_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `refund_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cancellation_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_cancellations_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_cancellations_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_cancellations_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_cancellations_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_cancellations_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_cancellations_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `discount_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_guests` int DEFAULT NULL,
  `max_usage` int DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_rules` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hall_booking_discounts_discount_code_unique` (`discount_code`),
  KEY `hall_booking_discounts_hall_id_foreign` (`hall_id`),
  CONSTRAINT `hall_booking_discounts_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `availability_date` date NOT NULL,
  `available_slots` int NOT NULL DEFAULT '0',
  `booked_slots` int NOT NULL DEFAULT '0',
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `special_conditions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100029` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100029` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `analytics_date` date NOT NULL,
  `total_views` int NOT NULL DEFAULT '0',
  `total_bookings` int NOT NULL DEFAULT '0',
  `conversion_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `average_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_analytics_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100123` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100123` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_analytics_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_calendar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `calendar_date` date NOT NULL,
  `available_slots` int NOT NULL,
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `calendar_data` json DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_calendar_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100055` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100055` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_calendar_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_discounts_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100129` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100129` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_discounts_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_exceptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `exception_date` date NOT NULL,
  `exception_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_details` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_exceptions_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100117` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100117` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_exceptions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `history_date` date NOT NULL,
  `availability_data` json NOT NULL,
  `change_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `change_description` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_history_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100125` (`group_booking_id`),
  KEY `hall_booking_group_availability_history_changed_by_foreign` (`changed_by`),
  CONSTRAINT `fk_gbid_100125` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_group_availability_history_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_inclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_inclusions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `inclusion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inclusion_details` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_inclusions_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100131` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100131` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_inclusions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `notification_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_details` json NOT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_notifications_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100121` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100121` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_notifications_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `preference_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preference_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_preferences_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100127` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100127` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_preferences_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_reviews_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100133` (`group_booking_id`),
  KEY `hall_booking_group_availability_reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `fk_gbid_100133` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_reviews_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `rule_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_rules_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100135` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100135` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_rules_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_availability_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_availability_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_availability_settings_hall_id_foreign` (`hall_id`),
  KEY `fk_gbid_100119` (`group_booking_id`),
  CONSTRAINT `fk_gbid_100119` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_availability_settings_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_inclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_inclusions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `inclusion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_optional` tinyint(1) NOT NULL DEFAULT '0',
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_inclusions_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_group_inclusions_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_group_inclusions_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_inclusions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `min_guests` int NOT NULL,
  `max_guests` int NOT NULL,
  `base_price_per_guest` decimal(10,2) NOT NULL,
  `group_discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `final_price_per_guest` decimal(10,2) NOT NULL,
  `pricing_rules` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_pricing_hall_id_foreign` (`hall_id`),
  CONSTRAINT `hall_booking_group_pricing_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_group_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_group_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned NOT NULL,
  `reviewer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `review_details` json DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_group_reviews_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_group_reviews_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_group_reviews_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_group_reviews_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `performed_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_history_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_history_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_history_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_history_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_history_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_history_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `notification_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_data` json DEFAULT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_notifications_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_notifications_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_notifications_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_notifications_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_notifications_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_notifications_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `availability_date` date NOT NULL,
  `available_slots` int NOT NULL DEFAULT '0',
  `booked_slots` int NOT NULL DEFAULT '0',
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `special_conditions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `analytics_date` date NOT NULL,
  `total_views` int NOT NULL DEFAULT '0',
  `total_bookings` int NOT NULL DEFAULT '0',
  `conversion_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `average_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_analytics_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_analytics_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_analytics_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_analytics_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_calendar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `calendar_date` date NOT NULL,
  `available_slots` int NOT NULL,
  `price_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `calendar_data` json DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_calendar_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_calendar_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_calendar_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_calendar_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_discounts_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_discounts_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_discounts_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_discounts_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_exceptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `exception_date` date NOT NULL,
  `exception_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_details` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_exceptions_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_exceptions_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_exceptions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_exceptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `history_date` date NOT NULL,
  `availability_data` json NOT NULL,
  `change_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `change_description` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_history_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_history_package_id_foreign` (`package_id`),
  KEY `hall_booking_package_availability_history_changed_by_foreign` (`changed_by`),
  CONSTRAINT `hall_booking_package_availability_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_package_availability_history_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_history_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_inclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_inclusions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `inclusion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inclusion_details` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_inclusions_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_inclusions_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_inclusions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_inclusions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `notification_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_details` json NOT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_notifications_hall_id_foreign` (`hall_id`),
  KEY `fk_pid_100120` (`package_id`),
  CONSTRAINT `fk_pid_100120` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_notifications_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `preference_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preference_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_preferences_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_preferences_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_preferences_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_preferences_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_reviews_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_reviews_package_id_foreign` (`package_id`),
  KEY `hall_booking_package_availability_reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `hall_booking_package_availability_reviews_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_reviews_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `rule_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_rules_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_rules_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_rules_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_rules_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_availability_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_availability_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_availability_settings_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_availability_settings_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_availability_settings_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_availability_settings_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_inclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_inclusions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `inclusion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_optional` tinyint(1) NOT NULL DEFAULT '0',
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_inclusions_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_inclusions_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_inclusions_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_inclusions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `package_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `final_price` decimal(10,2) NOT NULL,
  `pricing_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_pricing_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_pricing_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_pricing_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_pricing_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_package_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_package_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `reviewer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `review_details` json DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_package_reviews_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_package_reviews_package_id_foreign` (`package_id`),
  CONSTRAINT `hall_booking_package_reviews_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_package_reviews_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_payments_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_payments_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_payments_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_payments_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_payments_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_payments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `booking_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `total_guests` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_reports_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_reports_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_reports_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_reports_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_reports_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_reports_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_booking_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_booking_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hall_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `reviewer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci,
  `review_details` json DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hall_booking_reviews_hall_id_foreign` (`hall_id`),
  KEY `hall_booking_reviews_reservation_id_foreign` (`reservation_id`),
  KEY `hall_booking_reviews_group_booking_id_foreign` (`group_booking_id`),
  CONSTRAINT `hall_booking_reviews_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_booking_reviews_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hall_booking_reviews_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hall_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hall_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hall_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `attendees` int unsigned NOT NULL DEFAULT '1',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','confirmed','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hall_bookings_booking_number_unique` (`booking_number`),
  KEY `hall_bookings_guest_id_foreign` (`guest_id`),
  KEY `hall_bookings_created_by_foreign` (`created_by`),
  KEY `hall_bookings_updated_by_foreign` (`updated_by`),
  KEY `hall_bookings_event_date_status_index` (`event_date`,`status`),
  KEY `hall_bookings_hall_id_event_date_index` (`hall_id`,`event_date`),
  CONSTRAINT `hall_bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `hall_bookings_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hall_bookings_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `hall_bookings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `halls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `halls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `capacity` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `availability_rules` json DEFAULT NULL,
  `booking_blackouts` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `halls_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hotel_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `is_free` tinyint(1) NOT NULL DEFAULT '0',
  `pricing_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_service',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `available_online` tinyint(1) NOT NULL DEFAULT '1',
  `requires_advance_booking` tinyint(1) NOT NULL DEFAULT '0',
  `advance_hours` int DEFAULT NULL,
  `availability_schedule` json DEFAULT NULL,
  `max_quantity` int DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_services_hotel_id_foreign` (`hotel_id`),
  CONSTRAINT `hotel_services_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NG',
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Africa/Lagos',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hotels_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `housekeeping_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `housekeeping_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `housekeeping_task_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `housekeeping_notifications_reservation_id_foreign` (`reservation_id`),
  KEY `housekeeping_notifications_housekeeping_task_id_foreign` (`housekeeping_task_id`),
  KEY `housekeeping_notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `housekeeping_notifications_room_id_is_read_index` (`room_id`,`is_read`),
  KEY `housekeeping_notifications_created_at_index` (`created_at`),
  CONSTRAINT `housekeeping_notifications_housekeeping_task_id_foreign` FOREIGN KEY (`housekeeping_task_id`) REFERENCES `housekeeping_tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housekeeping_notifications_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housekeeping_notifications_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `housekeeping_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `housekeeping_schedule_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `housekeeping_schedule_rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `housekeeping_schedule_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `task_type` enum('checkout','cleaning','check_cleaning','stayover','deep_clean','inspection') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cleaning',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','skipped') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_room_unique` (`housekeeping_schedule_id`,`room_id`),
  KEY `housekeeping_schedule_rooms_room_id_foreign` (`room_id`),
  CONSTRAINT `housekeeping_schedule_rooms_housekeeping_schedule_id_foreign` FOREIGN KEY (`housekeeping_schedule_id`) REFERENCES `housekeeping_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `housekeeping_schedule_rooms_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `housekeeping_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `housekeeping_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `room_numbers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `preferred_start_time` time DEFAULT NULL,
  `preferred_end_time` time DEFAULT NULL,
  `status` enum('pending','active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `housekeeping_schedules_created_by_foreign` (`created_by`),
  KEY `housekeeping_schedules_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `housekeeping_schedules_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `housekeeping_schedules_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housekeeping_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `housekeeping_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `housekeeping_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `task_type` enum('checkout','stayover','deep_clean','inspection','maintenance','cleaning','check_cleaning') COLLATE utf8mb4_unicode_ci DEFAULT 'checkout',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` enum('pending','in_progress','completed','skipped') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `validation_status` enum('validated','rejected') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_timestamp` timestamp NULL DEFAULT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `completion_notes` text COLLATE utf8mb4_unicode_ci,
  `inspected_by` bigint unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  `inspection_status` enum('passed','failed','pending') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspection_notes` text COLLATE utf8mb4_unicode_ci,
  `estimated_minutes` int DEFAULT NULL,
  `actual_minutes` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `housekeeping_tasks_inspected_by_foreign` (`inspected_by`),
  KEY `housekeeping_tasks_scheduled_date_status_index` (`scheduled_date`,`status`),
  KEY `housekeeping_tasks_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `housekeeping_tasks_room_id_index` (`room_id`),
  CONSTRAINT `housekeeping_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housekeeping_tasks_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housekeeping_tasks_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `work_order` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `requested_by` bigint unsigned DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_requests_requested_by_foreign` (`requested_by`),
  CONSTRAINT `inventory_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `iptv_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iptv_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mac_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `android_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('online','offline','maintenance','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `last_activity` timestamp NULL DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  `last_heartbeat` timestamp NULL DEFAULT NULL,
  `device_info` json DEFAULT NULL,
  `current_settings` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iptv_devices_device_id_unique` (`device_id`),
  UNIQUE KEY `iptv_devices_mac_address_unique` (`mac_address`),
  KEY `iptv_devices_status_is_active_index` (`status`,`is_active`),
  KEY `iptv_devices_room_id_index` (`room_id`),
  KEY `iptv_devices_last_activity_index` (`last_activity`),
  CONSTRAINT `iptv_devices_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `iptv_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iptv_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `monthly_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `includes_adult_content` tinyint(1) NOT NULL DEFAULT '0',
  `includes_premium_channels` tinyint(1) NOT NULL DEFAULT '0',
  `includes_international_channels` tinyint(1) NOT NULL DEFAULT '0',
  `xtream_categories` json DEFAULT NULL,
  `xtream_channel_groups` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iptv_packages_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `iptv_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iptv_usage_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `iptv_device_id` bigint unsigned NOT NULL,
  `xtream_channel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `xtream_stream_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_data` json DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `guest_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iptv_usage_logs_iptv_device_id_foreign` (`iptv_device_id`),
  KEY `iptv_usage_logs_room_id_started_at_index` (`room_id`,`started_at`),
  KEY `iptv_usage_logs_reservation_id_started_at_index` (`reservation_id`,`started_at`),
  KEY `iptv_usage_logs_action_index` (`action`),
  KEY `iptv_usage_logs_xtream_channel_id_index` (`xtream_channel_id`),
  CONSTRAINT `iptv_usage_logs_iptv_device_id_foreign` FOREIGN KEY (`iptv_device_id`) REFERENCES `iptv_devices` (`id`),
  CONSTRAINT `iptv_usage_logs_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `iptv_usage_logs_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `key_card_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `key_card_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_card_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `returned_to` bigint unsigned DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `key_card_assignments_reservation_id_foreign` (`reservation_id`),
  KEY `key_card_assignments_returned_to_foreign` (`returned_to`),
  KEY `key_card_assignments_key_card_id_assigned_at_index` (`key_card_id`,`assigned_at`),
  KEY `key_card_assignments_guest_id_assigned_at_index` (`guest_id`,`assigned_at`),
  KEY `key_card_assignments_room_id_assigned_at_index` (`room_id`,`assigned_at`),
  KEY `key_card_assignments_assigned_by_assigned_at_index` (`assigned_by`,`assigned_at`),
  KEY `key_card_assignments_action_index` (`action`),
  CONSTRAINT `key_card_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_card_assignments_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_card_assignments_key_card_id_foreign` FOREIGN KEY (`key_card_id`) REFERENCES `key_cards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `key_card_assignments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_card_assignments_returned_to_foreign` FOREIGN KEY (`returned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_card_assignments_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `key_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `key_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `card_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `reservation_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `status` enum('available','assigned','lost','damaged','deactivated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `issued_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `issued_by` bigint unsigned DEFAULT NULL,
  `returned_to` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_cards_card_number_unique` (`card_number`),
  KEY `key_cards_guest_id_foreign` (`guest_id`),
  KEY `key_cards_issued_by_foreign` (`issued_by`),
  KEY `key_cards_returned_to_foreign` (`returned_to`),
  KEY `key_cards_status_is_active_index` (`status`,`is_active`),
  KEY `key_cards_reservation_id_status_index` (`reservation_id`,`status`),
  KEY `key_cards_room_id_index` (`room_id`),
  CONSTRAINT `key_cards_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_cards_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_cards_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_cards_returned_to_foreign` FOREIGN KEY (`returned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `key_cards_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `laundry_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laundry_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `laundry_order_id` bigint unsigned NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` enum('wash','dry_clean','iron','wash_iron','dry_clean_iron') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wash',
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laundry_items_laundry_order_id_foreign` (`laundry_order_id`),
  CONSTRAINT `laundry_items_laundry_order_id_foreign` FOREIGN KEY (`laundry_order_id`) REFERENCES `laundry_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `laundry_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laundry_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','picked_up','in_progress','ready','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('normal','express','overnight') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `pickup_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `express_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('unpaid','paid','billed_to_room') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `laundry_orders_order_number_unique` (`order_number`),
  KEY `laundry_orders_guest_id_foreign` (`guest_id`),
  KEY `laundry_orders_room_id_foreign` (`room_id`),
  KEY `laundry_orders_user_id_foreign` (`user_id`),
  CONSTRAINT `laundry_orders_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `laundry_orders_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `laundry_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `year` year NOT NULL,
  `vacation_days_allocated` decimal(4,1) NOT NULL DEFAULT '0.0',
  `vacation_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `vacation_days_remaining` decimal(4,1) NOT NULL DEFAULT '0.0',
  `sick_days_allocated` decimal(4,1) NOT NULL DEFAULT '0.0',
  `sick_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `sick_days_remaining` decimal(4,1) NOT NULL DEFAULT '0.0',
  `personal_days_allocated` decimal(4,1) NOT NULL DEFAULT '0.0',
  `personal_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `personal_days_remaining` decimal(4,1) NOT NULL DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_balances_user_id_year_unique` (`user_id`,`year`),
  CONSTRAINT `leave_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `leave_type` enum('vacation','sick','personal','emergency','bereavement','maternity','paternity','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` decimal(4,1) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `vacation_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `sick_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `personal_days_used` decimal(4,1) NOT NULL DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  KEY `leave_requests_user_id_status_index` (`user_id`,`status`),
  KEY `leave_requests_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'iptv',
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_devices` int NOT NULL DEFAULT '1',
  `max_rooms` int NOT NULL DEFAULT '10',
  `max_channels` int NOT NULL DEFAULT '50',
  `vod_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `premium_features` tinyint(1) NOT NULL DEFAULT '0',
  `allowed_features` json DEFAULT NULL,
  `issued_at` timestamp NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `last_validated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `hardware_fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_count` int NOT NULL DEFAULT '0',
  `max_activations` int NOT NULL DEFAULT '1',
  `device_info` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `licenses_license_key_unique` (`license_key`),
  KEY `licenses_license_key_status_index` (`license_key`,`status`),
  KEY `licenses_customer_email_status_index` (`customer_email`,`status`),
  KEY `licenses_expires_at_status_index` (`expires_at`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('warehouse','restaurant','frontdesk','bar','kitchen','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `locations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loyalty_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_memberships` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_points` int unsigned NOT NULL DEFAULT '0',
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenance_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748b',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `maintenance_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenance_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `reported_by` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` enum('open','assigned','in_progress','on_hold','resolved','closed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_details` text COLLATE utf8mb4_unicode_ci,
  `photos` json DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `work_performed` text COLLATE utf8mb4_unicode_ci,
  `cost` decimal(10,2) DEFAULT NULL,
  `resolved_by` bigint unsigned DEFAULT NULL,
  `requires_follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `follow_up_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `maintenance_requests_request_number_unique` (`request_number`),
  KEY `maintenance_requests_reported_by_foreign` (`reported_by`),
  KEY `maintenance_requests_department_id_foreign` (`department_id`),
  KEY `maintenance_requests_resolved_by_foreign` (`resolved_by`),
  KEY `maintenance_requests_status_priority_index` (`status`,`priority`),
  KEY `maintenance_requests_room_id_status_index` (`room_id`,`status`),
  KEY `maintenance_requests_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `maintenance_requests_reported_at_index` (`reported_at`),
  CONSTRAINT `maintenance_requests_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_requests_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `maintenance_requests_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_requests_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_hall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_hall` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint unsigned NOT NULL,
  `hall_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_hall_package_id_foreign` (`package_id`),
  KEY `package_hall_hall_id_foreign` (`hall_id`),
  CONSTRAINT `package_hall_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_hall_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `included_features` json DEFAULT NULL,
  `optional_features` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `max_bookings` int DEFAULT NULL,
  `min_guests` int DEFAULT NULL,
  `max_guests` int DEFAULT NULL,
  `duration_hours` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `availability_rules` json DEFAULT NULL,
  `booking_blackouts` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `packages_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_folio_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check','mobile_payment','crypto','comp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `exchange_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `local_amount` decimal(12,2) NOT NULL,
  `card_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last_four` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorization_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processor_response` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded','voided') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint unsigned NOT NULL,
  `refunded_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refunded_by` bigint unsigned DEFAULT NULL,
  `refund_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  KEY `payments_guest_folio_id_foreign` (`guest_folio_id`),
  KEY `payments_reservation_id_foreign` (`reservation_id`),
  KEY `payments_processed_by_foreign` (`processed_by`),
  KEY `payments_refunded_by_foreign` (`refunded_by`),
  KEY `payments_status_processed_at_index` (`status`,`processed_at`),
  KEY `payments_payment_method_processed_at_index` (`payment_method`,`processed_at`),
  CONSTRAINT `payments_guest_folio_id_foreign` FOREIGN KEY (`guest_folio_id`) REFERENCES `guest_folios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_refunded_by_foreign` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payroll_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `frequency` enum('weekly','bi_weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bi_weekly',
  `status` enum('draft','calculated','approved','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_gross_pay` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_net_pay` decimal(15,2) NOT NULL DEFAULT '0.00',
  `calculated_by` bigint unsigned DEFAULT NULL,
  `calculated_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_periods_calculated_by_foreign` (`calculated_by`),
  KEY `payroll_periods_approved_by_foreign` (`approved_by`),
  KEY `payroll_periods_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `payroll_periods_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payroll_periods_calculated_by_foreign` FOREIGN KEY (`calculated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `performance_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `reviewer_id` bigint unsigned DEFAULT NULL,
  `scheduled_for` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_reviews_user_id_foreign` (`user_id`),
  KEY `performance_reviews_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `performance_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `performance_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `can_book_halls` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_halls` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_packages` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_group_bookings` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pos_expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pos_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer','mobile','room_charge') COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_date` date NOT NULL,
  `receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_expenses_expense_number_unique` (`expense_number`),
  KEY `pos_expenses_category_id_foreign` (`category_id`),
  KEY `pos_expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `pos_expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `pos_expense_categories` (`id`),
  CONSTRAINT `pos_expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pos_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cash_drawer_session_id` bigint unsigned NOT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `type` enum('sale','refund','cash_in','cash_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer','mobile','room_charge') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_transactions_sale_id_foreign` (`sale_id`),
  KEY `pos_transactions_cash_drawer_session_id_created_at_index` (`cash_drawer_session_id`,`created_at`),
  CONSTRAINT `pos_transactions_cash_drawer_session_id_foreign` FOREIGN KEY (`cash_drawer_session_id`) REFERENCES `cash_drawer_sessions` (`id`),
  CONSTRAINT `pos_transactions_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `department_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `positions_department_id_foreign` (`department_id`),
  CONSTRAINT `positions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_warehouse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_warehouse` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_stock_level` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_warehouse_product_id_warehouse_id_unique` (`product_id`,`warehouse_id`),
  KEY `product_warehouse_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `product_warehouse_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_warehouse_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `margin_percentage` decimal(8,4) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `min_stock_level` int NOT NULL DEFAULT '5',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_service` tinyint(1) NOT NULL DEFAULT '0',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_unit_id_foreign` (`unit_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_documents_user_id_foreign` (`user_id`),
  KEY `purchase_documents_purchase_order_id_document_type_index` (`purchase_order_id`,`document_type`),
  CONSTRAINT `purchase_documents_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity_ordered` int NOT NULL,
  `quantity_received` int NOT NULL DEFAULT '0',
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `purchase_type` enum('resale','expense') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'resale',
  `expense_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('pending','approved','received','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remaining_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `delivery_time_days` int DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `purchase_conditions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_orders_user_id_foreign` (`user_id`),
  KEY `purchase_orders_location_id_foreign` (`location_id`),
  CONSTRAINT `purchase_orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quote_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quote_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_items_quote_id_index` (`quote_id`),
  CONSTRAINT `quote_items_quote_id_foreign` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quote_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quote_type` enum('guest','outsider') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `reservation_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valid_until` date DEFAULT NULL,
  `status` enum('draft','sent','accepted','rejected','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotes_quote_number_unique` (`quote_number`),
  KEY `quotes_reservation_id_foreign` (`reservation_id`),
  KEY `quotes_customer_id_foreign` (`customer_id`),
  KEY `quotes_created_by_foreign` (`created_by`),
  KEY `quotes_status_index` (`status`),
  KEY `quotes_quote_type_index` (`quote_type`),
  KEY `quotes_created_at_index` (`created_at`),
  KEY `quotes_valid_until_index` (`valid_until`),
  CONSTRAINT `quotes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotes_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotes_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservation_companions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_companions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relationship` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_companions_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `reservation_companions_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservation_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_room` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `adults` int NOT NULL DEFAULT '1',
  `children` int NOT NULL DEFAULT '0',
  `room_rate` decimal(10,2) DEFAULT NULL,
  `total_room_charges` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_room_reservation_id_room_id_unique` (`reservation_id`,`room_id`),
  KEY `reservation_room_room_id_foreign` (`room_id`),
  CONSTRAINT `reservation_room_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_room_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservation_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `hotel_service_id` bigint unsigned DEFAULT NULL,
  `breakfast_menu_id` bigint unsigned DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `service_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_services_reservation_id_foreign` (`reservation_id`),
  KEY `reservation_services_hotel_service_id_foreign` (`hotel_service_id`),
  KEY `reservation_services_breakfast_menu_id_foreign` (`breakfast_menu_id`),
  CONSTRAINT `reservation_services_breakfast_menu_id_foreign` FOREIGN KEY (`breakfast_menu_id`) REFERENCES `breakfast_menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_services_hotel_service_id_foreign` FOREIGN KEY (`hotel_service_id`) REFERENCES `hotel_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_services_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `reservation_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmation_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `group_booking_id` bigint unsigned DEFAULT NULL,
  `is_group_booking` tinyint(1) NOT NULL DEFAULT '0',
  `billing_type` enum('individual','group_consolidated','group_split') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `room_id` bigint unsigned DEFAULT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `nights` int NOT NULL,
  `number_of_adults` int NOT NULL DEFAULT '1',
  `number_of_children` int NOT NULL DEFAULT '0',
  `adults` int NOT NULL,
  `children` int NOT NULL DEFAULT '0',
  `infants` int NOT NULL DEFAULT '0',
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled','no_show','modified') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','partial','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `room_rate` decimal(10,2) NOT NULL,
  `total_room_charges` decimal(12,2) NOT NULL,
  `taxes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `service_charges` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `actual_check_in` timestamp NULL DEFAULT NULL,
  `actual_check_out` timestamp NULL DEFAULT NULL,
  `checked_in_by` bigint unsigned DEFAULT NULL,
  `checked_out_by` bigint unsigned DEFAULT NULL,
  `booking_source` enum('walk_in','phone','email','website','booking_com','expedia','agoda','travel_agent','corporate') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'walk_in',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ota_confirmation_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `guest_preferences` json DEFAULT NULL,
  `room_preferences` json DEFAULT NULL,
  `early_check_in_requested` tinyint(1) NOT NULL DEFAULT '0',
  `late_check_out_requested` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_check_in_time` time DEFAULT NULL,
  `preferred_check_out_time` time DEFAULT NULL,
  `iptv_preferences` json DEFAULT NULL,
  `iptv_adult_content` tinyint(1) NOT NULL DEFAULT '0',
  `iptv_language_preference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `airport_pickup` tinyint(1) NOT NULL DEFAULT '0',
  `airport_drop` tinyint(1) NOT NULL DEFAULT '0',
  `breakfast_included` tinyint(1) NOT NULL DEFAULT '0',
  `wifi_included` tinyint(1) NOT NULL DEFAULT '1',
  `parking_required` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_charges` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `hall_id` bigint unsigned DEFAULT NULL,
  `hall_start_time` datetime DEFAULT NULL,
  `hall_end_time` datetime DEFAULT NULL,
  `hall_booking_details` json DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `package_booking_details` json DEFAULT NULL,
  `group_booking_details` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_reservation_number_unique` (`reservation_number`),
  KEY `reservations_room_type_id_foreign` (`room_type_id`),
  KEY `reservations_checked_in_by_foreign` (`checked_in_by`),
  KEY `reservations_checked_out_by_foreign` (`checked_out_by`),
  KEY `reservations_cancelled_by_foreign` (`cancelled_by`),
  KEY `reservations_created_by_foreign` (`created_by`),
  KEY `reservations_updated_by_foreign` (`updated_by`),
  KEY `reservations_check_in_date_check_out_date_index` (`check_in_date`,`check_out_date`),
  KEY `reservations_status_check_in_date_index` (`status`,`check_in_date`),
  KEY `reservations_guest_id_index` (`guest_id`),
  KEY `reservations_room_id_index` (`room_id`),
  KEY `reservations_booking_source_index` (`booking_source`),
  KEY `reservations_group_booking_id_foreign` (`group_booking_id`),
  KEY `reservations_hotel_id_foreign` (`hotel_id`),
  KEY `reservations_hall_id_foreign` (`hall_id`),
  KEY `reservations_package_id_foreign` (`package_id`),
  KEY `reservations_confirmation_token_index` (`confirmation_token`),
  CONSTRAINT `reservations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reservations_checked_in_by_foreign` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reservations_checked_out_by_foreign` FOREIGN KEY (`checked_out_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reservations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reservations_group_booking_id_foreign` FOREIGN KEY (`group_booking_id`) REFERENCES `group_bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservations_hall_id_foreign` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservations_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `reservations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `position_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_position_id_foreign` (`position_id`),
  CONSTRAINT `roles_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_amenities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `amenity_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amenity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `condition` enum('excellent','good','fair','poor','broken') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'good',
  `last_checked` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_amenities_room_id_foreign` (`room_id`),
  CONSTRAINT `room_amenities_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_iptv_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_iptv_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `iptv_package_id` bigint unsigned NOT NULL,
  `xtream_custom_categories` json DEFAULT NULL,
  `xtream_blocked_categories` json DEFAULT NULL,
  `xtream_blocked_channels` json DEFAULT NULL,
  `adult_content_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `parental_control_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume_limit` int NOT NULL DEFAULT '100',
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `language_preferences` json DEFAULT NULL,
  `auto_power_off` tinyint(1) NOT NULL DEFAULT '0',
  `auto_power_off_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_iptv_settings_room_id_unique` (`room_id`),
  KEY `room_iptv_settings_iptv_package_id_foreign` (`iptv_package_id`),
  CONSTRAINT `room_iptv_settings_iptv_package_id_foreign` FOREIGN KEY (`iptv_package_id`) REFERENCES `iptv_packages` (`id`),
  CONSTRAINT `room_iptv_settings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_type_amenity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_type_amenity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_type_id` bigint unsigned NOT NULL,
  `room_amenity_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_type_amenity_room_type_id_foreign` (`room_type_id`),
  KEY `room_type_amenity_room_amenity_id_foreign` (`room_amenity_id`),
  CONSTRAINT `room_type_amenity_room_amenity_id_foreign` FOREIGN KEY (`room_amenity_id`) REFERENCES `room_amenities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_type_amenity_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_occupancy` int NOT NULL,
  `max_adults` int NOT NULL,
  `max_children` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `extra_adult_charge` decimal(8,2) NOT NULL DEFAULT '0.00',
  `extra_child_charge` decimal(8,2) NOT NULL DEFAULT '0.00',
  `amenities` json DEFAULT NULL,
  `iptv_channels` json DEFAULT NULL,
  `iptv_package` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_size_sqft` decimal(8,2) DEFAULT NULL,
  `bed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bed_type_id` bigint unsigned DEFAULT NULL,
  `bed_count` int NOT NULL DEFAULT '1',
  `has_balcony` tinyint(1) NOT NULL DEFAULT '0',
  `has_kitchen` tinyint(1) NOT NULL DEFAULT '0',
  `has_living_room` tinyint(1) NOT NULL DEFAULT '0',
  `view_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_types_code_unique` (`code`),
  KEY `room_types_bed_type_id_foreign` (`bed_type_id`),
  CONSTRAINT `room_types_bed_type_id_foreign` FOREIGN KEY (`bed_type_id`) REFERENCES `bed_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `room_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `floor_id` bigint unsigned DEFAULT NULL,
  `building_wing_id` bigint unsigned DEFAULT NULL,
  `status` enum('available','occupied','maintenance','cleaning','out_of_order','reserved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `iptv_device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iptv_mac_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iptv_ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iptv_active` tinyint(1) NOT NULL DEFAULT '1',
  `iptv_settings` json DEFAULT NULL,
  `iptv_last_seen` timestamp NULL DEFAULT NULL,
  `is_smoking` tinyint(1) NOT NULL DEFAULT '0',
  `is_accessible` tinyint(1) NOT NULL DEFAULT '0',
  `has_connecting_room` tinyint(1) NOT NULL DEFAULT '0',
  `connecting_room_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `maintenance_notes` text COLLATE utf8mb4_unicode_ci,
  `housekeeping_status` enum('clean','dirty','inspected','maintenance_required','waiting_for_check') COLLATE utf8mb4_unicode_ci DEFAULT 'clean',
  `features` json DEFAULT NULL,
  `special_features` text COLLATE utf8mb4_unicode_ci,
  `last_cleaned_at` timestamp NULL DEFAULT NULL,
  `last_cleaned_by` bigint unsigned DEFAULT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `custom_price_start` date DEFAULT NULL,
  `custom_price_end` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_room_number_unique` (`room_number`),
  KEY `rooms_last_cleaned_by_foreign` (`last_cleaned_by`),
  KEY `rooms_status_is_active_index` (`status`,`is_active`),
  KEY `rooms_room_type_id_index` (`room_type_id`),
  KEY `rooms_iptv_device_id_index` (`iptv_device_id`),
  KEY `rooms_floor_id_foreign` (`floor_id`),
  KEY `rooms_building_wing_id_foreign` (`building_wing_id`),
  KEY `rooms_hotel_id_foreign` (`hotel_id`),
  CONSTRAINT `rooms_building_wing_id_foreign` FOREIGN KEY (`building_wing_id`) REFERENCES `building_wings` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `rooms_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `rooms_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rooms_last_cleaned_by_foreign` FOREIGN KEY (`last_cleaned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `is_charged_to_room` tinyint(1) NOT NULL DEFAULT '0',
  `is_walk_in` tinyint(1) NOT NULL DEFAULT '0',
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer','mobile','room_charge') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `sale_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_sale_number_unique` (`sale_number`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_room_id_foreign` (`room_id`),
  KEY `sales_reservation_id_foreign` (`reservation_id`),
  KEY `sales_guest_id_foreign` (`guest_id`),
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hall_booking_settings` json DEFAULT NULL,
  `package_booking_settings` json DEFAULT NULL,
  `group_booking_settings` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `adjustment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_before` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity_after` decimal(10,2) NOT NULL DEFAULT '0.00',
  `adjustment_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_adjustments_product_id_foreign` (`product_id`),
  KEY `stock_adjustments_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_adjustments_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_adjustments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `received_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_batches_batch_number_unique` (`batch_number`),
  KEY `stock_batches_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `stock_batches_user_id_foreign` (`user_id`),
  KEY `stock_batches_product_id_received_date_index` (`product_id`,`received_date`),
  KEY `stock_batches_batch_number_index` (`batch_number`),
  KEY `stock_batches_location_id_foreign` (`location_id`),
  CONSTRAINT `stock_batches_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_batches_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `type` enum('in','out','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `previous_stock` int NOT NULL,
  `new_stock` int NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_user_id_foreign` (`user_id`),
  KEY `stock_movements_product_id_created_at_index` (`product_id`,`created_at`),
  KEY `stock_movements_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `stock_movements_location_id_foreign` (`location_id`),
  CONSTRAINT `stock_movements_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `from_warehouse_id` bigint unsigned NOT NULL,
  `to_warehouse_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `destination_location_id` bigint unsigned DEFAULT NULL,
  `from_location_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transfers_product_id_foreign` (`product_id`),
  KEY `stock_transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  KEY `stock_transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  KEY `stock_transfers_user_id_foreign` (`user_id`),
  KEY `stock_transfers_approved_by_foreign` (`approved_by`),
  KEY `stock_transfers_destination_location_id_foreign` (`destination_location_id`),
  KEY `stock_transfers_from_location_id_foreign` (`from_location_id`),
  CONSTRAINT `stock_transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_destination_location_id_foreign` FOREIGN KEY (`destination_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_from_location_id_foreign` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `payment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_type` enum('partial','full') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','credit_card') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_date` date NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_payments_payment_number_unique` (`payment_number`),
  KEY `supplier_payments_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `supplier_payments_user_id_foreign` (`user_id`),
  KEY `supplier_payments_supplier_id_payment_date_index` (`supplier_id`,`payment_date`),
  KEY `supplier_payments_payment_number_index` (`payment_number`),
  CONSTRAINT `supplier_payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `credit_limit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `due_date` datetime DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_created_by_foreign` (`created_by`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_invitations_team_id_email_unique` (`team_id`,`email`),
  CONSTRAINT `team_invitations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_team` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `time_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `work_shift_id` bigint unsigned DEFAULT NULL,
  `work_date` date NOT NULL,
  `clock_in_time` timestamp NULL DEFAULT NULL,
  `clock_out_time` timestamp NULL DEFAULT NULL,
  `break_start_time` timestamp NULL DEFAULT NULL,
  `break_end_time` timestamp NULL DEFAULT NULL,
  `regular_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `break_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','completed','incomplete','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_late` tinyint(1) NOT NULL DEFAULT '0',
  `is_early_out` tinyint(1) NOT NULL DEFAULT '0',
  `late_minutes` int NOT NULL DEFAULT '0',
  `early_out_minutes` int NOT NULL DEFAULT '0',
  `clock_in_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_out_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_in_latitude` decimal(10,8) DEFAULT NULL,
  `clock_in_longitude` decimal(11,8) DEFAULT NULL,
  `clock_out_latitude` decimal(10,8) DEFAULT NULL,
  `clock_out_longitude` decimal(11,8) DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `time_entries_user_id_work_date_unique` (`user_id`,`work_date`),
  KEY `time_entries_work_shift_id_foreign` (`work_shift_id`),
  KEY `time_entries_approved_by_foreign` (`approved_by`),
  KEY `time_entries_user_id_work_date_index` (`user_id`,`work_date`),
  KEY `time_entries_work_date_status_index` (`work_date`,`status`),
  CONSTRAINT `time_entries_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `time_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `time_entries_work_shift_id_foreign` FOREIGN KEY (`work_shift_id`) REFERENCES `work_shifts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_programs_created_by_foreign` (`created_by`),
  CONSTRAINT `training_programs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_name_unique` (`name`),
  UNIQUE KEY `units_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT '1',
  `granted_by` bigint unsigned DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permissions_user_id_permission_id_unique` (`user_id`,`permission_id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`),
  KEY `user_permissions_granted_by_foreign` (`granted_by`),
  CONSTRAINT `user_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  KEY `user_roles_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `user_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USA',
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `employment_status` enum('active','inactive','terminated','on_leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_id` bigint unsigned DEFAULT NULL,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `pay_type` enum('hourly','salary') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hourly',
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `work_permit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_permit_expiry` date DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_custom_accountant` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_employee_id_unique` (`employee_id`),
  KEY `users_employment_status_is_active_index` (`employment_status`,`is_active`),
  KEY `users_department_index` (`department`),
  KEY `users_hire_date_index` (`hire_date`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_position_id_foreign` (`position_id`),
  KEY `users_location_id_foreign` (`location_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vod_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vod_content` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `genre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'English',
  `release_year` year DEFAULT NULL,
  `rating` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `poster_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stream_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rental_price` decimal(6,2) NOT NULL DEFAULT '0.00',
  `is_free` tinyint(1) NOT NULL DEFAULT '0',
  `is_adult_content` tinyint(1) NOT NULL DEFAULT '0',
  `requires_subscription` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int NOT NULL DEFAULT '0',
  `rating_score` decimal(3,1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vod_content_genre_is_active_index` (`genre`,`is_active`),
  KEY `vod_content_language_is_active_index` (`language`,`is_active`),
  KEY `vod_content_rating_index` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vod_viewing_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vod_viewing_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `vod_content_id` bigint unsigned NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `watch_duration_seconds` int NOT NULL DEFAULT '0',
  `total_duration_seconds` int NOT NULL,
  `completion_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `rental_charge` decimal(6,2) NOT NULL DEFAULT '0.00',
  `was_completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vod_viewing_history_vod_content_id_foreign` (`vod_content_id`),
  KEY `vod_viewing_history_room_id_started_at_index` (`room_id`,`started_at`),
  KEY `vod_viewing_history_reservation_id_started_at_index` (`reservation_id`,`started_at`),
  CONSTRAINT `vod_viewing_history_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `vod_viewing_history_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `vod_viewing_history_vod_content_id_foreign` FOREIGN KEY (`vod_content_id`) REFERENCES `vod_content` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `waitlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `waitlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guest_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `requested_check_in` date NOT NULL,
  `requested_check_out` date NOT NULL,
  `requested_nights` int NOT NULL,
  `number_of_adults` int NOT NULL,
  `number_of_children` int NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  `status` enum('active','notified','converted','cancelled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `notified_at` timestamp NULL DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `converted_to_reservation_id` bigint unsigned DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waitlists_guest_id_foreign` (`guest_id`),
  KEY `waitlists_converted_to_reservation_id_foreign` (`converted_to_reservation_id`),
  KEY `waitlists_requested_check_in_requested_check_out_index` (`requested_check_in`,`requested_check_out`),
  KEY `waitlists_status_priority_index` (`status`,`priority`),
  KEY `waitlists_room_type_id_index` (`room_type_id`),
  CONSTRAINT `waitlists_converted_to_reservation_id_foreign` FOREIGN KEY (`converted_to_reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `waitlists_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `waitlists_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_overnight` tinyint(1) NOT NULL DEFAULT '0',
  `hours` decimal(4,2) NOT NULL,
  `break_minutes` decimal(5,2) NOT NULL DEFAULT '0.00',
  `overtime_threshold` decimal(4,2) DEFAULT NULL,
  `overtime_multiplier` decimal(3,2) NOT NULL DEFAULT '1.50',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_01_01_000001_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_01_000002_create_guests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_01_000003_create_roles_and_permissions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_01_000004_create_rooms_and_room_types_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_01_000005_create_reservations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_01_01_000006_create_time_tracking_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_01_01_000007_create_iptv_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_01_01_000008_create_financial_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_01_19_000000_create_pos_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_06_20_102113_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_06_20_102159_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_06_20_102200_create_teams_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_06_20_102201_create_team_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_06_20_102202_create_team_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_06_20_114855_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_06_20_152229_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_06_20_154555_create_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_01_16_184053_create_hotel_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_01_16_184208_create_breakfast_menus_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_01_16_184304_create_reservation_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_01_16_190931_make_guest_fields_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_01_16_202753_make_guest_id_fields_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_01_16_222127_create_room_amenities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_01_16_222645_add_features_to_rooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_01_19_000002_fix_room_amenities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_01_19_090455_add_emoji_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_01_19_120000_create_pos_extended_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_01_19_130000_add_license_data_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_01_19_172526_add_guard_name_to_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_01_19_180205_fix_role_permissions_table_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_01_19_193827_add_guard_name_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_01_19_232211_add_timestamps_to_user_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_01_20_165443_fix_permission_tables_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_01_20_171004_make_room_id_nullable_in_room_amenities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_01_20_184919_create_departments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_01_20_185005_create_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_20_185650_add_position_id_to_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_01_20_190541_add_missing_columns_to_departments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_01_20_195755_add_missing_columns_to_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_01_20_210305_add_department_and_position_foreign_keys_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_01_21_164523_create_customer_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_01_21_164524_create_customers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_01_21_164612_add_customer_id_to_sales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_01_22_000000_create_supplier_payments_and_stock_batches',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_01_22_055500_add_color_to_expense_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_01_22_094220_add_unit_cost_to_sale_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_01_22_113035_create_floors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_01_22_113044_create_building_wings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_01_22_113051_create_bed_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_01_22_113142_update_rooms_table_add_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_01_22_121045_add_bed_type_id_to_room_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_01_22_131155_create_guest_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_01_22_131239_add_guest_type_id_to_guests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_01_22_152854_add_group_booking_fields_to_reservations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_01_22_153012_create_group_bookings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_01_22_153019_create_waitlists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_01_22_153026_create_housekeeping_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_01_22_153033_create_maintenance_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_01_22_160000_add_group_booking_foreign_key_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_01_22_213615_create_key_cards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_01_22_214618_add_is_free_to_hotel_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_01_23_000001_create_housekeeping_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_01_23_000002_create_housekeeping_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_01_23_203943_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_01_23_210000_add_room_fields_to_sales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_01_26_110048_add_cleaning_to_housekeeping_tasks_task_type_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_01_26_120436_add_waiting_for_check_to_rooms_housekeeping_status_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_01_26_121114_add_check_cleaning_to_housekeeping_tasks_task_type_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_01_27_000010_update_pos_payment_methods_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_01_27_110000_create_hotels_and_assign_to_core_entities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_01_27_110100_create_reservation_room_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_01_27_110200_create_discounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_01_27_110300_create_employee_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_01_27_110400_add_soft_deletes_to_core_entities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_01_30_000001_create_concierge_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_01_30_000002_create_inventory_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_01_30_120000_create_performance_reviews_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_01_30_130000_add_last_activity_to_iptv_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_02_01_000001_create_budgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_02_03_135155_add_guest_counts_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_02_03_135339_add_adults_column_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_02_08_100000_create_budget_expenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_02_10_100000_add_print_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_02_17_220000_add_guest_preferences_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_02_18_183652_create_key_card_assignments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_02_18_200000_alter_maintenance_category_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_02_18_210000_create_maintenance_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_02_19_100000_create_halls_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_02_19_100001_create_packages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_02_19_100003_add_hall_booking_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_02_19_100004_add_package_booking_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_02_19_100005_add_group_booking_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_02_19_100006_create_package_hall_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_02_19_100007_create_group_booking_hall_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_02_19_100008_create_group_booking_package_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_02_19_100009_add_hall_availability_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_02_19_100010_add_package_availability_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_02_19_100011_add_group_booking_availability_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_02_19_100012_add_hall_booking_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_02_19_100013_add_hall_booking_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_02_19_100014_add_hall_booking_reports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_02_19_100015_add_hall_booking_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_02_19_100016_add_hall_booking_payment_tracking',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_02_19_100017_add_hall_booking_cancellation_tracking',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_02_19_100018_add_hall_booking_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_02_19_100019_add_hall_booking_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_02_19_100020_add_hall_booking_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_02_19_100021_add_hall_booking_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_02_19_100022_add_hall_booking_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_02_19_100023_add_hall_booking_availability_calendar',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_02_19_100024_add_hall_booking_package_pricing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2026_02_19_100025_add_hall_booking_group_pricing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2026_02_19_100026_add_hall_booking_package_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2026_02_19_100027_add_hall_booking_group_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2026_02_19_100028_add_hall_booking_package_availability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2026_02_19_100029_add_hall_booking_group_availability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2026_02_19_100030_add_hall_booking_package_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2026_02_19_100031_add_hall_booking_group_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2026_02_19_100032_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2026_02_19_100033_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2026_02_19_100034_add_hall_booking_package_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2026_02_19_100035_add_hall_booking_group_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2026_02_19_100036_add_hall_booking_package_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2026_02_19_100037_add_hall_booking_group_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2026_02_19_100038_add_hall_booking_package_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2026_02_19_100039_add_hall_booking_group_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2026_02_19_100040_add_hall_booking_package_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2026_02_19_100041_add_hall_booking_group_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2026_02_19_100042_add_hall_booking_package_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2026_02_19_100043_add_hall_booking_group_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2026_02_19_100044_add_hall_booking_package_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2026_02_19_100045_add_hall_booking_group_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2026_02_19_100046_add_hall_booking_package_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2026_02_19_100047_add_hall_booking_group_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2026_02_19_100048_add_hall_booking_package_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2026_02_19_100049_add_hall_booking_group_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2026_02_19_100050_add_hall_booking_package_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2026_02_19_100051_add_hall_booking_group_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2026_02_19_100052_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2026_02_19_100053_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2026_02_19_100054_add_hall_booking_package_availability_calendar',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_02_19_100055_add_hall_booking_group_availability_calendar',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_02_19_100056_add_hall_booking_package_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2026_02_19_100057_add_hall_booking_group_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2026_02_19_100058_add_hall_booking_package_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2026_02_19_100059_add_hall_booking_group_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2026_02_19_100060_add_hall_booking_package_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2026_02_19_100061_add_hall_booking_group_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2026_02_19_100062_add_hall_booking_package_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2026_02_19_100063_add_hall_booking_group_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2026_02_19_100064_add_hall_booking_package_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2026_02_19_100065_add_hall_booking_group_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2026_02_19_100066_add_hall_booking_package_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2026_02_19_100067_add_hall_booking_group_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2026_02_19_100068_add_hall_booking_package_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2026_02_19_100069_add_hall_booking_group_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2026_02_19_100070_add_hall_booking_package_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2026_02_19_100071_add_hall_booking_group_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2026_02_19_100072_add_hall_booking_package_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2026_02_19_100073_add_hall_booking_group_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2026_02_19_100074_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2026_02_19_100075_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2026_02_19_100076_add_hall_booking_package_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2026_02_19_100077_add_hall_booking_group_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2026_02_19_100078_add_hall_booking_package_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2026_02_19_100079_add_hall_booking_group_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2026_02_19_100080_add_hall_booking_package_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2026_02_19_100081_add_hall_booking_group_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2026_02_19_100082_add_hall_booking_package_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2026_02_19_100083_add_hall_booking_group_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2026_02_19_100084_add_hall_booking_package_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2026_02_19_100085_add_hall_booking_group_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2026_02_19_100086_add_hall_booking_package_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2026_02_19_100087_add_hall_booking_group_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2026_02_19_100088_add_hall_booking_package_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2026_02_19_100089_add_hall_booking_group_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2026_02_19_100090_add_hall_booking_package_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2026_02_19_100091_add_hall_booking_group_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2026_02_19_100092_add_hall_booking_package_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2026_02_19_100093_add_hall_booking_group_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2026_02_19_100094_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2026_02_19_100095_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2026_02_19_100096_add_hall_booking_package_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2026_02_19_100097_add_hall_booking_group_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2026_02_19_100098_add_hall_booking_package_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2026_02_19_100099_add_hall_booking_group_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2026_02_19_100100_add_hall_booking_package_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2026_02_19_100101_add_hall_booking_group_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2026_02_19_100102_add_hall_booking_package_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_02_19_100103_add_hall_booking_group_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_02_19_100104_add_hall_booking_package_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_02_19_100105_add_hall_booking_group_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_02_19_100106_add_hall_booking_package_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_02_19_100107_add_hall_booking_group_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_02_19_100108_add_hall_booking_package_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_02_19_100109_add_hall_booking_group_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_02_19_100110_add_hall_booking_package_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_02_19_100111_add_hall_booking_group_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_02_19_100112_add_hall_booking_package_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_02_19_100113_add_hall_booking_group_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_02_19_100114_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_02_19_100115_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_02_19_100116_add_hall_booking_package_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_02_19_100117_add_hall_booking_group_availability_exceptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_02_19_100118_add_hall_booking_package_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_02_19_100119_add_hall_booking_group_availability_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_02_19_100120_add_hall_booking_package_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_02_19_100121_add_hall_booking_group_availability_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2026_02_19_100122_add_hall_booking_package_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2026_02_19_100123_add_hall_booking_group_availability_analytics',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2026_02_19_100124_add_hall_booking_package_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2026_02_19_100125_add_hall_booking_group_availability_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2026_02_19_100126_add_hall_booking_package_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2026_02_19_100127_add_hall_booking_group_availability_preferences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2026_02_19_100128_add_hall_booking_package_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2026_02_19_100129_add_hall_booking_group_availability_discounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2026_02_19_100130_add_hall_booking_package_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2026_02_19_100131_add_hall_booking_group_availability_inclusions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2026_02_19_100132_add_hall_booking_package_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2026_02_19_100133_add_hall_booking_group_availability_reviews',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2026_02_19_100134_add_hall_booking_package_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2026_02_19_100135_add_hall_booking_group_availability_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2026_02_28_110117_create_brands_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2026_02_28_110125_create_warehouses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2026_02_28_110600_create_stock_adjustments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2026_02_28_110619_create_stock_transfers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2026_02_28_110630_create_product_warehouse_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2026_02_28_110700_create_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2026_02_28_112715_add_brand_id_and_unit_id_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2026_02_28_113559_make_unit_field_nullable_in_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2026_02_28_170000_create_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2026_02_28_171000_create_activity_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2026_02_28_200000_create_locations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2026_03_01_100000_create_housekeeping_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2026_03_01_171057_create_attendances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2026_03_01_212200_add_missing_package_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2026_03_02_000000_create_training_programs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2026_03_02_000100_create_leave_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2026_03_02_081500_create_hall_bookings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2026_03_02_175000_create_loyalty_memberships_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2026_03_03_000001_create_laundry_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2026_03_03_031638_add_location_and_type_to_purchase_orders',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2026_03_03_060000_add_margin_and_location_to_pos_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2026_03_03_080000_add_from_location_to_stock_transfers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2026_03_03_100001_add_confirmation_token_to_reservations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2026_03_04_080502_make_guest_id_nullable_in_guest_folios',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2026_03_04_203637_make_guest_folio_id_nullable_in_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2026_03_06_145140_add_customer_fields_to_guest_folios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2026_03_06_183823_make_reservation_id_nullable_in_guest_folios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2026_03_06_200829_make_reservation_id_nullable_in_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2026_03_06_201016_make_reservation_id_nullable_in_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_03_07_000001_create_quotes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_03_07_000002_create_quote_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_03_13_000001_add_payment_status_to_reservations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2026_05_01_000001_create_accountant_report_overrides',999);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2026_02_01_000500_add_validation_fields_to_housekeeping_tasks',1000);
