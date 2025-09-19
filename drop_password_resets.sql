-- SQL Script to remove password_resets table
-- Run this in phpMyAdmin or your MySQL client

DROP TABLE IF EXISTS password_resets;

-- Verify table is dropped
SHOW TABLES LIKE 'password_resets';