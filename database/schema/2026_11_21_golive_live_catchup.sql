-- Willis go-live 2026-11-21: bring live (thew_main) up to the staging schema. Additive only.
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `booking_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_date` date NOT NULL,
  `departure_date` date NOT NULL,
  `adults` tinyint unsigned NOT NULL,
  `children` tinyint unsigned NOT NULL DEFAULT '0',
  `rooms` tinyint unsigned NOT NULL DEFAULT '1',
  `room_type_id` bigint unsigned DEFAULT NULL,
  `meal_plan` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BB',
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','converted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `stay_id` bigint unsigned DEFAULT NULL,
  `decline_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handled_by` bigint unsigned DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_requests_room_type_id_foreign` (`room_type_id`),
  KEY `booking_requests_stay_id_foreign` (`stay_id`),
  KEY `booking_requests_handled_by_foreign` (`handled_by`),
  KEY `booking_requests_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `booking_requests_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `booking_requests_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `booking_requests_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spent_on` date NOT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('cash','credit_card','mobile_money','bank_transfer','cheque') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M-Pesa code, cheque or bank ref',
  `supplier_id` bigint unsigned DEFAULT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `status` enum('paid','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `void_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  KEY `expenses_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `expenses_department_id_foreign` (`department_id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_spent_on_status_index` (`spent_on`,`status`),
  KEY `expenses_category_spent_on_index` (`category`,`spent_on`),
  KEY `expenses_supplier_id_status_index` (`supplier_id`,`status`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expenses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expenses_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `facility_booking_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_booking_id` bigint unsigned NOT NULL,
  `facility_package_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `charge_basis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `days` int unsigned NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_booking_items_facility_booking_id_foreign` (`facility_booking_id`),
  KEY `facility_booking_items_facility_package_id_foreign` (`facility_package_id`),
  CONSTRAINT `facility_booking_items_facility_booking_id_foreign` FOREIGN KEY (`facility_booking_id`) REFERENCES `facility_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `facility_booking_items_facility_package_id_foreign` FOREIGN KEY (`facility_package_id`) REFERENCES `facility_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `facility_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `stay_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `pax` int unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tentative',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_bookings_stay_id_foreign` (`stay_id`),
  KEY `facility_bookings_created_by_foreign` (`created_by`),
  KEY `facility_bookings_facility_id_start_date_end_date_index` (`facility_id`,`start_date`,`end_date`),
  KEY `facility_bookings_status_index` (`status`),
  CONSTRAINT `facility_bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `facility_bookings_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `facility_bookings_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `guest_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guest_id` bigint unsigned DEFAULT NULL,
  `stay_id` bigint unsigned DEFAULT NULL,
  `booking_request_id` bigint unsigned DEFAULT NULL,
  `channel` enum('sms','email') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','sent','failed','test') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_by` bigint unsigned DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guest_messages_guest_id_foreign` (`guest_id`),
  KEY `guest_messages_stay_id_foreign` (`stay_id`),
  KEY `guest_messages_booking_request_id_foreign` (`booking_request_id`),
  KEY `guest_messages_sent_by_foreign` (`sent_by`),
  KEY `guest_messages_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `guest_messages_booking_request_id_foreign` FOREIGN KEY (`booking_request_id`) REFERENCES `booking_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guest_messages_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guest_messages_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guest_messages_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` bigint unsigned NOT NULL,
  `type` enum('in','out','adjustment','damage','expired') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL COMMENT 'Signed change: + in, - out',
  `balance_after` decimal(12,2) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `reference_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'purchase_order, task, manual',
  `reference_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_department_id_foreign` (`department_id`),
  KEY `inventory_transactions_created_by_foreign` (`created_by`),
  KEY `inventory_transactions_inventory_item_id_created_at_index` (`inventory_item_id`,`created_at`),
  KEY `inventory_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `inventory_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_transactions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_transactions_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

CREATE TABLE IF NOT EXISTS `room_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_type_id` bigint unsigned NOT NULL,
  `meal_plan` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BB',
  `basis` enum('per_room','per_person') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_room',
  `adults` tinyint unsigned DEFAULT NULL COMMENT 'per_room: rate for exactly this many adults; NULL = any',
  `rate` decimal(12,2) NOT NULL,
  `child_rate` decimal(12,2) DEFAULT NULL COMMENT 'Per child per night; NULL = use the child % setting',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_rates_room_type_id_meal_plan_adults_unique` (`room_type_id`,`meal_plan`,`adults`),
  CONSTRAINT `room_rates_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;

ALTER TABLE `facility_packages` ADD COLUMN `charge_basis` varchar(20) NOT NULL DEFAULT 'per_person' AFTER `price`;
ALTER TABLE `invoices` ADD COLUMN `facility_booking_id` bigint unsigned NULL DEFAULT NULL AFTER `stay_id`;
ALTER TABLE `invoices` ADD COLUMN `kitchen_order_id` bigint unsigned NULL DEFAULT NULL AFTER `facility_booking_id`;
ALTER TABLE `invoices` ADD COLUMN `tax_inclusive` tinyint(1) NOT NULL DEFAULT 1 AFTER `tax_amount`;
ALTER TABLE `invoices` ADD COLUMN `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `tax_inclusive`;
ALTER TABLE `invoices` ADD COLUMN `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `vat_rate`;
ALTER TABLE `invoices` ADD COLUMN `levy_rate` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `vat_amount`;
ALTER TABLE `invoices` ADD COLUMN `levy_amount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `levy_rate`;
ALTER TABLE `kitchen_orders` ADD COLUMN `charge_to_room` tinyint(1) NOT NULL DEFAULT 0 AFTER `stay_id`;
ALTER TABLE `kitchen_orders` ADD COLUMN `customer_name` varchar(255) NULL DEFAULT NULL AFTER `type`;
ALTER TABLE `kitchen_orders` ADD COLUMN `table_number` varchar(20) NULL DEFAULT NULL AFTER `customer_name`;
ALTER TABLE `kitchen_orders` ADD COLUMN `cancel_reason` varchar(255) NULL DEFAULT NULL AFTER `delivered_at`;
ALTER TABLE `payrolls` ADD COLUMN `overtime_hours` decimal(6,2) NOT NULL DEFAULT 0.00 AFTER `basic_salary`;
ALTER TABLE `payrolls` ADD COLUMN `paye` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `tax_deductions`;
ALTER TABLE `payrolls` ADD COLUMN `nssf` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `paye`;
ALTER TABLE `payrolls` ADD COLUMN `shif` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `nssf`;
ALTER TABLE `payrolls` ADD COLUMN `housing_levy` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `shif`;
ALTER TABLE `payrolls` ADD COLUMN `paid_by` bigint unsigned NULL DEFAULT NULL AFTER `paid_at`;
ALTER TABLE `purchase_order_items` ADD COLUMN `received_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `quantity`;
ALTER TABLE `purchase_orders` ADD COLUMN `supplier_reference` varchar(100) NULL DEFAULT NULL AFTER `notes`;
ALTER TABLE `staff_profiles` ADD COLUMN `job_title` varchar(255) NULL DEFAULT NULL AFTER `department_id`;
ALTER TABLE `staff_profiles` ADD COLUMN `id_number` varchar(30) NULL DEFAULT NULL AFTER `job_title`;
ALTER TABLE `staff_profiles` ADD COLUMN `kra_pin` varchar(20) NULL DEFAULT NULL AFTER `id_number`;
ALTER TABLE `staff_profiles` ADD COLUMN `nssf_number` varchar(30) NULL DEFAULT NULL AFTER `kra_pin`;
ALTER TABLE `staff_profiles` ADD COLUMN `shif_number` varchar(30) NULL DEFAULT NULL AFTER `nssf_number`;
ALTER TABLE `staff_profiles` ADD COLUMN `end_date` date NULL DEFAULT NULL AFTER `hire_date`;
ALTER TABLE `staff_profiles` ADD COLUMN `notes` text NULL DEFAULT NULL AFTER `emergency_contact`;
ALTER TABLE `stays` ADD COLUMN `meal_plan` varchar(2) NOT NULL DEFAULT 'BB' AFTER `children`;
ALTER TABLE `suppliers` ADD COLUMN `kra_pin` varchar(20) NULL DEFAULT NULL AFTER `email`;
ALTER TABLE `suppliers` ADD COLUMN `payment_terms` varchar(100) NULL DEFAULT NULL AFTER `address`;
ALTER TABLE `suppliers` ADD COLUMN `notes` text NULL DEFAULT NULL AFTER `payment_terms`;
ALTER TABLE `tasks` ADD COLUMN `blocks_room` tinyint(1) NOT NULL DEFAULT 0 AFTER `room_id`;

ALTER TABLE `purchase_orders` MODIFY `status` enum('draft','submitted','approved','ordered','partially_received','received','cancelled') NOT NULL DEFAULT 'draft';

-- Indexes for the new link columns (match staging)
ALTER TABLE `invoices` ADD INDEX `invoices_facility_booking_id_foreign` (`facility_booking_id`);
ALTER TABLE `invoices` ADD INDEX `invoices_kitchen_order_id_foreign` (`kitchen_order_id`);
ALTER TABLE `payrolls` ADD INDEX `payrolls_paid_by_foreign` (`paid_by`);

-- Live-only column the new code doesn't fill: let it be empty instead of blocking new packages
ALTER TABLE `facility_packages` MODIFY `duration_hours` int(11) NULL DEFAULT NULL;

-- Hand-applied on live (never artisan migrate). Same as 2026_11_21_100000_create_etims_receipts_table.
CREATE TABLE IF NOT EXISTS etims_receipts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT UNSIGNED NOT NULL,
  invc_no INT UNSIGNED NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  rcpt_no VARCHAR(40) NULL,
  tot_rcpt_no VARCHAR(40) NULL,
  intrl_data VARCHAR(120) NULL,
  rcpt_sign VARCHAR(120) NULL,
  sdc_id VARCHAR(40) NULL,
  mrc_no VARCHAR(40) NULL,
  receipt_at TIMESTAMP NULL,
  attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  error TEXT NULL,
  request LONGTEXT NULL,
  response LONGTEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY etims_receipts_invoice_id_unique (invoice_id),
  UNIQUE KEY etims_receipts_invc_no_unique (invc_no),
  KEY etims_receipts_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
