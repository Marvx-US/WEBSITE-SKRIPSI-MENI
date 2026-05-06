<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}