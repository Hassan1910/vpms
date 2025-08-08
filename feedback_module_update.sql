-- Feedback Module Database Update Script
-- Run this script to add feedback functionality to your existing VPMS database

-- Create feedback table
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
  CONSTRAINT `tblfeedback_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `tblregusers` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `tblfeedback_ibfk_2` FOREIGN KEY (`AdminID`) REFERENCES `tbladmin` (`ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Create feedback replies table
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
  CONSTRAINT `tblfeedback_replies_ibfk_1` FOREIGN KEY (`FeedbackID`) REFERENCES `tblfeedback` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Insert sample feedback data for testing (optional)
INSERT INTO `tblfeedback` (`UserID`, `Subject`, `Message`, `Category`, `Priority`, `Status`) VALUES
(2, 'Parking Space Booking Issue', 'I am having trouble booking a parking space. The system shows available spaces but when I try to book, it says no spaces available.', 'Booking Problem', 'High', 'Open'),
(3, 'Payment Gateway Problem', 'The payment is not processing correctly. I tried multiple times but the transaction fails.', 'Payment Issue', 'Critical', 'Open'),
(4, 'Feature Request: Mobile App', 'It would be great to have a mobile app for easier access to the parking system.', 'Feature Request', 'Medium', 'Open'),
(5, 'User Interface Improvement', 'The dashboard could be more user-friendly. Some buttons are hard to find.', 'User Interface', 'Low', 'Open');

-- Insert sample replies for testing (optional)
INSERT INTO `tblfeedback_replies` (`FeedbackID`, `SenderType`, `SenderID`, `Message`) VALUES
(1, 'Admin', 1, 'Thank you for reporting this issue. We are investigating the booking system and will fix this soon.'),
(1, 'User', 2, 'Thank you for the quick response. When can I expect this to be resolved?'),
(2, 'Admin', 1, 'We have identified the payment gateway issue and our technical team is working on it. We will update you within 24 hours.');

-- Update admin response for sample data
UPDATE `tblfeedback` SET 
    `AdminResponse` = 'Thank you for reporting this issue. We are investigating the booking system and will fix this soon.',
    `AdminID` = 1,
    `AdminResponseDate` = NOW(),
    `Status` = 'In Progress'
WHERE `ID` = 1;

UPDATE `tblfeedback` SET 
    `AdminResponse` = 'We have identified the payment gateway issue and our technical team is working on it. We will update you within 24 hours.',
    `AdminID` = 1,
    `AdminResponseDate` = NOW(),
    `Status` = 'In Progress'
WHERE `ID` = 2;

-- Create indexes for better performance
CREATE INDEX idx_feedback_user_status ON tblfeedback(UserID, Status);
CREATE INDEX idx_feedback_created_date ON tblfeedback(CreatedDate);
CREATE INDEX idx_replies_feedback_sender ON tblfeedback_replies(FeedbackID, SenderType);
CREATE INDEX idx_replies_unread ON tblfeedback_replies(IsRead, SenderType);

COMMIT;
