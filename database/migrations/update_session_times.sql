-- ============================================
-- Update Session Times Migration
-- ============================================
-- This script updates existing schedule assignments and system settings
-- to use the new session times.
--
-- New Session Times:
-- Sesi 1: 07:30 - 10:00
-- Sesi 2: 10:20 - 12:50
-- Sesi 3: 13:30 - 16:00
--
-- Run this script after updating the code files.
-- ============================================

-- Update System Settings
UPDATE system_settings SET value = '07:30' WHERE `key` = 'schedule.session_1_start';
UPDATE system_settings SET value = '10:00' WHERE `key` = 'schedule.session_1_end';
UPDATE system_settings SET value = '10:20' WHERE `key` = 'schedule.session_2_start';
UPDATE system_settings SET value = '12:50' WHERE `key` = 'schedule.session_2_end';
UPDATE system_settings SET value = '13:30' WHERE `key` = 'schedule.session_3_start';
UPDATE system_settings SET value = '16:00' WHERE `key` = 'schedule.session_3_end';

-- Update Schedule Assignments
-- Sesi 1: 07:30 - 10:00
UPDATE schedule_assignments 
SET time_start = '07:30:00', time_end = '10:00:00' 
WHERE session = '1';

-- Sesi 2: 10:20 - 12:50
UPDATE schedule_assignments 
SET time_start = '10:20:00', time_end = '12:50:00' 
WHERE session = '2';

-- Sesi 3: 13:30 - 16:00
UPDATE schedule_assignments 
SET time_start = '13:30:00', time_end = '16:00:00' 
WHERE session = '3';

-- Show updated counts
SELECT 
    session,
    time_start,
    time_end,
    COUNT(*) as total_assignments
FROM schedule_assignments
GROUP BY session, time_start, time_end
ORDER BY session;

-- Show updated system settings
SELECT `key`, value 
FROM system_settings 
WHERE `key` LIKE 'schedule.session%'
ORDER BY `key`;
