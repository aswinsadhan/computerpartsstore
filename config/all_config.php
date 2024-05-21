<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
require('csrf.php');
require('database.php');
ob_start();
?>
