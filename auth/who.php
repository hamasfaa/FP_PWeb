<?php
include '../../database/config.php';

if (!isset($_SESSION['U_ID'])) {
    header('Location: ../home/login.php');
    exit();
}
