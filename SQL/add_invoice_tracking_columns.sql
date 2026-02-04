-- Add columns to schoo_fee_invoices table to track gross amount and concession/discount amounts
-- This enables proper invoice tracking and audit trails

ALTER TABLE `schoo_fee_invoices` 
ADD COLUMN `gross_amount` DECIMAL(12,2) DEFAULT 0.00 AFTER `billing_month`,
ADD COLUMN `concession_amount` DECIMAL(12,2) DEFAULT 0.00 AFTER `gross_amount`,
ADD COLUMN `net_payable` DECIMAL(12,2) DEFAULT 0.00 AFTER `concession_amount`;

-- Update the indexes if needed (optional)
-- The net_payable should essentially match total_amount, but we keep both for backward compatibility

-- Migration note:
-- For existing invoices, set:
-- gross_amount = total_amount + concession_amount (assuming concession_amount was never tracked)
-- net_payable = total_amount
-- This should be a one-time fix
