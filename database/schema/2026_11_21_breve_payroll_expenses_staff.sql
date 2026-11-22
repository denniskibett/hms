-- Hand-applied on live (never artisan migrate). Same as 2026_11_21_300000_breve_payroll_expenses_staff. Additive only.
-- Payroll, expenses and staff records brought up to Breve's journeys.

ALTER TABLE staff_profiles
  ADD COLUMN pay_basis VARCHAR(20) NOT NULL DEFAULT 'statutory' AFTER salary,
  ADD COLUMN allowances JSON NULL AFTER pay_basis,
  ADD COLUMN deductions JSON NULL AFTER allowances,
  ADD COLUMN insurance_premium DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER deductions,
  ADD COLUMN pay_method VARCHAR(10) NOT NULL DEFAULT 'bank' AFTER insurance_premium,
  ADD COLUMN mpesa_phone VARCHAR(20) NULL AFTER pay_method;

ALTER TABLE payrolls
  ADD COLUMN insurance_relief DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER other_deductions,
  ADD COLUMN employer_nssf DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER insurance_relief,
  ADD COLUMN employer_housing_levy DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER employer_nssf,
  ADD COLUMN breakdown JSON NULL AFTER employer_housing_levy;

ALTER TABLE expenses MODIFY method ENUM('cash','credit_card','mobile_money','bank_transfer','cheque') NULL;
ALTER TABLE expenses MODIFY status ENUM('pending','approved','paid','rejected','void') NOT NULL DEFAULT 'paid';
ALTER TABLE expenses
  ADD COLUMN receipt_path VARCHAR(255) NULL AFTER reference,
  ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER created_by,
  ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
  ADD COLUMN paid_at TIMESTAMP NULL AFTER approved_at,
  ADD CONSTRAINT expenses_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users (id) ON DELETE SET NULL;

-- Expenses recorded before this change were paid when entered.
UPDATE expenses SET paid_at = created_at WHERE status = 'paid' AND paid_at IS NULL;
