-- Fix land_report table status column to support all necessary status values
-- This adds the missing status values to the enum

ALTER TABLE `land_report` 
MODIFY COLUMN `status` ENUM(
    'Rejected',
    'Approved', 
    'Sent to Owner',
    'Under Review',
    'Not Reviewed',
    'Pending',
    'Assessment Pending',
    ''
) NOT NULL DEFAULT '';

-- Verify the change
DESCRIBE `land_report`;

-- Display current records to verify
SELECT report_id, status FROM land_report;
