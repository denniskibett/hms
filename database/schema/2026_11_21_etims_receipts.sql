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
