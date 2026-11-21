-- Hand-applied on live (never artisan migrate). Same as 2026_11_21_200000_company_accounts_and_night_audit. Additive only.
CREATE TABLE IF NOT EXISTS companies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  kra_pin VARCHAR(20) NULL,
  contact_name VARCHAR(255) NULL,
  email VARCHAR(255) NULL,
  phone VARCHAR(30) NULL,
  address VARCHAR(255) NULL,
  credit_limit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_terms_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  rate_discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY companies_name_index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE stays ADD COLUMN company_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER guest_id, ADD INDEX stays_company_id_index (company_id);
ALTER TABLE facility_bookings ADD COLUMN company_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER stay_id, ADD INDEX facility_bookings_company_id_index (company_id);
ALTER TABLE invoices ADD COLUMN company_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER kitchen_order_id, ADD INDEX invoices_company_id_index (company_id);
ALTER TABLE stays MODIFY status ENUM('booked','checked_in','checked_out','cancelled','reserved','no_show') NOT NULL DEFAULT 'reserved';

CREATE TABLE IF NOT EXISTS night_audits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  business_date DATE NOT NULL,
  run_by BIGINT UNSIGNED NULL,
  run_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  summary LONGTEXT NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY night_audits_business_date_unique (business_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
