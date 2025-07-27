<?php
/**
 * Timezone Configuration for VPMS
 * This file sets the default timezone for the entire application
 * 
 * Common timezone options:
 * - 'Africa/Nairobi' (EAT - UTC+3) - Kenya, Uganda, Tanzania
 * - 'America/New_York' (EST/EDT - UTC-5/-4) - Eastern US
 * - 'Europe/London' (GMT/BST - UTC+0/+1) - UK
 * - 'Asia/Tokyo' (JST - UTC+9) - Japan
 * - 'Australia/Sydney' (AEST/AEDT - UTC+10/+11) - Australia
 * 
 * Change the timezone below to match your location
 */

// Set your timezone here
$default_timezone = 'Africa/Nairobi'; // Change this to your timezone

// Set the timezone if not already set
if (!ini_get('date.timezone')) {
    date_default_timezone_set($default_timezone);
}

// Optional: Set MySQL timezone to match PHP timezone
// Uncomment the lines below if you want to sync database timezone with PHP timezone
/*
if (isset($con) && $con) {
    $timezone_offset = date('P'); // Gets timezone offset like +03:00
    mysqli_query($con, "SET time_zone = '$timezone_offset'");
}
*/

// Helper function to get current time in different formats
function getCurrentTime($format = 'Y-m-d H:i:s') {
    return date($format);
}

// Helper function to format time from database
function formatTime($datetime, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($datetime));
}

// Helper function to get timezone info
function getTimezoneInfo() {
    return [
        'timezone' => date_default_timezone_get(),
        'offset' => date('P'),
        'current_time' => date('Y-m-d H:i:s'),
        'current_timestamp' => time()
    ];
}
?>
