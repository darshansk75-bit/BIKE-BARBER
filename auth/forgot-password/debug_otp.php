<?php
session_start();
if (isset($_SESSION['reset_otp'])) {
    echo "OTP:" . $_SESSION['reset_otp'];
} else {
    echo "NO_OTP";
}
?>
