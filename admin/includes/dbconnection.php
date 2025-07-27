<?php
// Set the default timezone (adjust this to your local timezone)
date_default_timezone_set('Africa/Nairobi'); // Change this to your timezone

$con=mysqli_connect("localhost", "root", "", "vpmsdb");
if(mysqli_connect_errno()){
echo "Connection Fail".mysqli_connect_error();
}

  ?>
