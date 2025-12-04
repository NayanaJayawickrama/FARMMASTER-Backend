-- Fix land_report table status column to support 'Sent to Owner' status
-- This adds the missing status value to the enum

ALTER TABLE `land_report` 
MODIFY COLUMN `status` ENUM('Rejected', 'Approved', 'Sent to Owner', 'Under Review', 'Not Reviewed', 'Pending', '') 
NOT NULL DEFAULT '';

-- Verify the change
DESCRIBE `land_report`;
