-- ========================================
-- VPMS Feedback Module - Standalone Database Script
-- ========================================
-- This script creates ONLY the feedback module tables and data
-- Run this on your existing VPMS database to add feedback functionality
-- This will NOT affect any existing data or tables

-- ========================================
-- Table structure for table `tblfeedback`
-- ========================================

CREATE TABLE IF NOT EXISTS `tblfeedback` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(5) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `Status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `Priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `Category` varchar(100) DEFAULT 'General',
  `CreatedDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedDate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AdminID` int(10) DEFAULT NULL,
  `AdminResponse` text DEFAULT NULL,
  `AdminResponseDate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `AdminID` (`AdminID`),
  KEY `Status` (`Status`),
  KEY `Priority` (`Priority`),
  KEY `idx_feedback_user_status` (`UserID`, `Status`),
  KEY `idx_feedback_created_date` (`CreatedDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ========================================
-- Table structure for table `tblfeedback_replies`
-- ========================================

CREATE TABLE IF NOT EXISTS `tblfeedback_replies` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `FeedbackID` int(10) NOT NULL,
  `SenderType` enum('User','Admin') NOT NULL,
  `SenderID` int(10) NOT NULL,
  `Message` text NOT NULL,
  `CreatedDate` timestamp NULL DEFAULT current_timestamp(),
  `IsRead` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `FeedbackID` (`FeedbackID`),
  KEY `SenderType` (`SenderType`),
  KEY `IsRead` (`IsRead`),
  KEY `idx_replies_feedback_sender` (`FeedbackID`, `SenderType`),
  KEY `idx_replies_unread` (`IsRead`, `SenderType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ========================================
-- Add Foreign Key Constraints
-- ========================================

-- Add foreign key for tblfeedback.UserID -> tblregusers.ID
ALTER TABLE `tblfeedback` 
ADD CONSTRAINT `tblfeedback_ibfk_1` 
FOREIGN KEY (`UserID`) REFERENCES `tblregusers` (`ID`) ON DELETE CASCADE;

-- Add foreign key for tblfeedback.AdminID -> tbladmin.ID
ALTER TABLE `tblfeedback` 
ADD CONSTRAINT `tblfeedback_ibfk_2` 
FOREIGN KEY (`AdminID`) REFERENCES `tbladmin` (`ID`) ON DELETE SET NULL;

-- Add foreign key for tblfeedback_replies.FeedbackID -> tblfeedback.ID
ALTER TABLE `tblfeedback_replies` 
ADD CONSTRAINT `tblfeedback_replies_ibfk_1` 
FOREIGN KEY (`FeedbackID`) REFERENCES `tblfeedback` (`ID`) ON DELETE CASCADE;

-- ========================================
-- Sample Data for Testing (Optional)
-- ========================================
-- You can remove this section if you don't want sample data

-- Insert sample feedback entries
INSERT INTO `tblfeedback` (`UserID`, `Subject`, `Message`, `Category`, `Priority`, `Status`, `CreatedDate`) VALUES
(2, 'Parking Space Booking Issue', 'I am having trouble booking a parking space. The system shows available spaces but when I try to book, it says no spaces are available. This has been happening for the past two days.', 'Booking Problem', 'High', 'Open', '2025-01-07 10:30:00'),
(3, 'Payment Gateway Problem', 'The payment is not processing correctly. I tried multiple times but the transaction fails at the final step. I have tried different cards but the issue persists.', 'Payment Issue', 'Critical', 'Open', '2025-01-07 11:15:00'),
(4, 'Feature Request: Mobile App', 'It would be great to have a mobile app for easier access to the parking system. This would make booking and managing parking much more convenient for users on the go.', 'Feature Request', 'Medium', 'Open', '2025-01-07 12:00:00'),
(5, 'User Interface Improvement', 'The dashboard could be more user-friendly. Some buttons are hard to find and the navigation could be improved. Maybe consider a more modern design approach.', 'User Interface', 'Low', 'Open', '2025-01-07 13:45:00'),
(2, 'Receipt Download Issue', 'I cannot download my parking receipt. When I click the download button, nothing happens. I need this receipt for my expense report.', 'Bug Report', 'Medium', 'Open', '2025-01-07 14:20:00'),
(6, 'Parking Duration Extension', 'Is there a way to extend parking duration without having to make a new booking? Sometimes meetings run longer than expected.', 'Feature Request', 'Low', 'Open', '2025-01-07 15:10:00');

-- Insert sample replies to demonstrate conversation functionality
INSERT INTO `tblfeedback_replies` (`FeedbackID`, `SenderType`, `SenderID`, `Message`, `CreatedDate`, `IsRead`) VALUES
(1, 'Admin', 1, 'Thank you for reporting this booking issue. We have identified a synchronization problem between our availability checker and the actual booking system. Our technical team is working on a fix and we expect to resolve this within 24 hours.', '2025-01-07 16:00:00', 0),
(1, 'User', 2, 'Thank you for the quick response. Will I be notified when the issue is fixed? Also, is there a workaround I can use in the meantime?', '2025-01-07 16:30:00', 0),
(1, 'Admin', 1, 'Yes, we will notify you once the fix is deployed. As a temporary workaround, you can call our support line at (555) 123-4567 and we can manually process your booking.', '2025-01-07 17:00:00', 0),
(2, 'Admin', 1, 'We have identified the payment gateway issue and our technical team is working on it. The problem appears to be with our SSL certificate renewal. We will update you within 24 hours with a resolution.', '2025-01-07 16:45:00', 0),
(3, 'Admin', 1, 'Thank you for this suggestion! A mobile app is actually on our roadmap for Q2 2025. We will keep you updated on the development progress. In the meantime, our website is mobile-responsive for basic functionality.', '2025-01-07 17:15:00', 0),
(4, 'Admin', 1, 'We appreciate your feedback on the user interface. We are planning a UI/UX overhaul in the coming months. Would you be interested in participating in user testing sessions?', '2025-01-07 17:30:00', 0),
(4, 'User', 5, 'Yes, I would be very interested in participating in user testing! Please let me know how I can get involved.', '2025-01-07 18:00:00', 0);

-- Update some feedback entries with admin responses
UPDATE `tblfeedback` SET 
    `AdminResponse` = 'Thank you for reporting this booking issue. We have identified a synchronization problem between our availability checker and the actual booking system. Our technical team is working on a fix and we expect to resolve this within 24 hours.',
    `AdminID` = 1,
    `AdminResponseDate` = '2025-01-07 16:00:00',
    `Status` = 'In Progress',
    `UpdatedDate` = '2025-01-07 16:00:00'
WHERE `ID` = 1;

UPDATE `tblfeedback` SET 
    `AdminResponse` = 'We have identified the payment gateway issue and our technical team is working on it. The problem appears to be with our SSL certificate renewal. We will update you within 24 hours with a resolution.',
    `AdminID` = 1,
    `AdminResponseDate` = '2025-01-07 16:45:00',
    `Status` = 'In Progress',
    `UpdatedDate` = '2025-01-07 16:45:00'
WHERE `ID` = 2;

UPDATE `tblfeedback` SET 
    `AdminResponse` = 'Thank you for this suggestion! A mobile app is actually on our roadmap for Q2 2025. We will keep you updated on the development progress. In the meantime, our website is mobile-responsive for basic functionality.',
    `AdminID` = 1,
    `AdminResponseDate` = '2025-01-07 17:15:00',
    `Status` = 'Resolved',
    `UpdatedDate` = '2025-01-07 17:15:00'
WHERE `ID` = 3;

UPDATE `tblfeedback` SET 
    `AdminResponse` = 'We appreciate your feedback on the user interface. We are planning a UI/UX overhaul in the coming months. Would you be interested in participating in user testing sessions?',
    `AdminID` = 1,
    `AdminResponseDate` = '2025-01-07 17:30:00',
    `Status` = 'In Progress',
    `UpdatedDate` = '2025-01-07 17:30:00'
WHERE `ID` = 4;

-- ========================================
-- Verification Queries
-- ========================================
-- Run these queries after installation to verify everything is working

-- Check if tables were created successfully
-- SELECT 'tblfeedback table created' as status, COUNT(*) as record_count FROM tblfeedback;
-- SELECT 'tblfeedback_replies table created' as status, COUNT(*) as record_count FROM tblfeedback_replies;

-- Check foreign key constraints
-- SELECT 
--     CONSTRAINT_NAME,
--     TABLE_NAME,
--     COLUMN_NAME,
--     REFERENCED_TABLE_NAME,
--     REFERENCED_COLUMN_NAME
-- FROM information_schema.KEY_COLUMN_USAGE 
-- WHERE REFERENCED_TABLE_SCHEMA = 'vpmsdb' 
-- AND TABLE_NAME IN ('tblfeedback', 'tblfeedback_replies');

-- Check sample data
-- SELECT 
--     f.ID,
--     f.Subject,
--     f.Status,
--     f.Priority,
--     f.Category,
--     CONCAT(u.FirstName, ' ', u.LastName) as UserName,
--     f.CreatedDate
-- FROM tblfeedback f
-- JOIN tblregusers u ON f.UserID = u.ID
-- ORDER BY f.CreatedDate DESC;

-- ========================================
-- Installation Complete
-- ========================================
-- The feedback module has been successfully installed!
-- 
-- Next steps:
-- 1. Verify the installation by running the verification queries above
-- 2. Test the user interface at: users/feedback.php
-- 3. Test the admin interface at: admin/manage-feedback.php
-- 4. Check the navigation menus for new feedback options
-- 
-- For troubleshooting, run: test_feedback_module.php

COMMIT;
